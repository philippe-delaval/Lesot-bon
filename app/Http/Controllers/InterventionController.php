<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterventionRequest;
use App\Http\Requests\UpdateInterventionRequest;
use App\Models\Intervention;
use App\Models\Technicien;
use App\Models\Client;
use App\Models\Demande;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class InterventionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Intervention::with(['technicien.user', 'client', 'demande'])
            ->latest('date_planifiee');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('technicien_id')) {
            $query->where('technicien_id', $request->technicien_id);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        if ($request->filled('type')) {
            $query->where('type_intervention', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_intervention', 'like', "%{$request->search}%")
                  ->orWhere('description_technique', 'like', "%{$request->search}%")
                  ->orWhere('adresse_intervention', 'like', "%{$request->search}%");
            });
        }

        $interventions = $query->paginate(15);

        $techniciens = Technicien::with('user')
            ->actif()
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'nom' => $t->user->name,
                'matricule' => $t->matricule,
                'competences' => $t->competences
            ]);

        return Inertia::render('Interventions/Index', [
            'interventions' => $interventions,
            'techniciens' => $techniciens,
            'filters' => $request->only(['statut', 'technicien_id', 'priorite', 'type', 'search']),
            'statutsDisponibles' => ['planifiee', 'en_route', 'sur_site', 'en_cours', 'terminee', 'annulee'],
            'prioritesDisponibles' => ['basse', 'normale', 'haute', 'urgente'],
            'typesDisponibles' => ['preventive', 'corrective', 'urgente', 'installation']
        ]);
    }

    public function create(): Response
    {
        $techniciens = Technicien::with('user')
            ->disponible()
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'nom' => $t->user->name,
                'matricule' => $t->matricule,
                'competences' => $t->competences,
                'latitude' => $t->latitude,
                'longitude' => $t->longitude,
                'charge_travail' => $t->calculerChargeTravailde()
            ]);

        $clients = Client::actif()
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'nom' => $c->nom,
                'adresse' => $c->adresse
            ]);

        $demandes = Demande::whereDoesntHave('intervention')
            ->with('client')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'titre' => $d->titre,
                'description' => $d->description,
                'client' => $d->client->nom
            ]);

        $equipements = Equipement::disponible()
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'nom' => $e->nom,
                'reference' => $e->reference,
                'type' => $e->type,
                'stock_disponible' => $e->stock_disponible
            ]);

        return Inertia::render('Interventions/Create', [
            'techniciens' => $techniciens,
            'clients' => $clients,
            'demandes' => $demandes,
            'equipements' => $equipements,
            'typesIntervention' => ['preventive', 'corrective', 'urgente', 'installation'],
            'priorites' => ['basse', 'normale', 'haute', 'urgente'],
            'competencesDisponibles' => ['plomberie', 'electricite', 'climatisation', 'chauffage', 'general']
        ]);
    }

    public function store(StoreInterventionRequest $request): RedirectResponse
    {
        $intervention = Intervention::create($request->validated());

        $intervention->ajouterLog(
            'planification',
            null,
            'planifiee',
            'Intervention planifiée'
        );

        return redirect()
            ->route('interventions.show', $intervention)
            ->with('success', 'Intervention créée avec succès.');
    }

    public function show(Intervention $intervention): Response
    {
        $intervention->load([
            'technicien.user',
            'client',
            'demande',
            'logs' => function ($query) {
                $query->with('technicien.user')->latest('timestamp_action');
            }
        ]);

        $kpis = $this->calculerKPIsIntervention($intervention);

        return Inertia::render('Interventions/Show', [
            'intervention' => $intervention,
            'kpis' => $kpis,
            'actions_disponibles' => $this->obtenirActionsDisponibles($intervention),
            'logs_formated' => $intervention->logs->map(fn($log) => $log->formaterPourRapport())
        ]);
    }

    public function edit(Intervention $intervention): Response
    {
        $techniciens = Technicien::with('user')
            ->where(function ($query) use ($intervention) {
                $query->disponible()
                      ->orWhere('id', $intervention->technicien_id);
            })
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'nom' => $t->user->name,
                'matricule' => $t->matricule,
                'competences' => $t->competences,
                'disponible' => $t->statut === 'disponible'
            ]);

        $equipements = Equipement::disponible()
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'nom' => $e->nom,
                'reference' => $e->reference,
                'stock_disponible' => $e->stock_disponible
            ]);

        return Inertia::render('Interventions/Edit', [
            'intervention' => $intervention,
            'techniciens' => $techniciens,
            'equipements' => $equipements,
            'typesIntervention' => ['preventive', 'corrective', 'urgente', 'installation'],
            'priorites' => ['basse', 'normale', 'haute', 'urgente'],
            'competencesDisponibles' => ['plomberie', 'electricite', 'climatisation', 'chauffage', 'general']
        ]);
    }

    public function update(UpdateInterventionRequest $request, Intervention $intervention): RedirectResponse
    {
        $ancienStatut = $intervention->statut;
        $intervention->update($request->validated());

        if ($ancienStatut !== $intervention->statut) {
            $intervention->ajouterLog(
                'modification',
                $ancienStatut,
                $intervention->statut,
                'Modification de l\'intervention'
            );
        }

        return redirect()
            ->route('interventions.show', $intervention)
            ->with('success', 'Intervention mise à jour avec succès.');
    }

    public function destroy(Intervention $intervention): RedirectResponse
    {
        if (in_array($intervention->statut, ['en_cours', 'terminee'])) {
            return back()->with('error', 'Impossible de supprimer une intervention en cours ou terminée.');
        }

        $intervention->delete();

        return redirect()
            ->route('interventions.index')
            ->with('success', 'Intervention supprimée avec succès.');
    }

    public function demarrer(Intervention $intervention): RedirectResponse
    {
        if ($intervention->statut !== 'sur_site') {
            return back()->with('error', 'L\'intervention doit être sur site pour être démarrée.');
        }

        $intervention->demarrerIntervention();

        return back()->with('success', 'Intervention démarrée.');
    }

    public function terminer(Request $request, Intervention $intervention): RedirectResponse
    {
        $request->validate([
            'diagnostic' => 'required|string',
            'succes' => 'required|boolean',
            'cout_reel' => 'nullable|numeric|min:0',
            'pieces_utilisees' => 'nullable|array',
            'recommandations' => 'nullable|string'
        ]);

        $intervention->update([
            'diagnostic' => $request->diagnostic,
            'cout_reel' => $request->cout_reel,
            'pieces_utilisees' => $request->pieces_utilisees,
            'recommandations' => $request->recommandations
        ]);

        $intervention->terminerIntervention($request->succes, $request->diagnostic);

        return back()->with('success', 'Intervention terminée avec succès.');
    }

    public function changerStatut(Request $request, Intervention $intervention): RedirectResponse
    {
        $request->validate([
            'statut' => 'required|in:planifiee,en_route,sur_site,en_cours,terminee,annulee',
            'commentaire' => 'nullable|string'
        ]);

        $ancienStatut = $intervention->statut;
        $intervention->update(['statut' => $request->statut]);

        $intervention->ajouterLog(
            'changement_statut',
            $ancienStatut,
            $request->statut,
            $request->commentaire
        );

        return back()->with('success', 'Statut mis à jour.');
    }

    public function assignerTechnicien(Request $request, Intervention $intervention): RedirectResponse
    {
        $request->validate([
            'technicien_id' => 'required|exists:techniciens,id'
        ]);

        $technicien = Technicien::findOrFail($request->technicien_id);

        if (!$intervention->peutEtreAssigneeA($technicien)) {
            return back()->with('error', 'Ce technicien ne peut pas être assigné à cette intervention.');
        }

        $ancienTechnicienId = $intervention->technicien_id;
        $intervention->update(['technicien_id' => $request->technicien_id]);

        $intervention->ajouterLog(
            'assignation',
            null,
            null,
            "Intervention assignée au technicien {$technicien->user->name}"
        );

        return back()->with('success', 'Technicien assigné avec succès.');
    }

    private function calculerKPIsIntervention(Intervention $intervention): array
    {
        return [
            'duree_planifiee' => $intervention->duree_estimee_minutes,
            'duree_reelle' => $intervention->duree_relle_minutes,
            'respect_planning' => $intervention->kpis['respect_planning'] ?? null,
            'respect_budget' => $intervention->kpis['respect_budget'] ?? null,
            'satisfaction_client' => $intervention->note_client,
            'first_time_fix' => $intervention->first_time_fix,
            'nombre_logs' => $intervention->logs->count(),
            'distance_technicien' => $intervention->calculerDistanceDepuisTechnicien(),
            'en_retard' => $intervention->estEnRetard()
        ];
    }

    private function obtenirActionsDisponibles(Intervention $intervention): array
    {
        $actions = [];

        switch ($intervention->statut) {
            case 'planifiee':
                $actions = ['demarrer_trajet', 'modifier', 'annuler'];
                break;
            case 'en_route':
                $actions = ['arriver_sur_site'];
                break;
            case 'sur_site':
                $actions = ['demarrer_intervention'];
                break;
            case 'en_cours':
                $actions = ['terminer_intervention', 'ajouter_pause'];
                break;
            case 'terminee':
                $actions = ['generer_rapport', 'facturer'];
                break;
        }

        return $actions;
    }
}
