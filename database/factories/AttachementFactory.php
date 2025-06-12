<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachement>
 */
class AttachementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::factory()->create();
        $dateIntervention = $this->faker->dateTimeBetween('-1 year', 'now');
        
        // Génération de fournitures réalistes
        $fournitures = [];
        $nbFournitures = $this->faker->numberBetween(1, 5);
        
        for ($i = 0; $i < $nbFournitures; $i++) {
            $fournitures[] = [
                'designation' => $this->faker->randomElement([
                    'Plomberie - Réparation fuite',
                    'Électricité - Installation prise',
                    'Carrelage - Pose au m²',
                    'Peinture - Mur intérieur',
                    'Chauffage - Entretien radiateur',
                    'Serrurerie - Changement serrure',
                    'Menuiserie - Pose fenêtre',
                    'Isolation - Combles perdus'
                ]),
                'quantite' => $this->faker->randomElement([
                    '1 unité',
                    '2 heures',
                    '5 m²',
                    '3 prises',
                    '10 ml',
                    '1 forfait'
                ]),
                'observations' => $this->faker->optional(0.6)->sentence()
            ];
        }

        // Données de géolocalisation réalistes (région Nord-Pas-de-Calais)
        $geolocation = $this->faker->optional(0.8)->passthrough([
            'latitude' => $this->faker->latitude(49.5, 51.1),
            'longitude' => $this->faker->longitude(1.5, 4.2),
            'timestamp' => $dateIntervention->format('c')
        ]);

        return [
            'client_id' => $client->id,
            'numero_dossier' => 'ATT-' . $dateIntervention->format('Ymd') . '-' . strtoupper($this->faker->lexify('????')),
            'client_nom' => $client->nom,
            'nom_signataire_client' => $this->faker->optional(0.7)->name(),
            'client_email' => $client->email,
            'client_adresse_facturation' => $client->adresse . ($client->complement_adresse ? ', ' . $client->complement_adresse : '') . ', ' . $client->code_postal . ' ' . $client->ville,
            'lieu_intervention' => $this->faker->randomElement([
                $client->adresse . ', ' . $client->ville, // Même adresse que le client
                $this->faker->streetAddress() . ', ' . $this->faker->city(), // Adresse différente
            ]),
            'date_intervention' => $dateIntervention->format('Y-m-d'),
            'designation_travaux' => $this->faker->randomElement([
                'Travaux de plomberie urgents',
                'Installation électrique conforme',
                'Rénovation partielle salle de bain',
                'Maintenance préventive chauffage',
                'Réparation menuiserie extérieure',
                'Travaux de carrelage cuisine',
                'Isolation combles et murs',
                'Dépannage serrurerie'
            ]),
            'fournitures_travaux' => $fournitures,
            'temps_total_passe' => $this->faker->randomFloat(2, 0.5, 8), // Entre 30min et 8h
            'signature_entreprise_path' => 'signatures/' . $dateIntervention->format('Y/m') . '/entreprise_' . $this->faker->uuid() . '.png',
            'signature_client_path' => 'signatures/' . $dateIntervention->format('Y/m') . '/client_' . $this->faker->uuid() . '.png',
            'pdf_path' => 'pdf/' . $dateIntervention->format('Y/m') . '/' . $this->faker->uuid() . '.pdf',
            'geolocation' => $geolocation,
            'created_by' => User::factory(),
            'created_at' => $dateIntervention,
            'updated_at' => $this->faker->dateTimeBetween($dateIntervention, 'now'),
        ];
    }

    /**
     * État pour un attachement avec signature client manquante
     */
    public function withoutClientSignature(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom_signataire_client' => null,
            'signature_client_path' => null,
        ]);
    }

    /**
     * État pour un attachement récent (moins de 7 jours)
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $recentDate = $this->faker->dateTimeBetween('-7 days', 'now');
            return [
                'date_intervention' => $recentDate->format('Y-m-d'),
                'created_at' => $recentDate,
                'updated_at' => $this->faker->dateTimeBetween($recentDate, 'now'),
            ];
        });
    }

    /**
     * État pour un attachement avec beaucoup de fournitures
     */
    public function withManySupplies(): static
    {
        return $this->state(function (array $attributes) {
            $fournitures = [];
            $nbFournitures = $this->faker->numberBetween(8, 15);
            
            for ($i = 0; $i < $nbFournitures; $i++) {
                $fournitures[] = [
                    'designation' => $this->faker->words(3, true),
                    'quantite' => $this->faker->numberBetween(1, 50) . ' ' . $this->faker->randomElement(['unités', 'm²', 'ml', 'heures']),
                    'observations' => $this->faker->optional(0.4)->sentence()
                ];
            }

            return [
                'fournitures_travaux' => $fournitures,
                'temps_total_passe' => $this->faker->randomFloat(2, 4, 16), // Intervention plus longue
            ];
        });
    }

    /**
     * État pour un attachement d'urgence
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'designation_travaux' => 'URGENCE - ' . $this->faker->randomElement([
                'Fuite d\'eau importante',
                'Panne électrique totale',
                'Chaudière en panne',
                'Serrure bloquée',
                'Vitre cassée'
            ]),
            'temps_total_passe' => $this->faker->randomFloat(2, 0.25, 2), // Intervention rapide
        ]);
    }
}
