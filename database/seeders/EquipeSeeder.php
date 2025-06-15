<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipe;
use App\Models\Employe;

class EquipeSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les IDs des chargés de projet
        $sophieMartin = Employe::where('email', 's.martin@lesot-elec.fr')->first();
        $pierreLeroy = Employe::where('email', 'p.leroy@lesot-elec.fr')->first();
        
        $equipes = [
            [
                'nom' => 'Équipe Installation Tertiaire',
                'code' => 'INST-T',
                'description' => 'Équipe spécialisée dans l\'installation électrique pour le secteur tertiaire',
                'charge_projet_id' => $sophieMartin->id,
                'specialisation' => 'tertiaire',
                'capacite_max' => 4,
                'competences_requises' => ['Installation électrique', 'Tableau électrique', 'Éclairage', 'Domotique'],
                'vehicules_attribues' => ['Renault Kangoo - AB-123-CD', 'Peugeot Partner - EF-456-GH'],
                'zones_intervention' => ['Saint-Laurent-Blangy', 'Arras', 'Lens', 'Béthune'],
                'horaire_debut' => '08:00',
                'horaire_fin' => '17:00',
                'active' => true
            ],
            [
                'nom' => 'Équipe Maintenance Industrielle',
                'code' => 'MAINT-I',
                'description' => 'Équipe de maintenance pour installations industrielles',
                'charge_projet_id' => $pierreLeroy->id,
                'specialisation' => 'industriel',
                'capacite_max' => 3,
                'competences_requises' => ['Maintenance préventive', 'Automatisme', 'Câblage industriel', 'Dépannage'],
                'vehicules_attribues' => ['Ford Transit - IJ-789-KL'],
                'zones_intervention' => ['Zone industrielle Arras', 'Douai', 'Cambrai'],
                'horaire_debut' => '07:30',
                'horaire_fin' => '16:30',
                'active' => true
            ],
            [
                'nom' => 'Équipe Dépannage Urgence',
                'code' => 'DEP-URG',
                'description' => 'Équipe d\'intervention rapide pour dépannages urgents',
                'charge_projet_id' => $sophieMartin->id,
                'specialisation' => 'depannage_urgence',
                'capacite_max' => 2,
                'competences_requises' => ['Dépannage', 'Diagnostic', 'Intervention rapide'],
                'vehicules_attribues' => ['Citroën Berlingo - MN-234-OP'],
                'zones_intervention' => ['Pas-de-Calais', 'Nord'],
                'horaire_debut' => '08:00',
                'horaire_fin' => '18:00',
                'active' => true
            ],
            [
                'nom' => 'Équipe Particuliers',
                'code' => 'PART',
                'description' => 'Équipe dédiée aux interventions chez les particuliers',
                'charge_projet_id' => $sophieMartin->id,
                'specialisation' => 'particulier',
                'capacite_max' => 3,
                'competences_requises' => ['Installation électrique', 'Dépannage', 'Mise en conformité'],
                'vehicules_attribues' => ['Renault Express - QR-567-ST'],
                'zones_intervention' => ['Saint-Laurent-Blangy', 'Arras', 'Achicourt', 'Dainville'],
                'horaire_debut' => '08:30',
                'horaire_fin' => '17:30',
                'active' => true
            ]
        ];

        foreach ($equipes as $equipeData) {
            $equipe = Equipe::create($equipeData);
        }

        // Affecter les employés aux équipes
        $this->affecterEmployesAuxEquipes();
    }

    private function affecterEmployesAuxEquipes(): void
    {
        // Récupérer les employés et équipes
        $thomasDurand = Employe::where('email', 't.durand@lesot-elec.fr')->first();
        $lucasMoreau = Employe::where('email', 'l.moreau@lesot-elec.fr')->first();
        $carlosGarcia = Employe::where('email', 'c.garcia@interim.fr')->first();
        $juliePetit = Employe::where('email', 'j.petit@lesot-elec.fr')->first();
        $marieRousseau = Employe::where('email', 'm.rousseau@lesot-elec.fr')->first();
        $alexandreBernard = Employe::where('email', 'a.bernard@lesot-elec.fr')->first();
        
        $equipeInstallation = Equipe::where('code', 'INST-T')->first();
        $equipeMaintenance = Equipe::where('code', 'MAINT-I')->first();
        
        $affectations = [
            // Équipe Installation Tertiaire
            [
                'equipe_id' => $equipeInstallation->id,
                'employe_id' => $thomasDurand->id,
                'role_equipe' => 'chef_equipe',
                'date_debut_affectation' => '2024-01-01',
                'active' => true
            ],
            [
                'equipe_id' => $equipeInstallation->id,
                'employe_id' => $lucasMoreau->id,
                'role_equipe' => 'membre',
                'date_debut_affectation' => '2024-01-01',
                'active' => true
            ],
            [
                'equipe_id' => $equipeInstallation->id,
                'employe_id' => $carlosGarcia->id,
                'role_equipe' => 'membre',
                'date_debut_affectation' => '2024-01-15',
                'active' => true
            ],
            [
                'equipe_id' => $equipeInstallation->id,
                'employe_id' => $juliePetit->id,
                'role_equipe' => 'apprenti',
                'date_debut_affectation' => '2024-09-01',
                'active' => true
            ],

            // Équipe Maintenance Industrielle
            [
                'equipe_id' => $equipeMaintenance->id,
                'employe_id' => $marieRousseau->id,
                'role_equipe' => 'chef_equipe',
                'date_debut_affectation' => '2024-01-01',
                'active' => true
            ],
            [
                'equipe_id' => $equipeMaintenance->id,
                'employe_id' => $alexandreBernard->id,
                'role_equipe' => 'membre',
                'date_debut_affectation' => '2024-01-01',
                'active' => true
            ],
        ];

        foreach ($affectations as $affectation) {
            \DB::table('employe_equipe')->insert(array_merge($affectation, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}