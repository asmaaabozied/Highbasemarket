<?php

use App\Models\Branch;
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
        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->nullableMorphs('visitable');
        });
        DB::table('employee_visits')
            ->whereNotNull('customer_id')
            ->update([
                'visitable_id'   => DB::raw('customer_id'),
                'visitable_type' => Branch::class,
            ]);

        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->constrained('branches')->cascadeOnDelete();
        });

        DB::table('employee_visits')
            ->where('visitable_type', Branch::class)
            ->update([
                'customer_id' => DB::raw('visitable_id'),
            ]);

        Schema::table('employee_visits', function (Blueprint $table): void {
            $table->dropMorphs('visitable');
        });
    }
};
