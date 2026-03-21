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
        Schema::table('invitations', function (Blueprint $table): void {
            if (Schema::hasColumns('invitations', ['sent_at', 'receiver_name', 'receiver_pronoun', 'lang', 'campaign', 'cc'])) {
                return;
            }

            $table->timestamp('sent_at')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_pronoun')->nullable();
            $table->string('lang')->nullable();
            $table->string('campaign')->default('gulf food');
            $table->json('cc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table): void {
            //
        });
    }
};
