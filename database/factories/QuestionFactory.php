<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'q' => $this->faker->sentence() . '?',
            'a' => $this->faker->word(),
            'b' => $this->faker->word(),
            'ans' => $this->faker->boolean() ? 1 : 0,
            'ex' => $this->faker->sentence(),
        ];
    }
}
