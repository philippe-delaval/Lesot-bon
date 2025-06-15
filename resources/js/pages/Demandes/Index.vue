<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';

interface Props {
  demandes: {
    data: any[];
    links: any[];
    meta: any;
  };
  filters: any;
  stats: any;
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Demandes', href: route('demandes.index') },
];

const creerNouvelleDemande = () => {
  router.visit(route('demandes.create'));
};
</script>

<template>
  <Head title="Demandes en cours" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container-responsive max-w-7xl mx-auto">
      <div class="space-y-4 md:space-y-6 lg:space-y-8">
        <!-- Header responsive -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <Heading title="Demandes en cours">
            <template #description>
              Gérez les demandes d'intervention et suivez leur progression
            </template>
          </Heading>

          <div class="flex gap-2">
            <Button @click="creerNouvelleDemande" class="w-full sm:w-auto gap-2">
              <Plus class="h-4 w-4" />
              Nouvelle demande
            </Button>
          </div>
        </div>

        <!-- Stats Cards responsive -->
        <div class="stats-grid">
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                En attente
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-yellow-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">
                {{ stats.en_attente }}
              </div>
              <p class="text-xs text-muted-foreground mt-1">
                Demandes non assignées
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Mes assignées
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-blue-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">
                {{ stats.assignees }}
              </div>
              <p class="text-xs text-muted-foreground mt-1">
                Demandes qui me sont assignées
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-muted-foreground">
                Mes créées
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-green-600 tabular-nums leading-none align-baseline min-h-[2rem] flex items-baseline">
                {{ stats.mes_creees }}
              </div>
              <p class="text-xs text-muted-foreground mt-1">
                Demandes que j'ai créées
              </p>
            </CardContent>
          </Card>
        </div>

        <!-- Liste simple des demandes -->
        <div v-if="demandes.data.length > 0" class="space-y-4">
          <div class="grid grid-cols-1 gap-3 md:gap-4 lg:gap-6">
            <Card v-for="demande in demandes.data" :key="demande.id">
              <CardContent class="p-6">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="font-semibold">{{ demande.titre }}</h3>
                    <p class="text-sm text-muted-foreground">{{ demande.description }}</p>
                    <div class="mt-2 text-xs text-muted-foreground">
                      Priorité: {{ demande.priorite }} | Statut: {{ demande.statut }}
                    </div>
                  </div>
                  <div class="text-sm text-muted-foreground">
                    #{{ demande.numero_demande }}
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Empty State responsive -->
        <Card v-else class="text-center py-8 md:py-12">
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
              <Button @click="creerNouvelleDemande" class="w-full sm:w-auto gap-2">
                <Plus class="h-4 w-4" />
                Créer une demande
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>