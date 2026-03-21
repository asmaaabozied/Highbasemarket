<?php

namespace Database\Factories;

use App\Models\ImportedFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ImportedFileFactory extends Factory
{
    protected $model = ImportedFile::class;

    public function definition(): array
    {
        return [
            'path'       => $this->faker->word(),
            'type'       => $this->faker->word(),
            'status'     => $this->faker->word(),
            'data'       => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
