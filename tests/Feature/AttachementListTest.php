<?php

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\AttachementSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('affichage liste attachements', function () {
    // Créer un utilisateur et se connecter
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Créer quelques attachements de test
    Attachement::factory(15)->create();
    
    $response = $this->get(route('attachements.index'));
    
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data')
            ->where('attachements.total', 15)
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('pagination liste attachements', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Créer plus d'attachements que la pagination par défaut
    Attachement::factory(25)->create();
    
    $response = $this->get(route('attachements.index'));
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data', 15) // Par défaut 15 par page
            ->where('attachements.total', 25)
            ->has('attachements.links') // Liens de pagination
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('recherche dans liste attachements', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Créer des attachements avec des données spécifiques
    $specificClient = Client::factory()->create(['nom' => 'Dupont Jean']);
    Attachement::factory()->create([
        'client_id' => $specificClient->id,
        'client_nom' => $specificClient->nom,
        'numero_dossier' => 'ATT-20250612-TEST'
    ]);
    
    // Créer d'autres attachements
    Attachement::factory(5)->create();
    
    // Test recherche par nom client
    $response = $this->get(route('attachements.index', ['search' => 'Dupont']));
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data', 1)
            ->where('attachements.data.0.client_nom', 'Dupont Jean')
    );
    
    // Test recherche par numéro de dossier
    $response = $this->get(route('attachements.index', ['search' => 'TEST']));
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data', 1)
            ->where('attachements.data.0.numero_dossier', 'ATT-20250612-TEST')
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('filtrage par date attachements', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Créer des attachements avec des dates spécifiques
    $recentAttachement = Attachement::factory()->create([
        'date_intervention' => now()->subDays(2)->format('Y-m-d')
    ]);
    
    $oldAttachement = Attachement::factory()->create([
        'date_intervention' => now()->subMonths(2)->format('Y-m-d')
    ]);
    
    // Filtrer par date récente (7 derniers jours)
    $response = $this->get(route('attachements.index', [
        'date_debut' => now()->subWeek()->format('Y-m-d'),
        'date_fin' => now()->format('Y-m-d')
    ]));
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data', 1)
            ->where('attachements.data.0.id', $recentAttachement->id)
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('affichage détail attachement', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $attachement = Attachement::factory()->create();
    
    $response = $this->get(route('attachements.show', $attachement));
    
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Show')
            ->has('attachement')
            ->where('attachement.id', $attachement->id)
            ->where('attachement.numero_dossier', $attachement->numero_dossier)
            ->has('attachement.client')
            ->has('attachement.fournitures_travaux')
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('affichage avec données du seeder', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Exécuter le seeder
    $this->seed(AttachementSeeder::class);
    
    $response = $this->get(route('attachements.index'));
    
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data')
            ->where('attachements.total', function ($total) {
                return $total >= 50; // Le seeder crée au moins 50 attachements
            })
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('structure des données inertia', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $attachement = Attachement::factory()->create();
    
    $response = $this->get(route('attachements.index'));
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data.0', fn (Assert $attachement) => 
                $attachement->has('id')
                    ->has('numero_dossier')
                    ->has('client_nom')
                    ->has('date_intervention')
                    ->has('designation_travaux')
                    ->has('temps_total_passe')
                    ->has('client')
                    ->has('fournitures_travaux')
                    ->has('geolocation')
                    ->has('created_at')
            )
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('autorisation accès attachements', function () {
    // Test sans authentification
    $response = $this->get(route('attachements.index'));
    $response->assertRedirect(route('login'));
    
    // Test avec utilisateur authentifié
    $user = User::factory()->create();
    $this->actingAs($user);
    
    Attachement::factory(3)->create();
    
    $response = $this->get(route('attachements.index'));
    $response->assertStatus(200);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('performance affichage liste', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Créer beaucoup d'attachements pour tester les performances
    Attachement::factory(100)->create();
    
    $start = microtime(true);
    
    $response = $this->get(route('attachements.index'));
    
    $duration = microtime(true) - $start;
    
    $response->assertStatus(200);
    expect($duration)->toBeLessThan(2); // Moins de 2 secondes
    
    $response->assertInertia(fn (Assert $page) => 
        $page->component('Attachements/Index')
            ->has('attachements.data', 15) // Pagination limite à 15
            ->where('attachements.total', 100)
    );
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);