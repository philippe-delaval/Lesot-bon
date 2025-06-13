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
    <div class="max-w-2xl mx-auto space-y-6">
      <Heading>
        Nouvelle demande d'intervention
        <template #description>
          Créez une nouvelle demande qui sera traitée par l'équipe
        </template>
      </Heading>

      <Card>
        <CardHeader>
          <CardTitle>Informations de la demande</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
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

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Description *</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Description détaillée de l'intervention demandée"
                rows="4"
                :class="{ 'border-red-500': form.errors.description }"
              />
              <InputError :message="form.errors.description" />
            </div>

            <!-- Priorité -->
            <div class="space-y-2">
              <Label for="priorite">Priorité *</Label>
              <Select v-model="form.priorite">
                <SelectTrigger :class="{ 'border-red-500': form.errors.priorite }">
                  <SelectValue placeholder="Sélectionner une priorité" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="basse">Basse</SelectItem>
                  <SelectItem value="normale">Normale</SelectItem>
                  <SelectItem value="haute">Haute</SelectItem>
                  <SelectItem value="urgente">Urgente</SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.priorite" />
            </div>

            <!-- Client -->
            <div class="space-y-2">
              <Label for="client">Client</Label>
              <Select v-model="form.client_id">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un client (optionnel)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="client in clients"
                    :key="client.id"
                    :value="client.id.toString()"
                  >
                    {{ client.nom }} - {{ client.email }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.client_id" />
            </div>

            <!-- Lieu d'intervention -->
            <div class="space-y-2">
              <Label for="lieu_intervention">Lieu d'intervention</Label>
              <Input
                id="lieu_intervention"
                v-model="form.lieu_intervention"
                placeholder="Adresse ou lieu de l'intervention"
              />
              <InputError :message="form.errors.lieu_intervention" />
            </div>

            <!-- Date souhaitée -->
            <div class="space-y-2">
              <Label for="date_souhaite_intervention">Date souhaitée</Label>
              <Input
                id="date_souhaite_intervention"
                v-model="form.date_souhaite_intervention"
                type="datetime-local"
                :min="new Date().toISOString().slice(0, 16)"
              />
              <InputError :message="form.errors.date_souhaite_intervention" />
            </div>

            <!-- Assignation directe (optionnelle) -->
            <div class="space-y-2">
              <Label for="receveur">Assigner directement à</Label>
              <Select v-model="form.receveur_id">
                <SelectTrigger>
                  <SelectValue placeholder="Laisser vide pour assignation ultérieure" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id.toString()"
                  >
                    {{ user.name }} - {{ user.email }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.receveur_id" />
            </div>

            <!-- Boutons -->
            <div class="flex justify-end gap-3 pt-6">
              <Button
                type="button"
                variant="outline"
                as="link"
                :href="route('demandes.index')"
              >
                Annuler
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
                class="min-w-[120px]"
              >
                <span v-if="form.processing">Création...</span>
                <span v-else>Créer la demande</span>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>