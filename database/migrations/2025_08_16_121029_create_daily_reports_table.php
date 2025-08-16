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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->json('verification_response')->nullable();
            $table->json('report_response')->nullable();
            $table->json('confirmation_response')->nullable();
            $table->decimal('total_revenue', 10, 2)->nullable();
            $table->enum('status', ['pending', 'verified', 'reported', 'confirmed', 'failed'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
