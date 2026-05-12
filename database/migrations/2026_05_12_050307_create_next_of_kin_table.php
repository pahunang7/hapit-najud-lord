<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('next_of_kin', function (Blueprint $table) {

            // =========================================================
            // PRIMARY + FOREIGN KEY (1:1 relationship with staff)
            // =========================================================
            $table->unsignedInteger('staff_no')->primary();

            // =========================================================
            // NEXT OF KIN DETAILS
            // =========================================================
            $table->string('full_name', 100);
            $table->string('relationship', 50);
            $table->string('address', 150);
            $table->string('telephone_no', 20);

            // =========================================================
            // FOREIGN KEY CONSTRAINT
            // =========================================================
            $table->foreign('staff_no')
                ->references('staff_no')
                ->on('staff')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('next_of_kin');
    }
};