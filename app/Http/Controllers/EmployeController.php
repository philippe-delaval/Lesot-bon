<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $statut = $request->get('statut');
        $habilitation = $request->get('habilitation');
        $disponibilite = $request->get('disponibilite');
        
        $employes = Employe::query()
            ->with(['chargeProjet', 'gestionnaire', 'equipeActive'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'ILIKE', "%{$search}%")
                      ->orWhere('prenom', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('matricule', 'ILIKE', "%{$search}%");
                });
            })
            ->when($statut, function ($query, $statut) {
                $query->where('statut', $statut);
            })
            ->when($habilitation, function ($query, $habilitation) {
                $query->avecHabilitation($habilitation);
            })
            ->when($disponibilite, function ($query, $disponibilite) {
                $query->where('disponibilite', $disponibilite);
            })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Employes/Index', [
            'employes' => $employes,
            'filters' => [
                'search' => $search,
                'statut' => $statut,
                'habilitation' => $habilitation,
                'disponibilite' => $disponibilite,
            ],
            'statistiques' => [
                'total' => Employe::count(),
                'permanents' => Employe::permanents()->count(),
                'interimaires' => Employe::interimaires()->count(),
                'disponibles' => Employe::disponibles()->count(),
            ]
        ]);
    }

    public function create(): Response
    {
        $gestionnaires = Employe::gestionnaires()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);
            
        $chargesProjets = Employe::chargesProjets()
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);

        return Inertia::render('Employes/Create', [
            'gestionnaires' => $gestionnaires,
            'chargesProjets' => $chargesProjets,
            'habilitations' => $this->getHabilitationsElectriques(),
            'certifications' => $this->getCertifications(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:employes,email',
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'required|string|max:50|unique:employes,matricule',
            'statut' => 'required|in:permanent,interimaire',
            'type_contrat' => 'required|in:cdi,cdd,interim,apprentissage,stage',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'salaire_base' => 'nullable|numeric|min:0',
            'role_hierarchique' => 'required|in:gestionnaire,charge_projet,employe',
            'charge_projet_id' => 'nullable|exists:employes,id',
            'gestionnaire_id' => 'nullable|exists:employes,id',
            'habilitations_electriques' => 'nullable|array',
            'habilitations_electriques.*' => 'string|in:B0,B1V,B2V,BR,BC,H0,H1V,H2V,HR,HC',
            'certifications' => 'nullable|array',
            'competences' => 'nullable|array',
            'disponibilite' => 'required|in:disponible,indisponible,conge,arret_maladie,formation',
            'vehicule_attribue' => 'nullable|string|max:255',
            'astreinte' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $employe = Employe::create($validated);

        return redirect()->route('employes.index')
            ->with('success', 'Employé créé avec succès.');
    }

    public function show(Employe $employe): Response
    {
        $employe->load([
            'chargeProjet',
            'gestionnaire',
            'employesSupervises',
            'employesGeres',
            'equipes.equipe',
            'plannings' => function ($query) {
                $query->with(['demande', 'equipe'])
                      ->orderBy('date_debut', 'desc')
                      ->limit(10);
            }
        ]);

        return Inertia::render('Employes/Show', [
            'employe' => $employe,
            'statistiques' => [
                'interventions_total' => $employe->plannings()->interventions()->count(),
                'interventions_terminees' => $employe->plannings()->termines()->count(),
                'heures_travaillees' => $this->calculerHeuresTravaillees($employe),
                'note_moyenne' => $this->calculerNoteMoyenne($employe),
            ]
        ]);
    }

    public function edit(Employe $employe): Response
    {
        $gestionnaires = Employe::gestionnaires()
            ->where('id', '!=', $employe->id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);
            
        $chargesProjets = Employe::chargesProjets()
            ->where('id', '!=', $employe->id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom']);

        return Inertia::render('Employes/Edit', [
            'employe' => $employe,
            'gestionnaires' => $gestionnaires,
            'chargesProjets' => $chargesProjets,
            'habilitations' => $this->getHabilitationsElectriques(),
            'certifications' => $this->getCertifications(),
        ]);
    }

    public function update(Request $request, Employe $employe)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:employes,email,' . $employe->id,
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'required|string|max:50|unique:employes,matricule,' . $employe->id,
            'statut' => 'required|in:permanent,interimaire',
            'type_contrat' => 'required|in:cdi,cdd,interim,apprentissage,stage',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'salaire_base' => 'nullable|numeric|min:0',
            'role_hierarchique' => 'required|in:gestionnaire,charge_projet,employe',
            'charge_projet_id' => 'nullable|exists:employes,id',
            'gestionnaire_id' => 'nullable|exists:employes,id',
            'habilitations_electriques' => 'nullable|array',
            'habilitations_electriques.*' => 'string|in:B0,B1V,B2V,BR,BC,H0,H1V,H2V,HR,HC',
            'certifications' => 'nullable|array',
            'competences' => 'nullable|array',
            'disponibilite' => 'required|in:disponible,indisponible,conge,arret_maladie,formation',
            'vehicule_attribue' => 'nullable|string|max:255',
            'astreinte' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $employe->update($validated);

        return redirect()->route('employes.index')
            ->with('success', 'Employé modifié avec succès.');
    }

    public function destroy(Employe $employe)
    {
        if ($employe->plannings()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un employé qui a des plannings associés.');
        }

        if ($employe->employesSupervises()->exists() || $employe->employesGeres()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un employé qui supervise d\'autres employés.');
        }

        $employe->delete();

        return redirect()->route('employes.index')
            ->with('success', 'Employé supprimé avec succès.');
    }

    public function apiList(Request $request)
    {
        $employes = Employe::query()
            ->disponibles()
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'email', 'matricule', 'habilitations_electriques']);

        return response()->json($employes->map(function ($employe) {
            return [
                'id' => $employe->id,
                'nom_complet' => $employe->nom_complet,
                'email' => $employe->email,
                'matricule' => $employe->matricule,
                'habilitations' => $employe->formatHabilitations(),
            ];
        }));
    }

    public function apiSearch(Request $request)
    {
        $search = $request->get('q');
        $habilitation = $request->get('habilitation');
        
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }
        
        $employes = Employe::query()
            ->disponibles()
            ->where(function ($query) use ($search) {
                $query->where('nom', 'ILIKE', "%{$search}%")
                      ->orWhere('prenom', 'ILIKE', "%{$search}%")
                      ->orWhere('matricule', 'ILIKE', "%{$search}%");
            })
            ->when($habilitation, function ($query, $habilitation) {
                $query->avecHabilitation($habilitation);
            })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->limit(10)
            ->get(['id', 'nom', 'prenom', 'email', 'matricule', 'habilitations_electriques']);

        return response()->json($employes->map(function ($employe) {
            return [
                'id' => $employe->id,
                'nom_complet' => $employe->nom_complet,
                'email' => $employe->email,
                'matricule' => $employe->matricule,
                'habilitations' => $employe->formatHabilitations(),
            ];
        }));
    }

    private function getHabilitationsElectriques(): array
    {
        return [
            'B0' => 'Habilitation basse tension - Exécutant non électricien',
            'B1V' => 'Habilitation basse tension - Exécutant électricien au voisinage',
            'B2V' => 'Habilitation basse tension - Chargé de travaux au voisinage',
            'BR' => 'Habilitation basse tension - Chargé d\'intervention de dépannage',
            'BC' => 'Habilitation basse tension - Chargé de consignation',
            'H0' => 'Habilitation haute tension - Exécutant non électricien',
            'H1V' => 'Habilitation haute tension - Exécutant électricien au voisinage',
            'H2V' => 'Habilitation haute tension - Chargé de travaux au voisinage',
            'HR' => 'Habilitation haute tension - Chargé d\'intervention de dépannage',
            'HC' => 'Habilitation haute tension - Chargé de consignation',
        ];
    }

    private function getCertifications(): array
    {
        return [
            'CACES' => 'Certificat d\'Aptitude à la Conduite En Sécurité',
            'SST' => 'Sauveteur Secouriste du Travail',
            'ATEX' => 'Atmosphères Explosives',
            'Travail en hauteur' => 'Port du harnais et travail en hauteur',
            'Formation échafaudage' => 'Montage et utilisation d\'échafaudages',
            'Conduite PL' => 'Permis poids lourd',
        ];
    }

    private function calculerHeuresTravaillees(Employe $employe): float
    {
        return $employe->plannings()
            ->termines()
            ->whereNotNull('duree_reelle_minutes')
            ->sum('duree_reelle_minutes') / 60;
    }

    private function calculerNoteMoyenne(Employe $employe): ?float
    {
        $moyenne = $employe->plannings()
            ->termines()
            ->whereNotNull('note_client')
            ->avg('note_client');

        return $moyenne ? round($moyenne, 1) : null;
    }
}