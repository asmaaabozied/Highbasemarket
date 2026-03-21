<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('branch_product_id');
            $table->foreignId('variant_id');
            $table->json('packaging');
            $table->decimal('total')->unsigned()->default(0);
            $table->decimal('price')->unsigned()->default(0);
            $table->integer('quantity')->unsigned();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_message')->nullable();
            $table->foreignId('paid_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('rejected_by')->nullable();
            $table->boolean('is_customer_operation')->default(false);
            $table->string('status')->default('pending');
            $table->decimal('hb_commission')->unsigned()->default(0);
            $table->dateTime('hb_commission_paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
