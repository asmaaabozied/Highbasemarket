<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suspended_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->morphs('notifiable');
            $table->string('type');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspended_notifications');
    }
};
