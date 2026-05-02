<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->integer('staff_no')->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('address', 150);
            $table->string('telephone_no', 20);
            $table->string('sex', 10)->nullable();
            $table->date('date_of_birth');
            $table->string('NIN', 20)->unique();
            $table->string('position', 20)->nullable(); // Manager, Supervisor, Secretary, Staff
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('date_joined');
            $table->integer('branch_no');
            $table->integer('supervisor_staff_no')->nullable();
            $table->timestamps();

            $table->foreign('branch_no')->references('branch_no')->on('branch_office');
        });

            Schema::table('staff', function (Blueprint $table) {
            $table->foreign('supervisor_staff_no')->references('staff_no')->on('staff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
