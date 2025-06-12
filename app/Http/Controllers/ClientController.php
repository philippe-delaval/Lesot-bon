<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        
        $clients = Client::query()
            ->when($search, function ($query, $search) {
                $query->search($search);
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'adresse' => 'required|string|max:255',
            'complement_adresse' => 'nullable|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client): Response
    {
        $client->load(['attachements' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'adresse' => 'required|string|max:255',
            'complement_adresse' => 'nullable|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client modifié avec succès.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * API: Récupérer tous les clients pour la liste déroulante
     */
    public function apiList(Request $request)
    {
        $clients = Client::query()
            ->orderBy('nom')
            ->get(['id', 'nom', 'email', 'adresse', 'complement_adresse', 'code_postal', 'ville']);

        return response()->json($clients);
    }

    /**
     * API: Rechercher des clients par terme
     */
    public function apiSearch(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }
        
        $clients = Client::query()
            ->search($search)
            ->orderBy('nom')
            ->limit(10)
            ->get(['id', 'nom', 'email', 'adresse', 'complement_adresse', 'code_postal', 'ville']);

        return response()->json($clients);
    }
}