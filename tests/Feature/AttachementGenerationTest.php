<?php

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\AttachementSeeder;
use Illuminate\Support\Carbon;

test('génération basique d\'attachements', function () {
    // Créer un utilisateur de test
    User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@test.com',
    ]);

    // Test avec 10 attachements
    $attachements = Attachement::factory(10)->create();
    
    expect($attachements)->toHaveCount(10);
    expect(Attachement::count())->toBe(10);

    // Vérification des relations
    $firstAttachement = $attachements->first();
    expect($firstAttachement->client)->toBeInstanceOf(Client::class);
    expect($firstAttachement->creator)->toBeInstanceOf(User::class);
    
    // Vérification des champs obligatoires
    expect($firstAttachement->numero_dossier)->not->toBeNull();
    expect($firstAttachement->client_nom)->not->toBeNull();
    expect($firstAttachement->client_email)->not->toBeNull();
    expect($firstAttachement->date_intervention)->not->toBeNull();
    expect($firstAttachement->designation_travaux)->not->toBeNull();
    expect($firstAttachement->fournitures_travaux)->not->toBeNull();
    expect($firstAttachement->temps_total_passe)->not->toBeNull();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('génération avec états spécifiques', function () {
    // Créer un utilisateur de test
    User::factory()->create();

    // Attachements récents
    $recentAttachements = Attachement::factory(3)->recent()->create();
    
    foreach ($recentAttachements as $attachement) {
        expect(Carbon::parse($attachement->date_intervention))
            ->toBeGreaterThanOrEqualTo(now()->subDays(7));
    }

    // Attachements d'urgence
    $emergencyAttachements = Attachement::factory(2)->emergency()->create();
    
    foreach ($emergencyAttachements as $attachement) {
        expect($attachement->designation_travaux)->toStartWith('URGENCE');
        expect($attachement->temps_total_passe)->toBeLessThanOrEqual(2.0);
    }

    // Attachements sans signature client
    $withoutSignature = Attachement::factory(2)->withoutClientSignature()->create();
    
    foreach ($withoutSignature as $attachement) {
        expect($attachement->nom_signataire_client)->toBeNull();
        expect($attachement->signature_client_path)->toBeNull();
    }

    // Attachements avec beaucoup de fournitures
    $withManySupplies = Attachement::factory(1)->withManySupplies()->create();
    $attachement = $withManySupplies->first();
    
    expect(count($attachement->fournitures_travaux))->toBeGreaterThanOrEqual(8);
    expect($attachement->temps_total_passe)->toBeGreaterThanOrEqual(4.0);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('structure des données générées', function () {
    User::factory()->create();
    $attachement = Attachement::factory()->create();

    // Vérification de la structure du numéro de dossier
    expect($attachement->numero_dossier)->toMatch('/^ATT-\d{8}-[A-Z]{4}$/');

    // Vérification de la structure des fournitures
    expect($attachement->fournitures_travaux)->toBeArray();
    expect($attachement->fournitures_travaux)->not->toBeEmpty();
    
    foreach ($attachement->fournitures_travaux as $fourniture) {
        expect($fourniture)->toHaveKey('designation');
        expect($fourniture)->toHaveKey('quantite');
        expect($fourniture)->toHaveKey('observations');
    }

    // Vérification des chemins de fichiers
    expect($attachement->signature_entreprise_path)->toStartWith('signatures/');
    expect($attachement->signature_client_path)->toStartWith('signatures/');
    expect($attachement->pdf_path)->toStartWith('pdf/');

    // Vérification de la géolocalisation (si présente)
    if ($attachement->geolocation) {
        expect($attachement->geolocation)->toHaveKey('latitude');
        expect($attachement->geolocation)->toHaveKey('longitude');
        expect($attachement->geolocation)->toHaveKey('timestamp');
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('performance de génération', function () {
    User::factory()->create();
    
    $start = microtime(true);
    
    // Générer 100 attachements
    Attachement::factory(100)->create();
    
    $duration = microtime(true) - $start;
    
    expect(Attachement::count())->toBe(100);
    expect($duration)->toBeLessThan(15); // 15 secondes max pour 100 attachements
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('génération avec clients existants', function () {
    // Créer des clients à réutiliser
    $clients = Client::factory(5)->create();
    $user = User::factory()->create();

    $attachements = collect();
    for ($i = 0; $i < 20; $i++) {
        $client = $clients->random();
        $attachement = Attachement::factory()->create([
            'client_id' => $client->id,
            'client_nom' => $client->nom,
            'client_email' => $client->email,
            'created_by' => $user->id,
        ]);
        $attachements->push($attachement);
    }

    expect($attachements)->toHaveCount(20);
    
    // Vérifier que nous avons utilisé seulement les 5 clients créés
    $uniqueClientIds = $attachements->pluck('client_id')->unique();
    expect($uniqueClientIds->count())->toBeLessThanOrEqual(5);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('cohérence des données client-attachement', function () {
    User::factory()->create();
    $attachement = Attachement::factory()->create();
    $client = $attachement->client;

    // Vérifier que les données client sont cohérentes
    expect($attachement->client_nom)->toBe($client->nom);
    expect($attachement->client_email)->toBe($client->email);
    
    // L'adresse de facturation doit contenir les éléments de l'adresse client
    expect($attachement->client_adresse_facturation)->toContain($client->adresse);
    expect($attachement->client_adresse_facturation)->toContain($client->code_postal);
    expect($attachement->client_adresse_facturation)->toContain($client->ville);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('contraintes métier', function () {
    User::factory()->create();
    $attachements = Attachement::factory(10)->create();

    foreach ($attachements as $attachement) {
        // Le temps total passé doit être positif
        expect($attachement->temps_total_passe)->toBeGreaterThan(0);
        
        // La date d'intervention ne doit pas être dans le futur
        expect(Carbon::parse($attachement->date_intervention))->toBeLessThanOrEqual(now());
        
        // Le numéro de dossier doit être unique
        $duplicates = Attachement::where('numero_dossier', $attachement->numero_dossier)
                                ->where('id', '!=', $attachement->id)
                                ->count();
        expect($duplicates)->toBe(0);
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('exécution du seeder', function () {
    $this->seed(AttachementSeeder::class);

    // Vérifier qu'au moins 50 attachements ont été créés
    expect(Attachement::count())->toBeGreaterThanOrEqual(50);
    
    // Vérifier qu'il y a des attachements récents
    $recentCount = Attachement::where('created_at', '>=', now()->subDays(7))->count();
    expect($recentCount)->toBeGreaterThan(0);
    
    // Vérifier qu'il y a des clients et utilisateurs
    expect(Client::count())->toBeGreaterThan(0);
    expect(User::count())->toBeGreaterThan(0);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
