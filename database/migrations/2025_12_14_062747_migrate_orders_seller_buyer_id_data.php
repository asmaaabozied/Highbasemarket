<?php

use App\Enum\OrderTypeEnum;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('orders')
            ->where('order_type', OrderTypeEnum::INSTANT_ORDER)
            ->update([
                'seller_employee_id' => DB::raw('employee_id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')->update([
            'seller_employee_id' => null,
        ]);
    }
};
