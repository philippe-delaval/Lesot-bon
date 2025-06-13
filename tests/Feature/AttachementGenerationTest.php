<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Attachement;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\AttachementSeeder;
use Illuminate\Support\Carbon;

class AttachementGenerationTest extends TestCase
{
    /** @test */
    public function test_generation_basique_attachements(): void
    {
        // Créer un utilisateur de test
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
        ]);

        // Test avec 10 attachements
        $attachements = Attachement::factory(10)->create();
        
        $this->assertCount(10, $attachements);
        $this->assertEquals(10, Attachement::count());

        // Vérification des relations
        $firstAttachement = $attachements->first();
        $this->assertInstanceOf(Client::class, $firstAttachement->client);
        $this->assertInstanceOf(User::class, $firstAttachement->creator);
        
        // Vérification des champs obligatoires
        $this->assertNotNull($firstAttachement->numero_dossier);
        $this->assertNotNull($firstAttachement->client_nom);
        $this->assertNotNull($firstAttachement->client_email);
        $this->assertNotNull($firstAttachement->date_intervention);
        $this->assertNotNull($firstAttachement->designation_travaux);
        $this->assertNotNull($firstAttachement->fournitures_travaux);
        $this->assertNotNull($firstAttachement->temps_total_passe);
    }

}
