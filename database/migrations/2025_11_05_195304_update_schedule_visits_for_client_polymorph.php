<?php

use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->nullableMorphs('visitable');
        });

        DB::table('schedule_visits')
            ->whereNotNull('customer_id')
            ->update([
                'visitable_id'   => DB::raw('customer_id'),
                'visitable_type' => Branch::class,
            ]);

        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->constrained('branches')->nullOnDelete();
        });

        DB::table('schedule_visits')
            ->where('visitable_type', Branch::class)
            ->update([
                'customer_id' => DB::raw('visitable_id'),
            ]);

        Schema::table('schedule_visits', function (Blueprint $table): void {
            $table->dropMorphs('visitable');
        });
    }
};
