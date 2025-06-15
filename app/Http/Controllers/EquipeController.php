<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $specialisation = $request->get('specialisation');
        $active = $request->get('active');
        
        $equipes = Equipe::query()
            ->with(['chargeProjet', 'employesActifs', 'chefEquipe'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'ILIKE', "%{$search}%")
                      ->orWhere('code', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            })
            ->when($specialisation, function ($query, $specialisation) {
                $query->parSpecialisation($specialisation);
            })
            ->when($active !== null, function ($query) use ($active) {
                $query->where('active', $active === 'true');
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Equipes/Index', [
            'equipes' => $equipes,
            'filters' => [
                'search' => $search,
                'specialisation' => $specialisation,
                'active' => $active,
            ],
            'specialisations' => $this->getSpecialisations(),
            'statistiques' => [
                'total' => Equipe::count(),
                'actives' => Equipe::actives()->count(),
                'completes' => Equipe::actives()->get()->filter->est_complete->count(),
            ]
        ]);
    }

    public function create(): Response
    {
        $chargesProjets = Employe::chargesProjets()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);

        return Inertia::render('Equipes/Create', [
            'chargesProjets' => $chargesProjets,
            'specialisations' => $this->getSpecialisations(),
            'competences' => $this->getCompetences(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:equipes,code',
            'description' => 'nullable|string',
            'charge_projet_id' => 'required|exists:employes,id',
            'specialisation' => 'required|in:installation_generale,maintenance,depannage_urgence,industriel,tertiaire,particulier,eclairage_public',
            'capacite_max' => 'required|integer|min:1|max:20',
            'competences_requises' => 'nullable|array',
            'vehicules_attribues' => 'nullable|array',
            'zones_intervention' => 'nullable|array',
            'horaire_debut' => 'required|date_format:H:i',
            'horaire_fin' => 'required|date_format:H:i|after:horaire_debut',
            'active' => 'boolean',
        ]);

        $equipe = Equipe::create($validated);

        return redirect()->route('equipes.index')
            ->with('success', 'Équipe créée avec succès.');
    }

    public function show(Equipe $equipe): Response
    {
        $equipe->load([
            'chargeProjet',
            'employesActifs',
            'chefEquipe',
            'plannings' => function ($query) {
                $query->with(['employe', 'demande'])
                      ->orderBy('date_debut', 'desc')
                      ->limit(10);
            }
        ]);

        return Inertia::render('Equipes/Show', [
            'equipe' => $equipe,
            'employesDisponibles' => Employe::disponibles()
                ->whereDoesntHave('equipeActive')
                ->orderBy('nom')
                ->get(['id', 'nom', 'prenom', 'habilitations_electriques']),
            'statistiques' => [
                'interventions_total' => $equipe->plannings()->interventions()->count(),
                'interventions_terminees' => $equipe->plannings()->termines()->count(),
                'taux_reussite' => $this->calculerTauxReussite($equipe),
                'prochaine_intervention' => $equipe->getProchainePlanification(),
            ]
        ]);
    }

    public function edit(Equipe $equipe): Response
    {
        $chargesProjets = Employe::chargesProjets()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);

        return Inertia::render('Equipes/Edit', [
            'equipe' => $equipe,
            'chargesProjets' => $chargesProjets,
            'specialisations' => $this->getSpecialisations(),
            'competences' => $this->getCompetences(),
        ]);
    }

    public function update(Request $request, Equipe $equipe)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:equipes,code,' . $equipe->id,
            'description' => 'nullable|string',
            'charge_projet_id' => 'required|exists:employes,id',
            'specialisation' => 'required|in:installation_generale,maintenance,depannage_urgence,industriel,tertiaire,particulier,eclairage_public',
            'capacite_max' => 'required|integer|min:1|max:20',
            'competences_requises' => 'nullable|array',
            'vehicules_attribues' => 'nullable|array',
            'zones_intervention' => 'nullable|array',
            'horaire_debut' => 'required|date_format:H:i',
            'horaire_fin' => 'required|date_format:H:i|after:horaire_debut',
            'active' => 'boolean',
        ]);

        $equipe->update($validated);

        return redirect()->route('equipes.index')
            ->with('success', 'Équipe modifiée avec succès.');
    }

    public function destroy(Equipe $equipe)
    {
        if ($equipe->plannings()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une équipe qui a des plannings associés.');
        }

        if ($equipe->employesActifs()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une équipe qui a des employés actifs.');
        }

        $equipe->delete();

        return redirect()->route('equipes.index')
            ->with('success', 'Équipe supprimée avec succès.');
    }

    public function ajouterEmploye(Request $request, Equipe $equipe)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'role_equipe' => 'required|in:chef_equipe,membre,apprenti',
        ]);

        $employe = Employe::findOrFail($validated['employe_id']);

        if (!$equipe->peutAccueillirEmploye()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'équipe a atteint sa capacité maximale.'
            ], 422);
        }

        $success = $equipe->ajouterEmploye($employe, $validated['role_equipe']);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'ajouter l\'employé à l\'équipe.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Employé ajouté à l\'équipe avec succès.'
        ]);
    }

    public function retirerEmploye(Request $request, Equipe $equipe, Employe $employe)
    {
        $success = $equipe->retirerEmploye($employe);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de retirer l\'employé de l\'équipe.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Employé retiré de l\'équipe avec succès.'
        ]);
    }

    public function apiList(Request $request)
    {
        $specialisation = $request->get('specialisation');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        
        $equipes = Equipe::query()
            ->actives()
            ->when($specialisation, function ($query, $specialisation) {
                $query->parSpecialisation($specialisation);
            })
            ->when($dateDebut && $dateFin, function ($query) use ($dateDebut, $dateFin) {
                $query->disponibles($dateDebut, $dateFin);
            })
            ->with('employesActifs')
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'specialisation', 'capacite_max']);

        return response()->json($equipes->map(function ($equipe) {
            return [
                'id' => $equipe->id,
                'nom' => $equipe->nom,
                'code' => $equipe->code,
                'specialisation' => $equipe->specialisation,
                'effectif_actuel' => $equipe->effectif_actuel,
                'capacite_max' => $equipe->capacite_max,
                'taux_occupation' => $equipe->taux_occupation,
                'competences_disponibles' => $equipe->getCompetencesDisponibles(),
            ];
        }));
    }

    private function getSpecialisations(): array
    {
        return [
            'installation_generale' => 'Installation générale',
            'maintenance' => 'Maintenance',
            'depannage_urgence' => 'Dépannage d\'urgence',
            'industriel' => 'Industriel',
            'tertiaire' => 'Tertiaire',
            'particulier' => 'Particulier',
            'eclairage_public' => 'Éclairage public',
        ];
    }

    private function getCompetences(): array
    {
        return [
            'Installation électrique',
            'Maintenance préventive',
            'Dépannage',
            'Mise en conformité',
            'Tableau électrique',
            'Éclairage',
            'Domotique',
            'Câblage industriel',
            'Automatisme',
            'Vidéosurveillance',
            'Alarme',
            'Interphonie',
            'Photovoltaïque',
            'Borne de recharge',
        ];
    }

    private function calculerTauxReussite(Equipe $equipe): float
    {
        $total = $equipe->plannings()->termines()->count();
        
        if ($total === 0) {
            return 0;
        }

        $reussis = $equipe->plannings()
            ->termines()
            ->where('objectifs_atteints', true)
            ->count();

        return round(($reussis / $total) * 100, 1);
    }
}