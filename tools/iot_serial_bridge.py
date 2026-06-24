#!/usr/bin/env python3
import argparse
import json
import re
import sys
import time
import urllib.error
import urllib.request

import serial


UID_KEYS = ("rfid_uid", "uid", "rfid", "card_uid", "card_id")

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8", errors="backslashreplace")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8", errors="backslashreplace")


def normalize_uid(value):
    return re.sub(r"[^0-9A-Za-z]", "", str(value)).upper()


def parse_line(line):
    text = line.strip()
    if not text:
        return None

    try:
        payload = json.loads(text)
        if isinstance(payload, dict):
            for key in UID_KEYS:
                if payload.get(key):
                    payload["rfid_uid"] = normalize_uid(payload[key])
                    return payload
    except json.JSONDecodeError:
        pass

    match = re.search(
        r"(?:CARD\s*)?(?:RFID\s*)?UID\s*[:=]\s*([0-9A-Fa-f][0-9A-Fa-f\s:-]{2,})",
        text,
        re.IGNORECASE,
    )
    if match:
        return {"rfid_uid": normalize_uid(match.group(1)), "raw": text}

    if re.fullmatch(r"[0-9A-Fa-f][0-9A-Fa-f\s:-]{3,63}", text):
        return {"rfid_uid": normalize_uid(text), "raw": text}

    columns = [column.strip() for column in text.split(",")]
    if len(columns) >= 2 and re.fullmatch(r"\d{6,64}", columns[0]):
        return {
            "rfid_uid": normalize_uid(columns[0]),
            "card_name": columns[1] if len(columns) > 1 else None,
            "card_detail": columns[2] if len(columns) > 2 else None,
            "raw": text,
        }

    return None


def post_json(url, token, payload):
    request = urllib.request.Request(
        url,
        data=json.dumps(payload).encode("utf-8"),
        headers={
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Device-Token": token,
        },
        method="POST",
    )
    with urllib.request.urlopen(request, timeout=10) as response:
        return response.status, json.loads(response.read().decode("utf-8"))


def ping_device(api_base, token):
    request = urllib.request.Request(
        f"{api_base.rstrip('/')}/iot/ping",
        headers={
            "Accept": "application/json",
            "X-Device-Token": token,
        },
        method="GET",
    )
    with urllib.request.urlopen(request, timeout=10) as response:
        status = response.status
        result = json.loads(response.read().decode("utf-8"))
    device = result.get("device", {})
    print(
        f"[bridge] heartbeat {device.get('device_name', 'device')} "
        f"({device.get('status', 'unknown')})",
        flush=True,
    )
    return status, result


def main():
    parser = argparse.ArgumentParser(description="Bridge CP2102 serial RFID data to Karpin API.")
    parser.add_argument("--port", default="COM8")
    parser.add_argument("--baud", type=int, default=115200)
    parser.add_argument("--api-base", default="http://127.0.0.1:8000/api")
    parser.add_argument("--token", required=True)
    args = parser.parse_args()

    while True:
        try:
            print(f"[bridge] opening {args.port} at {args.baud} baud", flush=True)
            with serial.Serial(args.port, args.baud, timeout=1) as connection:
                connection.dtr = False
                connection.rts = False
                print("[bridge] connected; waiting for RFID tap", flush=True)
                next_ping_at = time.monotonic()
                while True:
                    now = time.monotonic()
                    if now >= next_ping_at:
                        try:
                            ping_device(args.api_base, args.token)
                        except urllib.error.HTTPError as error:
                            detail = error.read().decode("utf-8", errors="replace")
                            print(f"[bridge] heartbeat error {error.code}: {detail}", flush=True)
                        except (urllib.error.URLError, TimeoutError) as error:
                            print(f"[bridge] heartbeat unavailable: {error}", flush=True)
                        next_ping_at = now + 30

                    raw = connection.readline()
                    if not raw:
                        continue

                    line = raw.decode("utf-8", errors="replace").strip()
                    payload = parse_line(line)
                    if not payload or not payload.get("rfid_uid"):
                        print(f"[bridge] ignored bytes: {raw.hex(' ')}", flush=True)
                        continue

                    try:
                        status, result = post_json(
                            f"{args.api_base.rstrip('/')}/iot/rfid/scan",
                            args.token,
                            payload,
                        )
                        child = result.get("data", {}).get("child")
                        print(
                            f"[bridge] RFID {payload['rfid_uid']} -> "
                            f"{child['child_name'] if child else 'not registered'} ({status})",
                            flush=True,
                        )

                        if child and payload.get("weight_kg") is not None and payload.get("height_cm") is not None:
                            measurement = {
                                key: payload[key]
                                for key in ("rfid_uid", "weight_kg", "height_cm", "temperature_c", "notes")
                                if payload.get(key) is not None
                            }
                            measure_status, measure_result = post_json(
                                f"{args.api_base.rstrip('/')}/iot/measurements",
                                args.token,
                                measurement,
                            )
                            print(
                                f"[bridge] measurement stored: "
                                f"{measure_result.get('data', {}).get('measurement_id')} ({measure_status})",
                                flush=True,
                            )
                    except urllib.error.HTTPError as error:
                        detail = error.read().decode("utf-8", errors="replace")
                        print(f"[bridge] API error {error.code}: {detail}", flush=True)
                    except (urllib.error.URLError, TimeoutError) as error:
                        print(f"[bridge] API unavailable: {error}; retrying", flush=True)
                        time.sleep(2)
        except serial.SerialException as error:
            print(f"[bridge] serial disconnected: {error}; reconnecting", flush=True)
            time.sleep(2)
        except KeyboardInterrupt:
            return 0


if __name__ == "__main__":
    sys.exit(main())
