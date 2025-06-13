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
    
    <div class="space-y-6">
      <Heading>
        Vue d'ensemble
        <template #description>
          Suivez l'activité de vos demandes et attachements
        </template>
      </Heading>

      <!-- Statistiques principales -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Demandes en attente -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">En attente</CardTitle>
            <ClipboardList class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ demandes.en_attente }}</div>
            <p class="text-xs text-muted-foreground">
              Demandes non assignées
            </p>
          </CardContent>
        </Card>

        <!-- Mes demandes assignées -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Mes assignées</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ demandes.mes_assignees }}</div>
            <p class="text-xs text-muted-foreground">
              Demandes qui me sont assignées
            </p>
          </CardContent>
        </Card>

        <!-- Mes demandes créées -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Mes créées</CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">{{ demandes.mes_creees }}</div>
            <p class="text-xs text-muted-foreground">
              Demandes en cours que j'ai créées
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
            <div class="text-2xl font-bold">{{ attachements.month }}</div>
            <p class="text-xs text-muted-foreground">
              Ce mois-ci
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- Demandes urgentes -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <AlertTriangle class="h-5 w-5 text-red-500" />
              Demandes urgentes
            </CardTitle>
            <Button variant="outline" size="sm" as="link" :href="route('demandes.index', { priorite: 'urgente' })">
              Voir toutes
            </Button>
          </CardHeader>
          <CardContent>
            <div v-if="demandes_urgentes.length > 0" class="space-y-3">
              <div 
                v-for="demande in demandes_urgentes" 
                :key="demande.id"
                class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200"
              >
                <div class="flex-1">
                  <Link 
                    :href="route('demandes.show', demande.id)"
                    class="font-medium text-sm hover:underline"
                  >
                    {{ demande.titre }}
                  </Link>
                  <div class="flex items-center gap-2 mt-1">
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
            <Button variant="outline" size="sm" as="link" :href="route('demandes.index')">
              Voir toutes
            </Button>
          </CardHeader>
          <CardContent>
            <div v-if="demandes_recentes.length > 0" class="space-y-3">
              <div 
                v-for="demande in demandes_recentes" 
                :key="demande.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-1">
                  <Link 
                    :href="route('demandes.show', demande.id)"
                    class="font-medium text-sm hover:underline"
                  >
                    {{ demande.titre }}
                  </Link>
                  <div class="flex items-center gap-2 mt-1">
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
      <div class="grid gap-4 md:grid-cols-3">
        <!-- Stats demandes -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Demandes</CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Aujourd'hui</span>
              <span class="font-medium">{{ demandes.today }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Cette semaine</span>
              <span class="font-medium">{{ demandes.week }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Ce mois</span>
              <span class="font-medium">{{ demandes.month }}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
              <span class="text-sm text-gray-600">Total</span>
              <span class="font-bold">{{ demandes.total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Stats attachements -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Attachements</CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Aujourd'hui</span>
              <span class="font-medium">{{ attachements.today }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Cette semaine</span>
              <span class="font-medium">{{ attachements.week }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Ce mois</span>
              <span class="font-medium">{{ attachements.month }}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
              <span class="text-sm text-gray-600">Total</span>
              <span class="font-bold">{{ attachements.total }}</span>
            </div>
          </CardContent>
        </Card>

        <!-- Actions rapides -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Actions rapides</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button class="w-full justify-start" variant="outline" as="link" :href="route('demandes.create')">
              <ClipboardList class="mr-2 h-4 w-4" />
              Nouvelle demande
            </Button>
            <Button class="w-full justify-start" variant="outline" as="link" :href="route('attachements.create')">
              <FileText class="mr-2 h-4 w-4" />
              Nouvel attachement
            </Button>
            <Button class="w-full justify-start" variant="outline" as="link" :href="route('clients.create')">
              <Users class="mr-2 h-4 w-4" />
              Nouveau client
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
