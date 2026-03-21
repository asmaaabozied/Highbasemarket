<?php

namespace Database\Factories;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        return [
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
            'account_id'  => $this->faker->randomNumber(),
            'upload_type' => $this->faker->word(),
            'upload_path' => $this->faker->word(),
            'status'      => $this->faker->word(),
        ];
    }
}
