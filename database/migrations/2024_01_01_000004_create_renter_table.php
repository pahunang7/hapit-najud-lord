<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('renter', function (Blueprint $table) {
            $table->integer('renter_no')->primary();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('address', 150);
            $table->string('telephone_no', 20);
            $table->string('preferred_type', 50)->nullable();
            $table->string('preferred_location', 100)->nullable();
            $table->decimal('max_rent', 10, 2)->nullable();
            $table->integer('staff_no');
            $table->integer('branch_no');
            $table->timestamps();

            $table->foreign('staff_no')->references('staff_no')->on('staff');
            $table->foreign('branch_no')->references('branch_no')->on('branch_office');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renter');
    }
};
