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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email')->after('name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->decimal('subtotal', 10, 2)->default(0)->after('customer_phone');
            $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            $table->decimal('discount', 10, 2)->default(0)->after('tax');
            $table->decimal('total', 10, 2)->default(0)->after('discount');
            $table->string('status')->default('pending')->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_email',
                'customer_phone',
                'subtotal',
                'tax',
                'discount',
                'total',
                'status'
            ]);
        });
    }
};
