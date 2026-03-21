<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('admin_id');
            $table->string('vendor_name');
            $table->string('vendor_type');
            $table->string('email');
            $table->string('link')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
