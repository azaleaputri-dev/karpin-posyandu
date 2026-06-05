<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained()->cascadeOnDelete();
            $table->string('nik', 32)->nullable()->unique();
            $table->string('child_name');
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date');
            $table->string('mother_name');
            $table->string('father_name')->nullable();
            $table->string('guardian_phone', 25)->nullable();
            $table->text('address')->nullable();
            $table->string('blood_type', 5)->nullable();
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
        Schema::dropIfExists('children');
    }
}
