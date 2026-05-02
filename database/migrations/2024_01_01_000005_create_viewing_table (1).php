<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viewing', function (Blueprint $table) {
            // Composite PK: property_no + renter_no + viewing_date
            $table->integer('property_no');
            $table->integer('renter_no');
            $table->date('viewing_date');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->primary(['property_no', 'renter_no', 'viewing_date']);

            $table->foreign('property_no')->references('property_no')->on('property_for_rent');
            $table->foreign('renter_no')->references('renter_no')->on('renter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewing');
    }
};
