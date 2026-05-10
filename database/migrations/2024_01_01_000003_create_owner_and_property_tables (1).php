<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('owner', function (Blueprint $table) {

    $table->integer('owner_no')->primary();

    $table->string('full_name', 100);

    $table->string('address', 150);

    $table->string('telephone_no', 20);

    $table->string('email', 100)->nullable();
});

        Schema::create('property_for_rent', function (Blueprint $table) {
            $table->integer('property_no')->primary();
            $table->string('street', 100);
            $table->string('area', 100);
            $table->string('city', 100);
            $table->string('postcode', 20);
            $table->string('property_type', 50);
            $table->integer('no_of_rooms')->nullable();
            $table->decimal('monthly_rent', 10, 2);
            //  Module 4 (Track rental status)
            $table->string('rental_status', 20)->default('available'); // available, reserved, rented
            $table->integer('owner_no');
            $table->integer('branch_no');
            $table->integer('staff_no');
            $table->timestamps();

            $table->foreign('owner_no')->references('owner_no')->on('owner');
            $table->foreign('branch_no')->references('branch_no')->on('branch_office');
            $table->foreign('staff_no')->references('staff_no')->on('staff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_for_rent');
        Schema::dropIfExists('owner');
    }
};
