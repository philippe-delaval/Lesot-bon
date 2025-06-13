<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Filter, Search } from 'lucide-vue-next';
import { 
  type Demande, 
  type DemandeFilters, 
  type DemandeStats
} from '@/types/index.d';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/components/ui/select';
import DemandeCard from '@/components/Demandes/DemandeCard.vue';
import Heading from '@/components/Heading.vue';

interface Props {
  demandes: {
    data: Demande[];
    links: any[];
    meta: any;
  };
  filters: DemandeFilters;
  stats: DemandeStats;
}

const props = defineProps<Props>();

const filters = ref<DemandeFilters>({
  role: props.filters.role || 'all',
  statut: props.filters.statut || 'all',
  priorite: props.filters.priorite || 'all',
  search: props.filters.search || '',
});

const applyFilters = () => {
  router.get(route('demandes.index'), filters.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filters.value = {
    role: 'all',
    statut: 'all',
    priorite: 'all',
    search: '',
  };
  applyFilters();
};

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Demandes', href: route('demandes.index') },
];

const statsCards = computed(() => [
  {
    title: 'En attente',
    value: props.stats.en_attente,
    description: 'Demandes non assignées',
    color: 'text-yellow-600',
  },
  {
    title: 'Mes assignées',
    value: props.stats.assignees,
    description: 'Demandes qui me sont assignées',
    color: 'text-blue-600',
  },
  {
    title: 'Mes créées',
    value: props.stats.mes_creees,
    description: 'Demandes que j\'ai créées',
    color: 'text-green-600',
  },
]);
</script>

<template>
  <Head title="Demandes en cours" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <Heading>
          Demandes en cours
          <template #description>
            Gérez les demandes d'intervention et suivez leur progression
          </template>
        </Heading>

        <div class="flex gap-2">
          <Button as="link" :href="route('demandes.create')" class="gap-2">
            <Plus class="h-4 w-4" />
            Nouvelle demande
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card v-for="stat in statsCards" :key="stat.title">
          <CardHeader class="pb-2">
            <CardTitle class="text-sm font-medium text-muted-foreground">
              {{ stat.title }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div :class="['text-2xl font-bold', stat.color]">
              {{ stat.value }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ stat.description }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Filter class="h-4 w-4" />
            Filtres
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-2">
              <label class="text-sm font-medium">Recherche</label>
              <div class="relative">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  v-model="filters.search"
                  placeholder="Rechercher..."
                  class="pl-8"
                  @keyup.enter="applyFilters"
                />
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium">Rôle</label>
              <Select v-model="filters.role" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un rôle" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes mes demandes</SelectItem>
                  <SelectItem value="creees">Mes demandes créées</SelectItem>
                  <SelectItem value="assignees">Mes demandes assignées</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium">Statut</label>
              <Select v-model="filters.statut" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un statut" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Tous les statuts</SelectItem>
                  <SelectItem value="en_attente">En attente</SelectItem>
                  <SelectItem value="assignee">Assignée</SelectItem>
                  <SelectItem value="en_cours">En cours</SelectItem>
                  <SelectItem value="terminee">Terminée</SelectItem>
                  <SelectItem value="annulee">Annulée</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium">Priorité</label>
              <Select v-model="filters.priorite" @update:modelValue="applyFilters">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner une priorité" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes les priorités</SelectItem>
                  <SelectItem value="urgente">Urgente</SelectItem>
                  <SelectItem value="haute">Haute</SelectItem>
                  <SelectItem value="normale">Normale</SelectItem>
                  <SelectItem value="basse">Basse</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <Button variant="outline" @click="resetFilters">
              Réinitialiser
            </Button>
            <Button @click="applyFilters">
              Appliquer
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Demandes List -->
      <div v-if="demandes.data.length > 0" class="space-y-4">
        <div class="grid grid-cols-1 gap-4">
          <DemandeCard 
            v-for="demande in demandes.data" 
            :key="demande.id"
            :demande="demande"
          />
        </div>

        <!-- Pagination -->
        <div v-if="demandes.meta.total > demandes.meta.per_page" class="flex justify-center">
          <nav class="flex items-center gap-2">
            <template v-for="link in demandes.links" :key="link.url">
              <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm rounded-md transition-colors',
                  link.active 
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
                v-html="link.label"
              />
              <span
                v-else
                class="px-3 py-2 text-sm text-gray-400"
                v-html="link.label"
              />
            </template>
          </nav>
        </div>
      </div>

      <!-- Empty State -->
      <Card v-else class="text-center py-12">
        <CardContent>
          <div class="space-y-4">
            <div class="text-4xl text-gray-300">📋</div>
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                Aucune demande trouvée
              </h3>
              <p class="text-gray-500 mt-1">
                Créez votre première demande d'intervention pour commencer.
              </p>
            </div>
            <Button as="link" :href="route('demandes.create')" class="gap-2">
              <Plus class="h-4 w-4" />
              Créer une demande
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>