<?php

use App\Models\Demande;
use App\Models\User;
use App\Models\Client;

describe('Gestion des demandes', function () {
    beforeEach(function () {
        $this->createur = User::factory()->create();
        $this->receveur = User::factory()->create();
        $this->client = Client::factory()->create();
    });

    describe('Index', function () {
        it('affiche la liste des demandes pour un utilisateur authentifié', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
            ]);

            $response = $this->actingAs($this->createur)
                ->get(route('demandes.index'));

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->component('Demandes/Index')
                    ->has('demandes.data', 1)
            );
        });

        it('filtre les demandes par rôle', function () {
            $demandeCreee = Demande::factory()->create([
                'createur_id' => $this->createur->id,
            ]);

            $demandeAssignee = Demande::factory()->create([
                'createur_id' => $this->receveur->id,
                'receveur_id' => $this->createur->id,
                'statut' => 'assignee',
            ]);

            $response = $this->actingAs($this->createur)
                ->get(route('demandes.index', ['role' => 'assignees']));

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->has('demandes.data', 1)
                    ->where('demandes.data.0.id', $demandeAssignee->id)
            );
        });
    });

    describe('Create', function () {
        it('affiche le formulaire de création', function () {
            $response = $this->actingAs($this->createur)
                ->get(route('demandes.create'));

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->component('Demandes/Create')
                    ->has('users')
                    ->has('clients')
            );
        });
    });

    describe('Store', function () {
        it('créateur peut créer une demande', function () {
            $data = [
                'titre' => 'Test Demande',
                'description' => 'Description de la demande',
                'priorite' => 'normale',
                'client_id' => $this->client->id,
                'lieu_intervention' => '123 Rue Test',
                'date_souhaite_intervention' => now()->addDays(1)->format('Y-m-d H:i:s'),
            ];

            $response = $this->actingAs($this->createur)
                ->post(route('demandes.store'), $data);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('demandes', [
                'titre' => 'Test Demande',
                'createur_id' => $this->createur->id,
                'statut' => 'en_attente',
            ]);
        });

        it('valide les données requises', function () {
            $response = $this->actingAs($this->createur)
                ->post(route('demandes.store'), []);

            $response->assertSessionHasErrors(['titre', 'description', 'priorite']);
        });

        it('valide la priorité', function () {
            $data = [
                'titre' => 'Test',
                'description' => 'Test',
                'priorite' => 'invalid',
            ];

            $response = $this->actingAs($this->createur)
                ->post(route('demandes.store'), $data);

            $response->assertSessionHasErrors(['priorite']);
        });
    });

    describe('Show', function () {
        it('affiche une demande pour le créateur', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
            ]);

            $response = $this->actingAs($this->createur)
                ->get(route('demandes.show', $demande));

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) => 
                $page->component('Demandes/Show')
                    ->where('demande.id', $demande->id)
            );
        });

        it('affiche une demande pour le receveur', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
            ]);

            $response = $this->actingAs($this->receveur)
                ->get(route('demandes.show', $demande));

            $response->assertStatus(200);
        });

        it('refuse l\'accès à un utilisateur non autorisé', function () {
            $autreUser = User::factory()->create();
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
            ]);

            $response = $this->actingAs($autreUser)
                ->get(route('demandes.show', $demande));

            $response->assertStatus(403);
        });
    });

    describe('Update', function () {
        it('créateur peut modifier une demande en attente', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'statut' => 'en_attente',
                'titre' => 'Titre original',
            ]);

            $data = [
                'titre' => 'Titre modifié',
                'description' => $demande->description,
                'priorite' => $demande->priorite,
                'statut' => $demande->statut,
            ];

            $response = $this->actingAs($this->createur)
                ->put(route('demandes.update', $demande), $data);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('demandes', [
                'id' => $demande->id,
                'titre' => 'Titre modifié',
            ]);
        });

        it('receveur peut modifier une demande assignée', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'assignee',
            ]);

            $data = [
                'titre' => $demande->titre,
                'description' => 'Description modifiée',
                'priorite' => $demande->priorite,
                'statut' => 'en_cours',
            ];

            $response = $this->actingAs($this->receveur)
                ->put(route('demandes.update', $demande), $data);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('demandes', [
                'id' => $demande->id,
                'statut' => 'en_cours',
            ]);
        });
    });

    describe('Assign', function () {
        it('créateur peut assigner une demande', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'statut' => 'en_attente',
            ]);

            $response = $this->actingAs($this->createur)
                ->post(route('demandes.assign', $demande), [
                    'receveur_id' => $this->receveur->id,
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('demandes', [
                'id' => $demande->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'assignee',
            ]);
        });

        it('receveur ne peut pas assigner une demande', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'en_attente',
            ]);

            $response = $this->actingAs($this->receveur)
                ->post(route('demandes.assign', $demande), [
                    'receveur_id' => $this->receveur->id,
                ]);

            $response->assertStatus(403);
        });
    });

    describe('Complete', function () {
        it('receveur peut compléter une demande assignée', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'assignee',
            ]);

            $response = $this->actingAs($this->receveur)
                ->post(route('demandes.complete', $demande), [
                    'notes_receveur' => 'Travail terminé avec succès',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('demandes', [
                'id' => $demande->id,
                'statut' => 'terminee',
                'notes_receveur' => 'Travail terminé avec succès',
            ]);
        });

        it('créateur ne peut pas compléter une demande', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'assignee',
            ]);

            $response = $this->actingAs($this->createur)
                ->post(route('demandes.complete', $demande));

            $response->assertStatus(403);
        });
    });

    describe('Convert to Attachement', function () {
        it('demande terminée peut être convertie en attachement', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'terminee',
            ]);

            $response = $this->actingAs($this->receveur)
                ->post(route('demandes.convert', $demande));

            $response->assertRedirect(route('attachements.create', ['from_demande' => $demande->id]));
        });

        it('demande non terminée ne peut pas être convertie', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'receveur_id' => $this->receveur->id,
                'statut' => 'assignee',
            ]);

            $response = $this->actingAs($this->receveur)
                ->post(route('demandes.convert', $demande));

            $response->assertRedirect();
            $response->assertSessionHas('error');
        });
    });

    describe('Delete', function () {
        it('créateur peut supprimer une demande en attente', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'statut' => 'en_attente',
            ]);

            $response = $this->actingAs($this->createur)
                ->delete(route('demandes.destroy', $demande));

            $response->assertRedirect();
            $this->assertDatabaseMissing('demandes', ['id' => $demande->id]);
        });

        it('créateur ne peut pas supprimer une demande assignée', function () {
            $demande = Demande::factory()->create([
                'createur_id' => $this->createur->id,
                'statut' => 'assignee',
            ]);

            $response = $this->actingAs($this->createur)
                ->delete(route('demandes.destroy', $demande));

            $response->assertStatus(403);
        });
    });
});