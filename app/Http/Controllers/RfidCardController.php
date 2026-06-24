<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\RfidScan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RfidCardController extends Controller
{
    public function index()
    {
        return view('rfid-cards.index', [
            'children' => Child::with('posyandu')
                ->whereNotNull('rfid_uid')
                ->orderBy('child_name')
                ->paginate(10),
        ]);
    }

    public function create()
    {
        return view('rfid-cards.create', [
            'children' => Child::with('posyandu')
                ->orderBy('child_name')
                ->get(),
            'initialScanId' => RfidScan::max('id'),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'rfid_uid' => $this->normalizeRfidUid($request->input('rfid_uid')),
        ]);

        $data = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'rfid_uid' => [
                'required',
                'string',
                'max:64',
                Rule::unique('children', 'rfid_uid')->ignore($request->input('child_id')),
            ],
        ]);

        $child = Child::findOrFail($data['child_id']);
        $child->update(['rfid_uid' => $data['rfid_uid']]);

        return redirect()
            ->route('rfid-cards.index')
            ->with('status', 'Kartu RFID berhasil dipasang ke ' . $child->child_name . '.');
    }

    public function destroy(Child $rfidCard)
    {
        $childName = $rfidCard->child_name;
        $rfidCard->update(['rfid_uid' => null]);

        return redirect()
            ->route('rfid-cards.index')
            ->with('status', 'Kartu RFID ' . $childName . ' berhasil dilepas.');
    }

    protected function normalizeRfidUid(?string $rfidUid): ?string
    {
        if (! $rfidUid) {
            return null;
        }

        return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', $rfidUid));
    }
}
