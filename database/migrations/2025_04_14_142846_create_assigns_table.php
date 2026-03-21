<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigns', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('assigner');
            $table->nullableMorphs('assignable');
            $table->nullableMorphs('assignee');
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assigns');
    }
};
