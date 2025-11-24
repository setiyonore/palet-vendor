<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_shiptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('master_customers')
                ->onDelete('cascade');


            $table->foreignId('city_id')
                ->constrained('master_cities')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_shiptos');
    }
};
