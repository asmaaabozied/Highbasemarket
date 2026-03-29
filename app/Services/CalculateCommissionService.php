<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Stock;

class CalculateCommissionService
{
    public function getPlan() {}

    public static function make(Branch $buyer, Branch $seller, Stock $stock): CalculateCommissionService
    {
        return new self($buyer, $seller, $stock);
    }
}
