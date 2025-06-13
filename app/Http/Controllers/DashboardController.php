<?php

namespace App\Http\Controllers;

use App\Models\Attachement;
use App\Models\Demande;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Statistiques des attachements
        $attachementsStats = [
            'today' => Attachement::whereDate('created_at', $today)->count(),
            'week' => Attachement::where('created_at', '>=', $thisWeek)->count(),
            'month' => Attachement::where('created_at', '>=', $thisMonth)->count(),
            'total' => Attachement::count(),
        ];

        // Statistiques des demandes
        $demandesStats = [
            'en_attente' => Demande::where('statut', 'en_attente')->count(),
            'mes_assignees' => Demande::assigneesA($user->id)->count(),
            'mes_creees' => Demande::creesPar($user->id)->enCours()->count(),
            'today' => Demande::whereDate('created_at', $today)->count(),
            'week' => Demande::where('created_at', '>=', $thisWeek)->count(),
            'month' => Demande::where('created_at', '>=', $thisMonth)->count(),
            'total' => Demande::count(),
        ];

        // Demandes récentes (les 5 dernières)
        $demandesRecentes = Demande::with(['createur', 'receveur', 'client'])
            ->where(function ($query) use ($user) {
                $query->where('createur_id', $user->id)
                      ->orWhere('receveur_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Demandes urgentes
        $demandesUrgentes = Demande::with(['createur', 'receveur', 'client'])
            ->where('priorite', 'urgente')
            ->whereIn('statut', ['en_attente', 'assignee', 'en_cours'])
            ->where(function ($query) use ($user) {
                $query->where('createur_id', $user->id)
                      ->orWhere('receveur_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Attachements récents (les 5 derniers)
        $attachementsRecents = Attachement::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'attachements' => $attachementsStats,
            'demandes' => $demandesStats,
            'demandes_recentes' => $demandesRecentes,
            'demandes_urgentes' => $demandesUrgentes,
            'attachements_recents' => $attachementsRecents,
        ]);
    }
}
