<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateDemandeRequest;
use App\Models\Demande;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class DemandeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Demande::class, 'demande');
    }

    public function index(Request $request): Response
    {
        $user = auth()->user();
        $query = Demande::with([
            'createur:id,name',
            'receveur:id,name',
            'client:id,nom'
        ])->orderBy('created_at', 'desc');

        if ($request->has('role') && $request->role === 'assignees') {
            $query->assigneesA($user->id);
        } elseif ($request->has('role') && $request->role === 'creees') {
            $query->creesPar($user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('createur_id', $user->id)
                  ->orWhere('receveur_id', $user->id);
            });
        }

        if ($request->has('statut') && $request->statut !== 'all') {
            $query->where('statut', $request->statut);
        }

        if ($request->has('priorite') && $request->priorite !== 'all') {
            $query->where('priorite', $request->priorite);
        }

        $demandes = $query->paginate(10);

        // Statistiques groupées en une seule requête
        $stats = Demande::selectRaw('
            SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN receveur_id = ? AND statut IN (\'assignee\', \'en_cours\') THEN 1 ELSE 0 END) as assignees,
            SUM(CASE WHEN createur_id = ? AND statut NOT IN (\'terminee\', \'annulee\') THEN 1 ELSE 0 END) as mes_creees
        ', [
            'en_attente',
            $user->id,
            $user->id
        ])->first();

        return Inertia::render('Demandes/Index', [
            'demandes' => $demandes,
            'filters' => $request->only(['role', 'statut', 'priorite']),
            'stats' => [
                'en_attente' => $stats->en_attente ?? 0,
                'assignees' => $stats->assignees ?? 0,
                'mes_creees' => $stats->mes_creees ?? 0,
            ]
        ]);
    }

    public function create(): Response
    {
        // Cache des utilisateurs et clients avec TTL de 5 minutes
        $users = cache()->remember('users_for_demande_form', 300, function () {
            return User::pourFormulaires()->get();
        });
        
        $clients = cache()->remember('clients_for_demande_form', 300, function () {
            return Client::pourFormulaires()->get();
        });

        return Inertia::render('Demandes/Create', [
            'users' => $users,
            'clients' => $clients,
        ]);
    }

    public function store(StoreDemandeRequest $request): RedirectResponse
    {
        $demande = Demande::create([
            ...$request->validated(),
            'createur_id' => auth()->id(),
            'date_demande' => now(),
        ]);

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande créée avec succès.');
    }

    public function show(Demande $demande): Response
    {
        $demande->load(['createur', 'receveur', 'client', 'attachement']);
        
        return Inertia::render('Demandes/Show', [
            'demande' => $demande,
            'canEdit' => auth()->user()->can('update', $demande),
            'canAssign' => auth()->user()->can('assign', $demande),
            'canComplete' => auth()->user()->can('complete', $demande),
            'canConvert' => auth()->user()->can('convertToAttachement', $demande),
        ]);
    }

    public function edit(Demande $demande): Response
    {
        // Cache des utilisateurs et clients avec TTL de 5 minutes
        $users = cache()->remember('users_for_demande_form', 300, function () {
            return User::pourFormulaires()->get();
        });
        
        $clients = cache()->remember('clients_for_demande_form', 300, function () {
            return Client::pourFormulaires()->get();
        });

        return Inertia::render('Demandes/Edit', [
            'demande' => $demande->load(['createur:id,name', 'receveur:id,name', 'client:id,nom']),
            'users' => $users,
            'clients' => $clients,
        ]);
    }

    public function update(UpdateDemandeRequest $request, Demande $demande): RedirectResponse
    {
        $demande->update($request->validated());

        return redirect()->route('demandes.show', $demande)
            ->with('success', 'Demande mise à jour avec succès.');
    }

    public function assign(Request $request, Demande $demande): RedirectResponse
    {
        $this->authorize('assign', $demande);

        $request->validate([
            'receveur_id' => 'required|exists:users,id',
        ]);

        $demande->update([
            'receveur_id' => $request->receveur_id,
            'statut' => 'assignee',
            'date_assignation' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Demande assignée avec succès.');
    }

    public function complete(Request $request, Demande $demande): RedirectResponse
    {
        $this->authorize('complete', $demande);

        $request->validate([
            'notes_receveur' => 'nullable|string',
        ]);

        $demande->update([
            'statut' => 'terminee',
            'date_completion' => now(),
            'notes_receveur' => $request->notes_receveur,
        ]);

        return redirect()->back()
            ->with('success', 'Demande complétée avec succès.');
    }

    public function convertToAttachement(Demande $demande): RedirectResponse
    {
        $this->authorize('convertToAttachement', $demande);

        if ($demande->statut !== 'terminee') {
            return redirect()->back()
                ->with('error', 'Seules les demandes terminées peuvent être converties en attachement.');
        }

        return redirect()->route('attachements.create', [
            'from_demande' => $demande->id,
        ])->with('info', 'Préremplissage de l\'attachement à partir de la demande.');
    }

    public function destroy(Demande $demande): RedirectResponse
    {
        $demande->delete();

        return redirect()->route('demandes.index')
            ->with('success', 'Demande supprimée avec succès.');
    }
}
