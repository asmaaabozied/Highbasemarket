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
        Schema::table('messages', function (Blueprint $table): void {
            $table->foreignId('sender_id')->nullable()->change();
            $table->foreignId('receiver_id')->nullable()->change();
            $table->foreignId('sender_branch_id')->after('branch_id')->nullable()->constrained('branches');
            $table->foreignId('receiver_branch_id')->nullable()->change()->constrained('branches');
            $table->foreignId('account_id')->after('receiver_branch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropForeign(['sender_branch_id']);
            $table->dropColumn('sender_branch_id');
            $table->dropColumn('account_id');
            $table->dropForeign(['receiver_branch_id']);
            $table->integer('receiver_branch_id')->change();
        });
    }
};
