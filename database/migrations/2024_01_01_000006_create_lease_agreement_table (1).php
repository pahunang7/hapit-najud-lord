<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lease_agreement', function (Blueprint $table) {
            $table->integer('lease_no')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration'); // months, 3-12
            $table->decimal('deposit', 10, 2);
            $table->string('deposit_paid', 3)->default('No'); // Yes/No
            $table->string('payment_method', 50);
            $table->integer('property_no');
            $table->integer('renter_no');
            $table->integer('staff_no');
            $table->timestamps();

            $table->foreign('property_no')->references('property_no')->on('property_for_rent');
            $table->foreign('renter_no')->references('renter_no')->on('renter');
            $table->foreign('staff_no')->references('staff_no')->on('staff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_agreement');
    }
};

