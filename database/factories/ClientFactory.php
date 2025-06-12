<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Villes de la région Nord-Pas-de-Calais pour plus de réalisme
        $villes = [
            ['ville' => 'Lille', 'code_postal' => '59000'],
            ['ville' => 'Arras', 'code_postal' => '62000'],
            ['ville' => 'Calais', 'code_postal' => '62100'],
            ['ville' => 'Lens', 'code_postal' => '62300'],
            ['ville' => 'Béthune', 'code_postal' => '62400'],
            ['ville' => 'Boulogne-sur-Mer', 'code_postal' => '62200'],
            ['ville' => 'Douai', 'code_postal' => '59500'],
            ['ville' => 'Valenciennes', 'code_postal' => '59300'],
            ['ville' => 'Dunkerque', 'code_postal' => '59140'],
            ['ville' => 'Cambrai', 'code_postal' => '59400'],
            ['ville' => 'Saint-Omer', 'code_postal' => '62500'],
            ['ville' => 'Liévin', 'code_postal' => '62800'],
        ];

        $villeData = $this->faker->randomElement($villes);

        return [
            'nom' => $this->faker->randomElement([
                // Particuliers
                $this->faker->lastName() . ' ' . $this->faker->firstName(),
                $this->faker->firstName() . ' ' . $this->faker->lastName(),
                // Entreprises
                'SARL ' . $this->faker->company(),
                'SAS ' . $this->faker->company(),
                'Entreprise ' . $this->faker->lastName(),
                $this->faker->company() . ' & Fils',
                'Copropriété ' . $this->faker->streetName(),
            ]),
            'email' => $this->faker->unique()->safeEmail(),
            'adresse' => $this->faker->buildingNumber() . ' ' . $this->faker->streetName(),
            'complement_adresse' => $this->faker->optional(0.3)->randomElement([
                'Appartement ' . $this->faker->buildingNumber(),
                'Bâtiment ' . $this->faker->randomLetter(),
                'Étage ' . $this->faker->numberBetween(1, 10),
                'Résidence ' . $this->faker->lastName(),
                'Lieu-dit ' . $this->faker->word(),
            ]),
            'code_postal' => $villeData['code_postal'],
            'ville' => $villeData['ville'],
            'telephone' => $this->faker->optional(0.9)->phoneNumber(),
            'notes' => $this->faker->optional(0.4)->sentence(),
        ];
    }

    /**
     * État pour un client entreprise
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $this->faker->randomElement([
                'SARL ' . $this->faker->company(),
                'SAS ' . $this->faker->company(),
                'EURL ' . $this->faker->lastName(),
                $this->faker->company() . ' & Associés',
                'Entreprise ' . $this->faker->lastName(),
            ]),
            'notes' => 'Client professionnel - ' . $this->faker->sentence(),
        ]);
    }

    /**
     * État pour un client particulier
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ]);
    }

    /**
     * État pour un client avec beaucoup d'historique
     */
    public function withHistory(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => 'Client fidèle depuis ' . $this->faker->year() . ' - ' . $this->faker->sentence(),
        ]);
    }
}
