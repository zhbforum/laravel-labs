<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'author_id'   => User::factory(),
            'assignee_id' => User::factory(),
            'title'       => rtrim($this->faker->sentence(5), '.'),
            'description' => $this->faker->paragraph(),
            'status'      => $this->faker->randomElement(['open', 'in_progress', 'done', 'archived']),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date'    => $this->faker->date('Y-m-d'),
        ];
    }
}
