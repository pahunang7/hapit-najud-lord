<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_office', function (Blueprint $table) {

            // Primary key (PostgreSQL/Laravel compatible)
            $table->increments('branch_no');

            // Address details
            $table->string('street', 100);
            $table->string('area', 100)->nullable();
            $table->string('city', 100);
            $table->string('postcode', 20);

            // Contact details
            $table->string('telephone_no', 20);
            $table->string('fax_no', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_office');
    }
};