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
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sender_id');
            $table->foreignId('receiver_id');
            $table->foreignId('message_media_id')->nullable();
            $table->foreignId('chat_id')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->text('body')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('receiver_branch_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
