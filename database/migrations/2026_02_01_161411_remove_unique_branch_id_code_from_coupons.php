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
        Schema::table('coupons', function (Blueprint $table): void {
            $indexes        = Schema::getIndexes('coupons');
            $hasUniqueIndex = collect($indexes)->contains(fn ($index): bool => $index['name'] === 'coupons_branch_id_code_unique');

            if ($hasUniqueIndex) {
                // Ensure there's a replacement index for branch_id to satisfy the foreign key
                $hasBranchIndex = collect($indexes)->contains(
                    fn ($index): bool => count($index['columns']) === 1 && $index['columns'][0] === 'branch_id'
                );

                if (! $hasBranchIndex) {
                    $table->index('branch_id', 'coupons_branch_id_index');
                }

                $table->dropUnique('coupons_branch_id_code_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
