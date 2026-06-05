<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('measured_at');
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('height_cm', 5, 2);
            $table->decimal('temperature_c', 4, 2)->nullable();
            $table->enum('source', ['manual', 'iot'])->default('manual');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('measurements');
    }
}
