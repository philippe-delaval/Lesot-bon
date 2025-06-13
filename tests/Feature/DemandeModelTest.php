<?php

use App\Models\Demande;
use App\Models\User;
use App\Models\Client;
use App\Models\Attachement;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Modèle Demande', function () {
    it('génère automatiquement le numéro de demande', function () {
        $user = User::factory()->create();
        
        $demande = Demande::factory()->create([
            'createur_id' => $user->id,
        ]);
        
        expect($demande->numero_demande)->toMatch('/^DEM-\d{4}-\d{3}$/');
    });

    it('génère des numéros de demande séquentiels', function () {
        $user = User::factory()->create();
        
        $demande1 = Demande::factory()->create(['createur_id' => $user->id]);
        $demande2 = Demande::factory()->create(['createur_id' => $user->id]);
        
        expect($demande1->numero_demande)->toMatch('/^DEM-\d{4}-001$/');
        expect($demande2->numero_demande)->toMatch('/^DEM-\d{4}-002$/');
    });

    it('a les relations définies', function () {
        $createur = User::factory()->create();
        $receveur = User::factory()->create();
        $client = Client::factory()->create();
        
        $demande = Demande::factory()->create([
            'createur_id' => $createur->id,
            'receveur_id' => $receveur->id,
            'client_id' => $client->id,
        ]);
        
        expect($demande->createur)->toBeInstanceOf(User::class);
        expect($demande->receveur)->toBeInstanceOf(User::class);
        expect($demande->client)->toBeInstanceOf(Client::class);
        expect($demande->createur->id)->toBe($createur->id);
        expect($demande->receveur->id)->toBe($receveur->id);
        expect($demande->client->id)->toBe($client->id);
    });

    it('scope enCours fonctionne correctement', function () {
        $user = User::factory()->create();
        
        $demandeEnAttente = Demande::factory()->create([
            'createur_id' => $user->id,
            'statut' => 'en_attente'
        ]);
        
        $demandeTerminee = Demande::factory()->create([
            'createur_id' => $user->id,
            'statut' => 'terminee'
        ]);
        
        $demandeAnnulee = Demande::factory()->create([
            'createur_id' => $user->id,
            'statut' => 'annulee'
        ]);
        
        $demandesEnCours = Demande::enCours()->get();
        
        expect($demandesEnCours)->toHaveCount(1);
        expect($demandesEnCours->first()->id)->toBe($demandeEnAttente->id);
    });

    it('scope assigneesA fonctionne correctement', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $demandeAssignee = Demande::factory()->create([
            'createur_id' => $user1->id,
            'receveur_id' => $user2->id,
            'statut' => 'assignee'
        ]);
        
        $demandeNonAssignee = Demande::factory()->create([
            'createur_id' => $user1->id,
            'statut' => 'en_attente'
        ]);
        
        $demandesAssignees = Demande::assigneesA($user2->id)->get();
        
        expect($demandesAssignees)->toHaveCount(1);
        expect($demandesAssignees->first()->id)->toBe($demandeAssignee->id);
    });

    it('scope creesPar fonctionne correctement', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $demandeUser1 = Demande::factory()->create([
            'createur_id' => $user1->id,
        ]);
        
        $demandeUser2 = Demande::factory()->create([
            'createur_id' => $user2->id,
        ]);
        
        $demandesUser1 = Demande::creesPar($user1->id)->get();
        
        expect($demandesUser1)->toHaveCount(1);
        expect($demandesUser1->first()->id)->toBe($demandeUser1->id);
    });

    it('retourne les bonnes classes CSS pour les badges', function () {
        $demande = new Demande();
        
        $demande->statut = 'en_attente';
        expect($demande->statut_badge)->toBe('bg-yellow-100 text-yellow-800');
        
        $demande->statut = 'terminee';
        expect($demande->statut_badge)->toBe('bg-green-100 text-green-800');
        
        $demande->priorite = 'urgente';
        expect($demande->priorite_color)->toBe('bg-red-100 text-red-600');
        
        $demande->priorite = 'normale';
        expect($demande->priorite_color)->toBe('bg-blue-100 text-blue-600');
    });

    it('peut être liée à un attachement', function () {
        $user = User::factory()->create();
        $attachement = Attachement::factory()->create();
        
        $demande = Demande::factory()->create([
            'createur_id' => $user->id,
            'attachement_id' => $attachement->id,
        ]);
        
        expect($demande->attachement)->toBeInstanceOf(Attachement::class);
        expect($demande->attachement->id)->toBe($attachement->id);
    });
});