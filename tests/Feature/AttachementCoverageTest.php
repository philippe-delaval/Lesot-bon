<?php

use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\AttachementSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

test('couverture complète factory et seeder', function () {
    // Test de tous les états de la factory
    $user = User::factory()->create();
    
    // État par défaut
    $defaultAttachement = Attachement::factory()->create();
    expect($defaultAttachement)->toBeInstanceOf(Attachement::class);
    expect($defaultAttachement->client)->toBeInstanceOf(Client::class);
    expect($defaultAttachement->creator)->toBeInstanceOf(User::class);
    
    // État withoutClientSignature
    $withoutSignature = Attachement::factory()->withoutClientSignature()->create();
    expect($withoutSignature->nom_signataire_client)->toBeNull();
    expect($withoutSignature->signature_client_path)->toBeNull();
    
    // État recent
    $recent = Attachement::factory()->recent()->create();
    expect($recent->date_intervention)->toBeGreaterThanOrEqual(now()->subDays(7)->format('Y-m-d'));
    
    // État withManySupplies
    $manySupplies = Attachement::factory()->withManySupplies()->create();
    expect(count($manySupplies->fournitures_travaux))->toBeGreaterThanOrEqual(8);
    expect($manySupplies->temps_total_passe)->toBeGreaterThanOrEqual(4.0);
    
    // État emergency
    $emergency = Attachement::factory()->emergency()->create();
    expect($emergency->designation_travaux)->toStartWith('URGENCE');
    expect($emergency->temps_total_passe)->toBeLessThanOrEqual(2.0);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture complète client factory', function () {
    // État par défaut
    $defaultClient = Client::factory()->create();
    expect($defaultClient)->toBeInstanceOf(Client::class);
    expect($defaultClient->nom)->not->toBeNull();
    expect($defaultClient->email)->not->toBeNull();
    
    // État company
    $company = Client::factory()->company()->create();
    expect($company->nom)->toMatch('/SARL|SAS|EURL|Entreprise/');
    expect($company->notes)->toContain('Client professionnel');
    
    // État individual
    $individual = Client::factory()->individual()->create();
    expect($individual->notes)->not->toContain('Client professionnel');
    
    // État withHistory
    $withHistory = Client::factory()->withHistory()->create();
    expect($withHistory->notes)->toContain('Client fidèle depuis');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture complète seeder avec toutes les méthodes', function () {
    // Test de la méthode run normale
    $seeder = new AttachementSeeder();
    $seeder->run();
    
    // Vérifier que tous les types d'attachements ont été créés
    $total = Attachement::count();
    expect($total)->toBeGreaterThanOrEqual(50);
    
    // Vérifier la présence des différents types
    $recent = Attachement::where('created_at', '>=', now()->subDays(7))->count();
    expect($recent)->toBeGreaterThan(0);
    
    $emergency = Attachement::where('designation_travaux', 'like', 'URGENCE%')->count();
    expect($emergency)->toBeGreaterThan(0);
    
    $withoutSignature = Attachement::whereNull('nom_signataire_client')->count();
    expect($withoutSignature)->toBeGreaterThan(0);
    
    // Test de la méthode performance
    $performanceSeeder = new AttachementSeeder();
    $performanceSeeder->performance(100);
    
    $newTotal = Attachement::count();
    expect($newTotal)->toBe($total + 100);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture complète modèle attachement', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    
    $attachement = Attachement::factory()->create([
        'client_id' => $client->id,
        'created_by' => $user->id,
        'geolocation' => [
            'latitude' => 50.6292,
            'longitude' => 3.0573,
            'timestamp' => now()->toISOString()
        ]
    ]);
    
    // Test des relations
    expect($attachement->client)->toBeInstanceOf(Client::class);
    expect($attachement->creator)->toBeInstanceOf(User::class);
    expect($attachement->client->id)->toBe($client->id);
    expect($attachement->creator->id)->toBe($user->id);
    
    // Test des accessors
    expect($attachement->pdf_url)->toStartWith('http');
    expect($attachement->signature_entreprise_url)->toStartWith('http');
    expect($attachement->signature_client_url)->toStartWith('http');
    expect($attachement->formatted_geolocation)->toContain('50.629200, 3.057300');
    
    // Test des scopes
    $searchResults = Attachement::search($attachement->numero_dossier)->get();
    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->id)->toBe($attachement->id);
    
    $dateResults = Attachement::dateBetween(
        now()->subDay()->format('Y-m-d'),
        now()->addDay()->format('Y-m-d')
    )->get();
    expect($dateResults->pluck('id'))->toContain($attachement->id);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture complète observateur attachement', function () {
    // Capturer les logs
    Log::shouldReceive('info')->atLeast()->once();
    Log::shouldReceive('warning')->atLeast()->once();
    
    $user = User::factory()->create();
    
    // Test created event
    $attachement = Attachement::factory()->create(['created_by' => $user->id]);
    
    // Test updated event
    $attachement->update(['client_nom' => 'Nouveau nom']);
    
    // Test saving event avec signatures
    $attachement->signature_entreprise_path = 'new/signature/path.png';
    $attachement->save();
    
    $attachement->signature_client_path = 'new/client/signature.png';
    $attachement->nom_signataire_client = 'Jean Dupont';
    $attachement->save();
    
    $attachement->pdf_path = 'new/pdf/path.pdf';
    $attachement->save();
    
    // Test deleted event
    $attachement->delete();
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture edge cases et validations', function () {
    $user = User::factory()->create();
    
    // Test avec geolocation null
    $withoutGeo = Attachement::factory()->create(['geolocation' => null]);
    expect($withoutGeo->formatted_geolocation)->toBeNull();
    
    // Test avec geolocation incomplète
    $incompleteGeo = Attachement::factory()->create([
        'geolocation' => ['latitude' => 50.0]
    ]);
    expect($incompleteGeo->formatted_geolocation)->toBeNull();
    
    // Test avec fournitures_travaux vide
    $emptySupplies = Attachement::factory()->create([
        'fournitures_travaux' => []
    ]);
    expect($emptySupplies->fournitures_travaux)->toBeArray();
    expect($emptySupplies->fournitures_travaux)->toBeEmpty();
    
    // Test avec temps_total_passe à 0
    $zeroTime = Attachement::factory()->create([
        'temps_total_passe' => 0
    ]);
    expect($zeroTime->temps_total_passe)->toBe(0.0);
    
    // Test unicité numero_dossier
    $attachement1 = Attachement::factory()->create();
    $numero = $attachement1->numero_dossier;
    
    $duplicates = Attachement::where('numero_dossier', $numero)
                            ->where('id', '!=', $attachement1->id)
                            ->count();
    expect($duplicates)->toBe(0);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture performance et limites', function () {
    $user = User::factory()->create();
    
    // Test génération en masse
    $start = microtime(true);
    $attachements = Attachement::factory(50)->create();
    $duration = microtime(true) - $start;
    
    expect($attachements)->toHaveCount(50);
    expect($duration)->toBeLessThan(30); // Max 30 secondes pour 50 attachements
    
    // Test avec client existant (optimisation)
    $client = Client::factory()->create();
    $attachementsWithExistingClient = collect();
    
    for ($i = 0; $i < 10; $i++) {
        $attachement = Attachement::factory()->create([
            'client_id' => $client->id,
            'client_nom' => $client->nom,
            'client_email' => $client->email,
        ]);
        $attachementsWithExistingClient->push($attachement);
    }
    
    $uniqueClientIds = $attachementsWithExistingClient->pluck('client_id')->unique();
    expect($uniqueClientIds)->toHaveCount(1);
    expect($uniqueClientIds->first())->toBe($client->id);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('couverture formats et structures de données', function () {
    $user = User::factory()->create();
    $attachement = Attachement::factory()->create();
    
    // Vérifications format numero_dossier
    expect($attachement->numero_dossier)->toMatch('/^ATT-\d{8}-[A-Z]{4}$/');
    
    // Vérifications structure fournitures_travaux
    expect($attachement->fournitures_travaux)->toBeArray();
    expect($attachement->fournitures_travaux)->not->toBeEmpty();
    
    foreach ($attachement->fournitures_travaux as $fourniture) {
        expect($fourniture)->toHaveKey('designation');
        expect($fourniture)->toHaveKey('quantite');
        expect($fourniture)->toHaveKey('observations');
        
        expect($fourniture['designation'])->toBeString();
        expect($fourniture['quantite'])->toBeString();
    }
    
    // Vérifications chemins de fichiers
    expect($attachement->signature_entreprise_path)->toStartWith('signatures/');
    expect($attachement->signature_client_path)->toStartWith('signatures/');
    expect($attachement->pdf_path)->toStartWith('pdf/');
    
    // Vérifications dates
    expect($attachement->date_intervention)->toMatch('/^\d{4}-\d{2}-\d{2}$/');
    expect($attachement->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($attachement->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);