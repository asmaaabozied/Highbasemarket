<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set subtotal to current total, and update total to be net (total - discount)
        // for all existing orders where subtotal is still 0 (unprocessed).
        DB::table('orders')
            ->where('subtotal', 0)
            ->update([
                'subtotal' => DB::raw('total'),
                'total'    => DB::raw('total - discount_amount'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: subtotal back to 0, and total back to subtotal
        DB::table('orders')
            ->update([
                'total'    => DB::raw('subtotal'),
                'subtotal' => 0,
            ]);
    }
};
