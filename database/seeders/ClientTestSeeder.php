<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'nom' => 'Martin Dupont',
                'email' => 'martin.dupont@email.com',
                'adresse' => '123 rue de la République',
                'complement_adresse' => 'Appartement 5',
                'code_postal' => '62000',
                'ville' => 'Arras',
                'telephone' => '03.21.55.44.33',
                'notes' => 'Client privilégié depuis 2020'
            ],
            [
                'nom' => 'Sophie Legrand',
                'email' => 'sophie.legrand@entreprise.fr',
                'adresse' => '45 avenue des Tilleuls',
                'complement_adresse' => null,
                'code_postal' => '62300',
                'ville' => 'Lens',
                'telephone' => '06.12.34.56.78',
                'notes' => 'Entreprise de construction'
            ],
            [
                'nom' => 'Pierre Morel',
                'email' => 'p.morel@example.com',
                'adresse' => '67 boulevard Victor Hugo',
                'complement_adresse' => 'Bâtiment B',
                'code_postal' => '62100',
                'ville' => 'Calais',
                'telephone' => '03.21.96.85.74',
                'notes' => null
            ],
            [
                'nom' => 'Marie Dubois',
                'email' => 'marie.dubois@gmail.com',
                'adresse' => '12 place de la Mairie',
                'complement_adresse' => null,
                'code_postal' => '62200',
                'ville' => 'Boulogne-sur-Mer',
                'telephone' => '06.87.65.43.21',
                'notes' => 'Travaux de rénovation réguliers'
            ],
            [
                'nom' => 'Jean-Claude Vandamme',
                'email' => 'jc.vandamme@hotmail.fr',
                'adresse' => '89 rue Pasteur',
                'complement_adresse' => 'Étage 2',
                'code_postal' => '62400',
                'ville' => 'Béthune',
                'telephone' => '03.21.68.97.85',
                'notes' => 'Préfère les rendez-vous le matin'
            ],
            [
                'nom' => 'Entreprise Constructions SA',
                'email' => 'contact@constructions-sa.fr',
                'adresse' => '156 zone industrielle Nord',
                'complement_adresse' => 'Entrepôt 7',
                'code_postal' => '62160',
                'ville' => 'Bully-les-Mines',
                'telephone' => '03.21.77.88.99',
                'notes' => 'Client professionnel - facturation entreprise'
            ],
            [
                'nom' => 'Isabelle Roux',
                'email' => 'isabelle.roux@yahoo.fr',
                'adresse' => '34 impasse des Roses',
                'complement_adresse' => null,
                'code_postal' => '62800',
                'ville' => 'Liévin',
                'telephone' => '06.45.67.89.12',
                'notes' => 'Nouveau client - contact via recommandation'
            ],
            [
                'nom' => 'SAS Rénovation Plus',
                'email' => 'devis@renovation-plus.com',
                'adresse' => '78 rue du Commerce',
                'complement_adresse' => 'Local commercial',
                'code_postal' => '62110',
                'ville' => 'Hénin-Beaumont',
                'telephone' => '03.21.45.67.89',
                'notes' => 'Partenaire sous-traitant pour gros chantiers'
            ]
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}