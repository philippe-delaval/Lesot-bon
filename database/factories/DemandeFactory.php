<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Demande>
 */
class DemandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'priorite' => $this->faker->randomElement(['normale', 'haute', 'urgente']),
            'statut' => $this->faker->randomElement(['en_attente', 'assignee', 'en_cours', 'terminee']),
            'createur_id' => User::factory(),
            'receveur_id' => null,
            'client_id' => null,
            'lieu_intervention' => $this->faker->address(),
            'date_demande' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'date_souhaite_intervention' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'date_assignation' => null,
            'date_completion' => null,
            'notes_receveur' => null,
            'attachement_id' => null,
        ];
    }

    /**
     * Indicate that the demande is assigned.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'assignee',
            'receveur_id' => User::factory(),
            'date_assignation' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the demande is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'terminee',
            'receveur_id' => User::factory(),
            'date_assignation' => $this->faker->dateTimeBetween('-1 week', '-2 days'),
            'date_completion' => $this->faker->dateTimeBetween('-2 days', 'now'),
            'notes_receveur' => $this->faker->optional()->paragraph(),
        ]);
    }

    /**
     * Indicate that the demande is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priorite' => 'urgente',
        ]);
    }

    /**
     * Indicate that the demande has a client.
     */
    public function withClient(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => Client::factory(),
        ]);
    }
}
