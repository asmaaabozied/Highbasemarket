<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RfqPost>
 */
class RfqPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => 'Branch 2',
            'ended_at'  => '',
            'published' => 0,
            'address'   => [
                ['state' => '1992', 'country' => '18'],
            ],
            'description' => 'This is a test RFQ',
        ];
    }
}
