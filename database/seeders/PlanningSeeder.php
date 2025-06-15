<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Planning;
use App\Models\Employe;
use App\Models\Equipe;
use Carbon\Carbon;

class PlanningSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les employés par email
        $thomas = Employe::where('email', 't.durand@lesot-elec.fr')->first();
        $lucas = Employe::where('email', 'l.moreau@lesot-elec.fr')->first();
        $marie = Employe::where('email', 'm.rousseau@lesot-elec.fr')->first();
        $alexandre = Employe::where('email', 'a.bernard@lesot-elec.fr')->first();
        $romain = Employe::where('email', 'r.lambert@lesot-elec.fr')->first();
        $julie = Employe::where('email', 'j.petit@lesot-elec.fr')->first();
        $sophie = Employe::where('email', 's.martin@lesot-elec.fr')->first();
        
        // Récupérer les équipes
        $equipeInstallation = Equipe::where('code', 'INST-T')->first();
        $equipeMaintenance = Equipe::where('code', 'MAINT-I')->first();
        
        $plannings = [
            // Cette semaine - Interventions en cours
            [
                'employe_id' => $thomas->id,
                'equipe_id' => $equipeInstallation->id,
                'date_debut' => Carbon::now()->startOfWeek()->addDay(0)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDay(0)->addHours(6)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '08:00',
                'heure_fin_prevue' => '14:00',
                'type_affectation' => 'intervention',
                'statut' => 'termine',
                'lieu_intervention' => 'Centre Commercial Arras',
                'description_tache' => 'Installation éclairage LED magasin textile',
                'materiels_requis' => ['Spots LED', 'Câble 2.5mm²', 'Disjoncteurs'],
                'duree_estimee_minutes' => 360,
                'duree_reelle_minutes' => 340,
                'cree_par_id' => $sophie->id,
                'valide_par_id' => $sophie->id,
                'date_validation' => Carbon::now()->startOfWeek()->addDay(0)->addHours(7)->format('Y-m-d H:i:s'),
                'difficulte' => 'moyenne',
                'note_client' => 5,
                'objectifs_atteints' => true,
                'rapport_intervention' => 'Installation terminée avec succès. Client très satisfait.'
            ],

            [
                'employe_id' => $lucas->id,
                'equipe_id' => $equipeInstallation->id,
                'date_debut' => Carbon::now()->startOfWeek()->addDay(1)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDay(1)->addHours(8)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '08:00',
                'heure_fin_prevue' => '16:00',
                'type_affectation' => 'formation',
                'statut' => 'termine',
                'lieu_intervention' => 'Centre de formation Arras',
                'description_tache' => 'Formation habilitation B2V',
                'cree_par_id' => $sophie->id,
                'valide_par_id' => $sophie->id,
                'date_validation' => Carbon::now()->startOfWeek()->addDay(1)->addHours(8)->format('Y-m-d H:i:s'),
                'objectifs_atteints' => true
            ],

            [
                'employe_id' => $marie->id,
                'equipe_id' => $equipeMaintenance->id,
                'date_debut' => Carbon::now()->startOfWeek()->addDay(2)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDay(2)->addHours(4)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '07:30',
                'heure_fin_prevue' => '11:30',
                'type_affectation' => 'maintenance',
                'statut' => 'en_cours',
                'lieu_intervention' => 'Usine Lactalis - Beuvry',
                'description_tache' => 'Maintenance préventive ligne de production',
                'materiels_requis' => ['Multimètre', 'Outillage spécialisé'],
                'duree_estimee_minutes' => 240,
                'cree_par_id' => $sophie->id,
                'difficulte' => 'difficile'
            ],

            [
                'employe_id' => $alexandre->id,
                'equipe_id' => $equipeMaintenance->id,
                'date_debut' => Carbon::now()->startOfWeek()->addDay(3)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDay(3)->addHours(8)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '07:30',
                'heure_fin_prevue' => '15:30',
                'type_affectation' => 'intervention',
                'statut' => 'planifie',
                'lieu_intervention' => 'Schneider Electric - Carvin',
                'description_tache' => 'Dépannage variateur de vitesse',
                'materiels_requis' => ['Variateur de rechange', 'Outils de diagnostic'],
                'duree_estimee_minutes' => 480,
                'cree_par_id' => $sophie->id,
                'difficulte' => 'complexe'
            ],

            // Congé de Romain
            [
                'employe_id' => $romain->id,
                'date_debut' => Carbon::now()->startOfWeek()->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDays(4)->addHours(8)->format('Y-m-d H:i:s'),
                'type_affectation' => 'conge',
                'statut' => 'termine',
                'description_tache' => 'Congés payés',
                'cree_par_id' => $sophie->id,
                'valide_par_id' => $sophie->id,
                'date_validation' => Carbon::now()->startOfWeek()->subDays(7)->format('Y-m-d H:i:s'),
                'objectifs_atteints' => true
            ],

            // Formation apprentie
            [
                'employe_id' => $julie->id,
                'date_debut' => Carbon::now()->startOfWeek()->addDay(4)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addDay(4)->addHours(7)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '08:00',
                'heure_fin_prevue' => '15:00',
                'type_affectation' => 'formation',
                'statut' => 'planifie',
                'lieu_intervention' => 'CFA Arras',
                'description_tache' => 'Cours théoriques - Normes électriques',
                'cree_par_id' => $sophie->id
            ],

            // Semaine prochaine
            [
                'employe_id' => $thomas->id,
                'equipe_id' => $equipeInstallation->id,
                'date_debut' => Carbon::now()->startOfWeek()->addWeek(1)->addDay(0)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addWeek(1)->addDay(1)->addHours(8)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '08:00',
                'heure_fin_prevue' => '16:00',
                'type_affectation' => 'intervention',
                'statut' => 'planifie',
                'lieu_intervention' => 'Leclerc Arras',
                'description_tache' => 'Installation borne de recharge électrique',
                'materiels_requis' => ['Borne de recharge', 'Câble 16mm²'],
                'duree_estimee_minutes' => 960,
                'cree_par_id' => $sophie->id,
                'difficulte' => 'moyenne'
            ],

            [
                'employe_id' => $marie->id,
                'equipe_id' => $equipeMaintenance->id,
                'date_debut' => Carbon::now()->startOfWeek()->addWeek(1)->addDay(2)->format('Y-m-d H:i:s'),
                'date_fin' => Carbon::now()->startOfWeek()->addWeek(1)->addDay(2)->addHours(4)->format('Y-m-d H:i:s'),
                'heure_debut_prevue' => '07:30',
                'heure_fin_prevue' => '11:30',
                'type_affectation' => 'astreinte',
                'statut' => 'planifie',
                'description_tache' => 'Astreinte technique - Disponibilité dépannage',
                'cree_par_id' => $sophie->id
            ]
        ];

        foreach ($plannings as $planningData) {
            Planning::create($planningData);
        }
    }
}