<?php

use App\Enum\VisitStatus;
use App\Models\Branch;
use App\Models\Employee;
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
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Employee::class)->constrained();
            $table->foreignIdFor(Branch::class)->constrained();

            $table->decimal('store_latitude', 10, 7)->nullable();
            $table->decimal('store_longitude', 10, 7)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('distance_meters')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->timestamp('visited_at')->nullable();
            $table->longText('notes')->nullable();
            $table->longText('rejection_reason')->nullable();
            $table->string('status')->default(VisitStatus::RECORDER);
            $table->timestamps();

            $table->index(['employee_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
