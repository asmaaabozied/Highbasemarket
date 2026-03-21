<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesAttributesSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = require __DIR__.'/attributes.php';

        foreach ($attributes as $attribute) {
            Category::where('name', $attribute['sub_category'])->first()?->update([
                'attributes'           => $attribute['attributes'],
                'custom_fields'        => $attribute['custom_fields'],
                'displayed_attributes' => $attribute['displayed_attributes'],
            ]);
        }
    }
}
