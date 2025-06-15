<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Employe;
use App\Models\Equipe;
use App\Models\Demande;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PlanningController extends Controller
{
    public function index(Request $request): Response
    {
        $vue = $request->get('vue', 'calendrier'); // calendrier, liste, equipe
        $dateDebut = $request->get('date_debut', now()->startOfWeek()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->endOfWeek()->format('Y-m-d'));
        $employe = $request->get('employe');
        $equipe = $request->get('equipe');
        $statut = $request->get('statut');
        $type = $request->get('type');

        $planningsQuery = Planning::query()
            ->with(['employe', 'demande.client', 'equipe', 'creePar'])
            ->whereBetween('date_debut', [$dateDebut, $dateFin])
            ->when($employe, function ($query, $employe) {
                $query->parEmploye($employe);
            })
            ->when($equipe, function ($query, $equipe) {
                $query->parEquipe($equipe);
            })
            ->when($statut, function ($query, $statut) {
                $query->where('statut', $statut);
            })
            ->when($type, function ($query, $type) {
                $query->parType($type);
            });

        // Toujours utiliser paginate pour une structure cohérente
        $plannings = $planningsQuery->orderBy('date_debut', 'desc')->paginate(20);

        return Inertia::render('Planning/Index', [
            'plannings' => $plannings,
            'employes' => Employe::orderBy('nom')->get(['id', 'nom', 'prenom']),
            'equipes' => Equipe::actives()->orderBy('nom')->get(['id', 'nom', 'code']),
            'filters' => [
                'vue' => $vue,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'employe' => $employe,
                'equipe' => $equipe,
                'statut' => $statut,
                'type' => $type,
            ],
            'statistiques' => [
                'total_planifie' => Planning::planifies()->count(),
                'en_cours' => Planning::enCours()->count(),
                'termines_aujourdhui' => Planning::termines()
                    ->whereDate('date_fin', today())
                    ->count(),
                'en_retard' => Planning::where('date_fin', '<', now())
                    ->where('statut', '!=', 'termine')
                    ->count(),
            ]
        ]);
    }

    public function create(Request $request): Response
    {
        $demandeId = $request->get('demande_id');
        $employeId = $request->get('employe_id');
        $dateDebut = $request->get('date_debut');

        $employes = Employe::disponibles()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'habilitations_electriques']);

        $equipes = Equipe::actives()
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'specialisation']);

        $demandes = Demande::where('statut', 'validee')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'titre', 'client_id', 'urgence']);

        return Inertia::render('Planning/Create', [
            'employes' => $employes,
            'equipes' => $equipes,
            'demandes' => $demandes,
            'defaults' => [
                'demande_id' => $demandeId,
                'employe_id' => $employeId,
                'date_debut' => $dateDebut,
            ],
            'typesAffectation' => $this->getTypesAffectation(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'demande_id' => 'nullable|exists:demandes,id',
            'equipe_id' => 'nullable|exists:equipes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'heure_debut_prevue' => 'nullable|date_format:H:i',
            'heure_fin_prevue' => 'nullable|date_format:H:i|after:heure_debut_prevue',
            'type_affectation' => 'required|in:intervention,maintenance,formation,conge,arret_maladie,deplacement,administratif,astreinte',
            'lieu_intervention' => 'nullable|string|max:255',
            'coordonnees_gps' => 'nullable|array',
            'coordonnees_gps.lat' => 'nullable|numeric|between:-90,90',
            'coordonnees_gps.lng' => 'nullable|numeric|between:-180,180',
            'description_tache' => 'nullable|string',
            'materiels_requis' => 'nullable|array',
            'duree_estimee_minutes' => 'nullable|integer|min:15|max:1440',
            'vehicule_utilise' => 'nullable|string|max:255',
            'commentaires' => 'nullable|string',
        ]);

        // Vérifier les conflits
        $conflits = Planning::where('employe_id', $validated['employe_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                      ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                      ->orWhere(function ($subQuery) use ($validated) {
                          $subQuery->where('date_debut', '<=', $validated['date_debut'])
                                   ->where('date_fin', '>=', $validated['date_fin']);
                      });
            })
            ->where('statut', '!=', 'annule')
            ->exists();

        if ($conflits) {
            return redirect()->back()
                ->withErrors(['employe_id' => 'L\'employé a déjà une affectation sur cette période.'])
                ->withInput();
        }

        $validated['cree_par_id'] = auth()->user()->employe->id ?? 1; // TODO: gérer l'auth
        $validated['statut'] = 'planifie';

        $planning = Planning::create($validated);

        return redirect()->route('planning.index')
            ->with('success', 'Planning créé avec succès.');
    }

    public function show(Planning $planning): Response
    {
        $planning->load([
            'employe',
            'demande.client',
            'equipe.employesActifs',
            'creePar',
            'validePar'
        ]);

        return Inertia::render('Planning/Show', [
            'planning' => $planning,
            'rapport' => $planning->genererRapport(),
            'conflits' => $this->detecterConflits($planning),
        ]);
    }

    public function edit(Planning $planning): Response
    {
        if (!$planning->peutEtreModifie()) {
            return redirect()->back()
                ->with('error', 'Ce planning ne peut plus être modifié.');
        }

        $employes = Employe::disponibles()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'habilitations_electriques']);

        $equipes = Equipe::actives()
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'specialisation']);

        $demandes = Demande::where('statut', 'validee')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'titre', 'client_id', 'urgence']);

        return Inertia::render('Planning/Edit', [
            'planning' => $planning,
            'employes' => $employes,
            'equipes' => $equipes,
            'demandes' => $demandes,
            'typesAffectation' => $this->getTypesAffectation(),
        ]);
    }

    public function update(Request $request, Planning $planning)
    {
        if (!$planning->peutEtreModifie()) {
            return redirect()->back()
                ->with('error', 'Ce planning ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'demande_id' => 'nullable|exists:demandes,id',
            'equipe_id' => 'nullable|exists:equipes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'heure_debut_prevue' => 'nullable|date_format:H:i',
            'heure_fin_prevue' => 'nullable|date_format:H:i|after:heure_debut_prevue',
            'type_affectation' => 'required|in:intervention,maintenance,formation,conge,arret_maladie,deplacement,administratif,astreinte',
            'lieu_intervention' => 'nullable|string|max:255',
            'coordonnees_gps' => 'nullable|array',
            'description_tache' => 'nullable|string',
            'materiels_requis' => 'nullable|array',
            'duree_estimee_minutes' => 'nullable|integer|min:15|max:1440',
            'vehicule_utilise' => 'nullable|string|max:255',
            'commentaires' => 'nullable|string',
        ]);

        // Vérifier les conflits (exclure le planning actuel)
        $conflits = Planning::where('employe_id', $validated['employe_id'])
            ->where('id', '!=', $planning->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('date_debut', [$validated['date_debut'], $validated['date_fin']])
                      ->orWhereBetween('date_fin', [$validated['date_debut'], $validated['date_fin']])
                      ->orWhere(function ($subQuery) use ($validated) {
                          $subQuery->where('date_debut', '<=', $validated['date_debut'])
                                   ->where('date_fin', '>=', $validated['date_fin']);
                      });
            })
            ->where('statut', '!=', 'annule')
            ->exists();

        if ($conflits) {
            return redirect()->back()
                ->withErrors(['employe_id' => 'L\'employé a déjà une affectation sur cette période.'])
                ->withInput();
        }

        $planning->update($validated);

        return redirect()->route('planning.index')
            ->with('success', 'Planning modifié avec succès.');
    }

    public function destroy(Planning $planning)
    {
        if (!$planning->peutEtreAnnule()) {
            return redirect()->back()
                ->with('error', 'Ce planning ne peut plus être supprimé.');
        }

        $planning->update(['statut' => 'annule']);

        return redirect()->route('planning.index')
            ->with('success', 'Planning annulé avec succès.');
    }

    public function demarrer(Planning $planning)
    {
        if (!$planning->demarrer()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de démarrer cette intervention.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Intervention démarrée avec succès.'
        ]);
    }

    public function terminer(Request $request, Planning $planning)
    {
        $validated = $request->validate([
            'rapport_intervention' => 'nullable|string',
            'kilometres_parcourus' => 'nullable|numeric|min:0',
            'frais_deplacement' => 'nullable|numeric|min:0',
            'difficulte' => 'nullable|in:facile,moyenne,difficile,complexe',
            'note_client' => 'nullable|integer|between:1,5',
            'objectifs_atteints' => 'boolean',
        ]);

        if (!$planning->terminer($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de terminer cette intervention.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Intervention terminée avec succès.'
        ]);
    }

    public function valider(Request $request, Planning $planning)
    {
        $validated = $request->validate([
            'commentaires' => 'nullable|string',
        ]);

        $validateur = auth()->user()->employe; // TODO: gérer l'auth

        if (!$planning->valider($validateur, $validated['commentaires'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de valider cette intervention.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Intervention validée avec succès.'
        ]);
    }

    public function calendrierData(Request $request)
    {
        $dateDebut = $request->get('start');
        $dateFin = $request->get('end');
        $vue = $request->get('vue', 'tous'); // tous, employe, equipe

        $planningsQuery = Planning::query()
            ->with(['employe', 'demande.client', 'equipe'])
            ->whereBetween('date_debut', [$dateDebut, $dateFin])
            ->where('statut', '!=', 'annule');

        if ($vue === 'employe') {
            $employeId = $request->get('employe_id');
            $planningsQuery->where('employe_id', $employeId);
        } elseif ($vue === 'equipe') {
            $equipeId = $request->get('equipe_id');
            $planningsQuery->where('equipe_id', $equipeId);
        }

        $plannings = $planningsQuery->get();

        return response()->json($plannings->map(function ($planning) {
            return [
                'id' => $planning->id,
                'title' => $this->getEventTitle($planning),
                'start' => $planning->date_debut->format('Y-m-d\TH:i:s'),
                'end' => $planning->date_fin->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $planning->getTypeColor(),
                'borderColor' => $planning->getTypeColor(),
                'textColor' => '#fff',
                'extendedProps' => [
                    'statut' => $planning->statut,
                    'type' => $planning->type_affectation,
                    'employe' => $planning->employe->nom_complet,
                    'lieu' => $planning->lieu_intervention,
                    'description' => $planning->description_tache,
                ],
            ];
        }));
    }

    private function getTypesAffectation(): array
    {
        return [
            'intervention' => 'Intervention',
            'maintenance' => 'Maintenance',
            'formation' => 'Formation',
            'conge' => 'Congé',
            'arret_maladie' => 'Arrêt maladie',
            'deplacement' => 'Déplacement',
            'administratif' => 'Administratif',
            'astreinte' => 'Astreinte',
        ];
    }

    private function detecterConflits(Planning $planning): array
    {
        return Planning::where('employe_id', $planning->employe_id)
            ->where('id', '!=', $planning->id)
            ->where(function ($query) use ($planning) {
                $query->where(function ($q) use ($planning) {
                    $q->whereBetween('date_debut', [$planning->date_debut, $planning->date_fin])
                      ->orWhereBetween('date_fin', [$planning->date_debut, $planning->date_fin])
                      ->orWhere(function ($subQ) use ($planning) {
                          $subQ->where('date_debut', '<=', $planning->date_debut)
                               ->where('date_fin', '>=', $planning->date_fin);
                      });
                });
            })
            ->where('statut', '!=', 'annule')
            ->with(['employe', 'demande'])
            ->get()
            ->toArray();
    }

    private function getEventTitle(Planning $planning): string
    {
        $prefix = match ($planning->type_affectation) {
            'intervention' => '🔧',
            'maintenance' => '🛠️',
            'formation' => '📚',
            'conge' => '🏖️',
            'arret_maladie' => '🏥',
            'deplacement' => '🚗',
            'administratif' => '📋',
            'astreinte' => '📞',
            default => '📅'
        };

        if ($planning->demande) {
            return $prefix . ' ' . $planning->demande->titre;
        }

        return $prefix . ' ' . ucfirst($planning->type_affectation);
    }
}