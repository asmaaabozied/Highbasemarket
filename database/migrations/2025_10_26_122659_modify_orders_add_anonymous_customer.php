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
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->change();

            $table->foreignId('anonymous_customer_id')->nullable()->constrained('anonymous_customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable(false)->change();

            $table->dropForeign(['anonymous_customer_id']);
            $table->dropColumn('anonymous_customer_id');

        });
    }
};
