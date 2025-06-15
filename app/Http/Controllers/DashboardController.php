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
        $today = now()->toDateString();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Statistiques des attachements optimisées
        $attachementsStats = Attachement::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as month
        ', [$today, $thisWeek, $thisMonth])->first();

        // Statistiques des demandes optimisées
        $demandesStats = Demande::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN createur_id = ? AND statut NOT IN (\'terminee\', \'annulee\') THEN 1 ELSE 0 END) as mes_creees,
            SUM(CASE WHEN receveur_id = ? AND statut IN (\'assignee\', \'en_cours\') THEN 1 ELSE 0 END) as mes_assignees,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as month
        ', [
            'en_attente',
            $user->id,
            $user->id,
            $today,
            $thisWeek,
            $thisMonth
        ])->first();

        // Demandes récentes (les 5 dernières) avec relations optimisées
        $demandesRecentes = Demande::with([
                'createur:id,name',
                'receveur:id,name',
                'client:id,nom'
            ])
            ->where(function ($query) use ($user) {
                $query->where('createur_id', $user->id)
                      ->orWhere('receveur_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Demandes urgentes (5 dernières) avec relations optimisées
        $demandesUrgentes = Demande::with([
                'createur:id,name',
                'receveur:id,name',
                'client:id,nom'
            ])
            ->where('priorite', 'urgente')
            ->whereIn('statut', ['en_attente', 'assignee', 'en_cours'])
            ->where(function ($query) use ($user) {
                $query->where('createur_id', $user->id)
                      ->orWhere('receveur_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Attachements récents (5 derniers) avec relations optimisées
        $attachementsRecents = Attachement::with('client:id,nom')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'attachements' => [
                'today' => $attachementsStats->today ?? 0,
                'week' => $attachementsStats->week ?? 0,
                'month' => $attachementsStats->month ?? 0,
                'total' => $attachementsStats->total ?? 0,
            ],
            'demandes' => [
                'en_attente' => $demandesStats->en_attente ?? 0,
                'mes_assignees' => $demandesStats->mes_assignees ?? 0,
                'mes_creees' => $demandesStats->mes_creees ?? 0,
                'today' => $demandesStats->today ?? 0,
                'week' => $demandesStats->week ?? 0,
                'month' => $demandesStats->month ?? 0,
                'total' => $demandesStats->total ?? 0,
            ],
            'demandes_recentes' => $demandesRecentes,
            'demandes_urgentes' => $demandesUrgentes,
            'attachements_recents' => $attachementsRecents,
        ]);
    }
}
