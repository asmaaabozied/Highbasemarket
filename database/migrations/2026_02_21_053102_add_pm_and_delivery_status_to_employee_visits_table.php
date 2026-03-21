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
        Schema::table('employee_visits', function (Blueprint $table) {
            $table->string('pm_name')->nullable();
            $table->json('pm_phone')->nullable();
            $table->string('pm_email')->nullable();
            $table->boolean('shipment_delivered')->nullable();
            $table->string('shipment_not_delivered_reason', 50)->nullable();
            $table->string('shipment_not_delivered_other', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_visits', function (Blueprint $table) {
            $table->dropColumn([
                'pm_name',
                'pm_phone',
                'pm_email',
                'shipment_delivered',
                'shipment_not_delivered_reason',
                'shipment_not_delivered_other',
            ]);
        });
    }
};
