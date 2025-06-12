<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Models\Attachement;
use App\Http\Requests\AttachementStoreRequest;
use Carbon\Carbon;

class AttachementController extends Controller
{
    /**
     * Afficher le formulaire d'attachement
     */
    public function create()
    {
        return Inertia::render('Attachements/Create');
    }

    /**
     * Enregistrer un nouvel attachement
     */
    public function store(AttachementStoreRequest $request)
    {
        // Les données sont déjà validées par AttachementStoreRequest
        $validated = $request->validated();

        try {
            // Décoder les données JSON
            $fournitures = json_decode($validated['fournitures_travaux'], true);
            $geolocation = $validated['geolocation'] ? json_decode($validated['geolocation'], true) : null;

            // Générer un numéro unique si pas fourni
            if (empty($validated['numero_dossier'])) {
                $validated['numero_dossier'] = 'ATT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }

            // Sauvegarder les signatures
            $signatureEntreprisePath = $this->saveSignature($validated['signature_entreprise'], 'entreprise', $validated['numero_dossier']);
            $signatureClientPath = $this->saveSignature($validated['signature_client'], 'client', $validated['numero_dossier']);

            // Sauvegarder le PDF
            $pdfPath = $request->file('pdf')->store('attachements/pdf/' . date('Y/m'), 'public');

            // Créer l'enregistrement en base de données
            $attachement = Attachement::create([
                'numero_dossier' => $validated['numero_dossier'],
                'client_nom' => $validated['client_nom'],
                'client_email' => $validated['client_email'],
                'client_adresse_facturation' => $validated['client_adresse_facturation'],
                'lieu_intervention' => $validated['lieu_intervention'],
                'date_intervention' => $validated['date_intervention'],
                'designation_travaux' => $validated['designation_travaux'],
                'fournitures_travaux' => $fournitures,
                'temps_total_passe' => $validated['temps_total_passe'],
                'signature_entreprise_path' => $signatureEntreprisePath,
                'signature_client_path' => $signatureClientPath,
                'pdf_path' => $pdfPath,
                'geolocation' => $geolocation,
                'created_by' => auth()->id(),
            ]);

            // Envoyer une copie par email à l'entreprise (optionnel)
            $this->sendInternalNotification($attachement);

            return redirect()->route('attachements.index')
                ->with('success', 'Attachement créé avec succès!');

        } catch (\Exception $e) {
            \Log::error('Erreur création attachement: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création de l\'attachement.'])
                ->withInput();
        }
    }

    /**
     * Afficher un attachement spécifique
     */
    public function show(Attachement $attachement)
    {
        return Inertia::render('Attachements/Show', [
            'attachement' => $attachement->load('creator')
        ]);
    }

    /**
     * Lister tous les attachements
     */
    public function index(Request $request)
    {
        $query = Attachement::with('creator')
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                    ->orWhere('client_nom', 'like', "%{$search}%")
                    ->orWhere('lieu_intervention', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_intervention', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_intervention', '<=', $request->date_to);
        }

        $attachements = $query->paginate(20);

        return Inertia::render('Attachements/Index', [
            'attachements' => $attachements,
            'filters' => $request->only(['search', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Télécharger le PDF d'un attachement
     */
    public function downloadPdf(Attachement $attachement)
    {
        if (!Storage::disk('public')->exists($attachement->pdf_path)) {
            abort(404, 'Fichier PDF introuvable');
        }

        return Storage::disk('public')->download(
            $attachement->pdf_path,
            "attachement_{$attachement->numero_dossier}.pdf"
        );
    }

    /**
     * Sauvegarder une signature en tant qu'image
     */
    private function saveSignature($signatureData, $type, $numeroDossier)
    {
        // Extraire les données base64
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        $imageName = "{$numeroDossier}_{$type}_" . time() . '.png';
        
        $path = 'attachements/signatures/' . date('Y/m') . '/' . $imageName;
        
        Storage::disk('public')->put($path, base64_decode($image));
        
        return $path;
    }

    /**
     * Envoyer une notification interne
     */
    private function sendInternalNotification(Attachement $attachement)
    {
        try {
            // Vous pouvez personnaliser cette partie selon vos besoins
            // Par exemple, envoyer un email à l'administrateur ou à l'équipe
            
            // Mail::to(config('mail.admin_email'))->send(new AttachementCreated($attachement));
            
        } catch (\Exception $e) {
            \Log::error('Erreur envoi notification: ' . $e->getMessage());
        }
    }

    /**
     * API : Lister les attachements
     */
    public function apiIndex(Request $request)
    {
        $query = Attachement::with('creator')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->search($search);
        }

        $attachements = $query->get();

        return response()->json([
            'data' => $attachements
        ]);
    }

    /**
     * API : Afficher un attachement
     */
    public function apiShow(Attachement $attachement)
    {
        return response()->json([
            'data' => $attachement->load('creator')
        ]);
    }
}
