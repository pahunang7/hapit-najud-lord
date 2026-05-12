<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('staff_no');

            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('address', 150);
            $table->string('telephone_no', 20);

            $table->string('sex', 10);                     // NOT NULL per case study
            $table->date('date_of_birth');

            $table->string('nin', 20)->unique();            // NOT NULL + unique

            $table->string('job_title', 20);               // was 'position' — fix here
            $table->decimal('salary', 10, 2);              // NOT NULL per case study

            $table->date('date_joined');

            $table->integer('branch_no');
            $table->integer('supervisor_staff_no')->nullable();

            // Manager-specific
            $table->date('date_start')->nullable();
            $table->decimal('car_allowance', 10, 2)->nullable();
            $table->decimal('bonus', 10, 2)->nullable();

            // Secretary-specific
            $table->integer('typing_speed')->nullable();

            $table->foreign('branch_no')
                  ->references('branch_no')
                  ->on('branch_office')
                  ->onDelete('restrict');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->foreign('supervisor_staff_no')
                  ->references('staff_no')
                  ->on('staff')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['supervisor_staff_no']);
        });
        Schema::dropIfExists('staff');
    }
};