<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, FileText, Clock, AlertTriangle, TrendingUp, Users } from 'lucide-vue-next';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import StatutBadge from '@/components/Demandes/StatutBadge.vue';
import PrioriteBadge from '@/components/Demandes/PrioriteBadge.vue';
import Heading from '@/components/Heading.vue';
import { type Demande, type Attachement } from '@/types/index.d';

interface Props {
  attachements: {
    today: number;
    week: number;
    month: number;
    total: number;
  };
  demandes: {
    en_attente: number;
    mes_assignees: number;
    mes_creees: number;
    today: number;
    week: number;
    month: number;
    total: number;
  };
  demandes_recentes: Demande[];
  demandes_urgentes: Demande[];
  attachements_recents: Attachement[];
}

defineProps<Props>();

const formatDate = (date: string) => {
  return format(new Date(date), 'dd MMM yyyy', { locale: fr });
};
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />
    
    <!-- Container responsive avec marges mobile-first -->
    <div class="container-responsive max-w-7xl mx-auto">
      <div class="space-y-6 md:space-y-8 lg:space-y-10">
        <Heading title="Vue d'ensemble">
          <template #description>
            Suivez l'activité de vos demandes et attachements
          </template>
        </Heading>

        <!-- Statistiques principales -->
        <div class="stats-grid">
        <!-- Demandes en attente -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">En attente</CardTitle>
            <ClipboardList class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">{{ demandes.en_attente }}</div>
            <p class="text-xs text-muted-foreground">
              Demandes non assignées
            </p>
          </CardContent>
        </Card>

        <!-- Mes demandes assignées -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Assignées</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">{{ demandes.mes_assignees }}</div>
            <p class="text-xs text-muted-foreground">
              Qui me sont assignées
            </p>
          </CardContent>
        </Card>

        <!-- Mes demandes créées -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Créées</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">{{ demandes.mes_creees }}</div>
            <p class="text-xs text-muted-foreground">
              Que j'ai créées
            </p>
          </CardContent>
        </Card>

        <!-- Attachements du mois -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Attachements</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">{{ attachements.month }}</div>
            <p class="text-xs text-muted-foreground">
              Ce mois-ci
            </p>
          </CardContent>
        </Card>
      </div>

        <div class="sections-grid">
        <!-- Demandes urgentes -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <AlertTriangle class="h-5 w-5 text-red-500" />
              Demandes urgentes
            </CardTitle>
            <Button variant="outline" size="sm" @click="() => $inertia.visit(route('demandes.index', { priorite: 'urgente' }))">
              Voir toutes
            </Button>
          </CardHeader>
          <CardContent>
            <div v-if="demandes_urgentes.length > 0" class="space-y-3">
              <div 
                v-for="demande in demandes_urgentes" 
                :key="demande.id"
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 md:p-5 bg-red-50 rounded-lg border border-red-200 gap-3 sm:gap-4"
              >
                <div class="flex-1">
                  <Link 
                    :href="route('demandes.show', demande.id)"
                    class="font-medium text-sm md:text-base hover:underline block"
                  >
                    {{ demande.titre }}
                  </Link>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <StatutBadge :statut="demande.statut" />
                    <span class="text-xs text-gray-500">{{ formatDate(demande.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-6 text-gray-500">
              <AlertTriangle class="h-8 w-8 mx-auto mb-2 text-gray-300" />
              <p class="text-sm">Aucune demande urgente</p>
            </div>
          </CardContent>
        </Card>

        <!-- Demandes récentes -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <Clock class="h-5 w-5 text-blue-500" />
              Demandes récentes
            </CardTitle>
            <Button variant="outline" size="sm" @click="() => $inertia.visit(route('demandes.index'))">
              Voir toutes
            </Button>
          </CardHeader>
          <CardContent>
            <div v-if="demandes_recentes.length > 0" class="space-y-3">
              <div 
                v-for="demande in demandes_recentes" 
                :key="demande.id"
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 md:p-5 bg-gray-50 rounded-lg gap-3 sm:gap-4"
              >
                <div class="flex-1">
                  <Link 
                    :href="route('demandes.show', demande.id)"
                    class="font-medium text-sm md:text-base hover:underline block"
                  >
                    {{ demande.titre }}
                  </Link>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <StatutBadge :statut="demande.statut" />
                    <PrioriteBadge :priorite="demande.priorite" />
                    <span class="text-xs text-gray-500">{{ formatDate(demande.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-6 text-gray-500">
              <Clock class="h-8 w-8 mx-auto mb-2 text-gray-300" />
              <p class="text-sm">Aucune demande récente</p>
            </div>
          </CardContent>
        </Card>
      </div>

        <!-- Statistiques détaillées -->
        <div class="stats-details-grid">
        <!-- Stats demandes -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Demandes</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Aujourd'hui</span>
              <span class="font-medium tabular-nums">{{ demandes.today }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Cette semaine</span>
              <span class="font-medium tabular-nums">{{ demandes.week }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Ce mois</span>
              <span class="font-medium tabular-nums">{{ demandes.month }}</span>
            </div>
            <div class="flex justify-between items-center border-t pt-3">
              <span class="text-sm md:text-base text-gray-600 font-medium">Total</span>
              <span class="font-bold text-lg tabular-nums">{{ demandes.total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Stats attachements -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Attachements</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Aujourd'hui</span>
              <span class="font-medium tabular-nums">{{ attachements.today }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Cette semaine</span>
              <span class="font-medium tabular-nums">{{ attachements.week }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm md:text-base text-gray-600">Ce mois</span>
              <span class="font-medium tabular-nums">{{ attachements.month }}</span>
            </div>
            <div class="flex justify-between items-center border-t pt-3">
              <span class="text-sm md:text-base text-gray-600 font-medium">Total</span>
              <span class="font-bold text-lg tabular-nums">{{ attachements.total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Actions rapides -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Actions rapides</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button class="w-full justify-start" variant="outline" @click="() => $inertia.visit(route('demandes.create'))">
              <ClipboardList class="mr-2 h-4 w-4" />
              Nouvelle demande
            </Button>
            <Button class="w-full justify-start" variant="outline" @click="() => $inertia.visit(route('attachements.create'))">
              <FileText class="mr-2 h-4 w-4" />
              Nouvel attachement
            </Button>
            <Button class="w-full justify-start" variant="outline" @click="() => $inertia.visit(route('clients.create'))">
              <Users class="mr-2 h-4 w-4" />
              Nouveau client
            </Button>
          </CardContent>
        </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
