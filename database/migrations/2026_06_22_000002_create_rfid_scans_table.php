<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRfidScansTable extends Migration
{
    public function up()
    {
        Schema::create('rfid_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rfid_uid', 64);
            $table->enum('status', ['recognized', 'unrecognized'])->default('unrecognized');
            $table->json('payload')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
            $table->index(['device_id', 'scanned_at']);
            $table->index(['child_id', 'scanned_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rfid_scans');
    }
}
