<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 months', '-1 week');
        $end   = (clone $start)->modify('+7 days');

        return [
            'period_start' => $start->format('Y-m-d'),
            'period_end'   => $end->format('Y-m-d'),
            'payload'      => ['kpi' => ['tasks_done' => rand(5, 30), 'comments' => rand(10, 50)]],
            'path'         => $this->faker->filePath(),
        ];
    }
}
