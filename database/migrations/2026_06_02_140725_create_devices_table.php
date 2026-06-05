<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_code')->unique();
            $table->string('device_name');
            $table->string('device_type')->default('timbangan-iot');
            $table->string('location')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->timestamp('last_seen_at')->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
