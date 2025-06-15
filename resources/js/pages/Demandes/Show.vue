<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { type Demande } from '@/types/index.d';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, User, MapPin, Calendar, FileText, AlertCircle } from 'lucide-vue-next';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import Heading from '@/components/Heading.vue';

interface Props {
  demande: Demande;
  canEdit: boolean;
  canAssign: boolean;
  canComplete: boolean;
  canConvert: boolean;
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Demandes', href: route('demandes.index') },
  { title: `Demande #${props.demande.numero_demande}`, href: route('demandes.show', props.demande.id) },
];

const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: fr });
};

const getStatutColor = (statut: string) => {
  const colors = {
    en_attente: 'bg-yellow-100 text-yellow-800',
    assignee: 'bg-blue-100 text-blue-800',
    en_cours: 'bg-purple-100 text-purple-800',
    terminee: 'bg-green-100 text-green-800',
    annulee: 'bg-red-100 text-red-800',
  };
  return colors[statut] || 'bg-gray-100 text-gray-800';
};

const getPrioriteColor = (priorite: string) => {
  const colors = {
    normale: 'bg-green-100 text-green-800 border-green-200',
    haute: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    urgente: 'bg-red-100 text-red-800 border-red-200',
  };
  return colors[priorite] || 'bg-green-100 text-green-800 border-green-200';
};
</script>

<template>
  <Head :title="`Demande #${demande.numero_demande}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container-responsive max-w-7xl mx-auto">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-4">
            <Button variant="ghost" size="sm" @click="() => router.visit(route('demandes.index'))">
              <ArrowLeft class="h-4 w-4" />
              Retour
            </Button>
            <Heading :title="`Demande #${demande.numero_demande}`">
              <template #description>
                {{ demande.titre }}
              </template>
            </Heading>
          </div>

          <div class="flex gap-2">
            <Button v-if="canEdit" variant="outline" size="sm" @click="() => router.visit(route('demandes.edit', demande.id))">
              <Edit class="h-4 w-4" />
              Modifier
            </Button>
          </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-muted-foreground">Statut</p>
                  <Badge :class="getStatutColor(demande.statut)" class="mt-1">
                    {{ demande.statut.replace('_', ' ') }}
                  </Badge>
                </div>
                <AlertCircle class="h-8 w-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-muted-foreground">Priorité</p>
                  <Badge :class="getPrioriteColor(demande.priorite)" class="mt-1 border">
                    {{ demande.priorite }}
                  </Badge>
                </div>
                <AlertCircle class="h-8 w-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-muted-foreground">Date de création</p>
                  <p class="text-lg font-semibold">{{ formatDate(demande.date_demande) }}</p>
                </div>
                <Calendar class="h-8 w-8 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Description
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-sm text-muted-foreground leading-relaxed">
                  {{ demande.description }}
                </p>
              </CardContent>
            </Card>

            <!-- Location -->
            <Card v-if="demande.lieu_intervention">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="h-5 w-5" />
                  Lieu d'intervention
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-sm text-muted-foreground">
                  {{ demande.lieu_intervention }}
                </p>
              </CardContent>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- People -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5" />
                  Personnes
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <p class="text-sm font-medium text-muted-foreground">Créé par</p>
                  <p class="text-sm">{{ demande.createur?.name || 'N/A' }}</p>
                </div>
                
                <div v-if="demande.receveur">
                  <p class="text-sm font-medium text-muted-foreground">Assigné à</p>
                  <p class="text-sm">{{ demande.receveur.name }}</p>
                </div>

                <div v-if="demande.client">
                  <p class="text-sm font-medium text-muted-foreground">Client</p>
                  <p class="text-sm">{{ demande.client.nom }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Dates -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Calendar class="h-5 w-5" />
                  Dates importantes
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <p class="text-sm font-medium text-muted-foreground">Date de demande</p>
                  <p class="text-sm">{{ formatDate(demande.date_demande) }}</p>
                </div>

                <div v-if="demande.date_souhaite_intervention">
                  <p class="text-sm font-medium text-muted-foreground">Date souhaitée</p>
                  <p class="text-sm">{{ formatDate(demande.date_souhaite_intervention) }}</p>
                </div>

                <div v-if="demande.date_assignation">
                  <p class="text-sm font-medium text-muted-foreground">Date d'assignation</p>
                  <p class="text-sm">{{ formatDate(demande.date_assignation) }}</p>
                </div>

                <div v-if="demande.date_completion">
                  <p class="text-sm font-medium text-muted-foreground">Date de completion</p>
                  <p class="text-sm">{{ formatDate(demande.date_completion) }}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>