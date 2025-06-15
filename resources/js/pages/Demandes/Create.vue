<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type User, type Client, type DemandeForm } from '@/types/index.d';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/components/ui/select';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

interface Props {
  users: User[];
  clients: Client[];
}

const props = defineProps<Props>();

const form = useForm<DemandeForm>({
  titre: '',
  description: '',
  priorite: 'normale',
  client_id: undefined,
  lieu_intervention: '',
  date_souhaite_intervention: '',
  receveur_id: undefined,
});


const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Demandes', href: route('demandes.index') },
  { title: 'Nouvelle demande', href: route('demandes.create') },
];

const submit = () => {
  form.post(route('demandes.store'), {
    onSuccess: () => {
      // La redirection est gérée par le contrôleur
    },
  });
};
</script>

<template>
  <Head title="Nouvelle demande" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Container responsive optimisé -->
    <div class="container-responsive max-w-4xl mx-auto">
      <div class="space-y-4 md:space-y-6">
        <Heading title="Nouvelle demande d'intervention">
          <template #description>
            Créez une nouvelle demande qui sera traitée par l'équipe
          </template>
        </Heading>

        <Card>
          <CardHeader>
            <CardTitle>Informations de la demande</CardTitle>
          </CardHeader>
          <CardContent class="p-4 md:p-6">
            <form @submit.prevent="submit" class="space-y-4 md:space-y-6">
              <!-- Grille responsive pour les champs principaux -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                <!-- Titre -->
                <div class="space-y-2">
                  <Label for="titre">Titre *</Label>
                  <Input
                    id="titre"
                    v-model="form.titre"
                    placeholder="Résumé de l'intervention demandée"
                    :class="{ 'border-red-500': form.errors.titre }"
                  />
                  <InputError :message="form.errors.titre" />
                </div>

                <!-- Priorité -->
                <div class="space-y-2">
                  <Label for="priorite">Priorité *</Label>
                  <select 
                    v-model="form.priorite"
                    :class="[
                      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                      { 'border-red-500': form.errors.priorite }
                    ]"
                  >
                    <option value="">Sélectionner une priorité</option>
                    <option value="normale">Normale</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                  </select>
                  <InputError :message="form.errors.priorite" />
                </div>
              </div>

              <!-- Description (pleine largeur) -->
              <div class="space-y-2">
                <Label for="description">Description *</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  placeholder="Description détaillée de l'intervention demandée"
                  :rows="4"
                  :class="{ 'border-red-500': form.errors.description }"
                />
                <InputError :message="form.errors.description" />
              </div>

              <!-- Grille responsive pour les champs secondaires -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
                <!-- Client -->
                <div class="space-y-2">
                  <Label for="client">Client</Label>
                  <select 
                    v-model="form.client_id"
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">Sélectionner un client (optionnel)</option>
                    <option
                      v-for="client in clients"
                      :key="client.id"
                      :value="client.id"
                    >
                      {{ client.nom }} - {{ client.email }}
                    </option>
                  </select>
                  <InputError :message="form.errors.client_id" />
                </div>

                <!-- Date souhaitée -->
                <div class="space-y-2">
                  <Label for="date_souhaite_intervention">Date souhaitée</Label>
                  <Input
                    id="date_souhaite_intervention"
                    v-model="form.date_souhaite_intervention"
                    type="date"
                    :min="new Date().toISOString().slice(0, 10)"
                  />
                  <InputError :message="form.errors.date_souhaite_intervention" />
                </div>
              </div>

              <!-- Lieu d'intervention (pleine largeur) -->
              <div class="space-y-2">
                <Label for="lieu_intervention">Lieu d'intervention</Label>
                <Input
                  id="lieu_intervention"
                  v-model="form.lieu_intervention"
                  placeholder="Adresse ou lieu de l'intervention"
                />
                <InputError :message="form.errors.lieu_intervention" />
              </div>

              <!-- Assignation directe (pleine largeur) -->
              <div class="space-y-2">
                <Label for="receveur">Assigner directement à</Label>
                <select 
                  v-model="form.receveur_id"
                  class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">Laisser vide pour assignation ultérieure</option>
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.name }} - {{ user.email }}
                  </option>
                </select>
                <InputError :message="form.errors.receveur_id" />
              </div>

              <!-- Boutons responsive -->
              <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 md:pt-6">
                <Button
                  type="button"
                  variant="outline"
                  as="link"
                  :href="route('demandes.index')"
                  class="w-full sm:w-auto"
                >
                  Annuler
                </Button>
                <Button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full sm:w-auto min-w-[120px]"
                >
                  <span v-if="form.processing">Création...</span>
                  <span v-else>Créer la demande</span>
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>