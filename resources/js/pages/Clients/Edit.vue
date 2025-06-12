<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { Users, ArrowLeft, Save } from 'lucide-vue-next'

interface Client {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string
  code_postal: string
  ville: string
  telephone?: string
  notes?: string
}

interface Props {
  client: Client
}

const props = defineProps<Props>()

const breadcrumbItems = [
  { title: 'Vue d\'ensemble', href: '/dashboard' },
  { title: 'Clients', href: '/clients' },
  { title: props.client.nom, href: `/clients/${props.client.id}` },
  { title: 'Modifier', current: true }
]

const form = useForm({
  nom: props.client.nom,
  email: props.client.email,
  adresse: props.client.adresse,
  complement_adresse: props.client.complement_adresse || '',
  code_postal: props.client.code_postal,
  ville: props.client.ville,
  telephone: props.client.telephone || '',
  notes: props.client.notes || '',
})

const submit = () => {
  form.put(route('clients.update', props.client.id))
}
</script>

<template>
  <AppLayout>
    <Head :title="`Modifier - ${client.nom}`" />
    
    <div class="flex flex-1 flex-col gap-6 p-4">
      <!-- Breadcrumb -->
      <Breadcrumbs :breadcrumbs="breadcrumbItems" />
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Users class="h-8 w-8 text-blue-600" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Modifier le client</h1>
            <p class="text-sm text-gray-500">{{ client.nom }}</p>
          </div>
        </div>
        
        <Link
          :href="route('clients.show', client.id)"
          class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Retour
        </Link>
      </div>

      <!-- Formulaire -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Section Informations principales -->
          <div class="border-b border-gray-200 pb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations principales</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nom du client <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.nom"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Nom complet du client"
                />
                <div v-if="form.errors.nom" class="mt-1 text-sm text-red-600">{{ form.errors.nom }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Adresse email <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="email@exemple.com"
                />
                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Téléphone
                </label>
                <input
                  v-model="form.telephone"
                  type="tel"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="03 21 215 200"
                />
                <div v-if="form.errors.telephone" class="mt-1 text-sm text-red-600">{{ form.errors.telephone }}</div>
              </div>
            </div>
          </div>

          <!-- Section Adresse -->
          <div class="border-b border-gray-200 pb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Adresse</h2>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Adresse <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.adresse"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Numéro et nom de rue"
                />
                <div v-if="form.errors.adresse" class="mt-1 text-sm text-red-600">{{ form.errors.adresse }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Complément d'adresse
                </label>
                <input
                  v-model="form.complement_adresse"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Bâtiment, étage, appartement..."
                />
                <div v-if="form.errors.complement_adresse" class="mt-1 text-sm text-red-600">{{ form.errors.complement_adresse }}</div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Code postal <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.code_postal"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="62000"
                  />
                  <div v-if="form.errors.code_postal" class="mt-1 text-sm text-red-600">{{ form.errors.code_postal }}</div>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ville <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.ville"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Arras"
                  />
                  <div v-if="form.errors.ville" class="mt-1 text-sm text-red-600">{{ form.errors.ville }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section Notes -->
          <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notes privées
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Informations supplémentaires, historique, préférences..."
              ></textarea>
              <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <Link
              :href="route('clients.show', client.id)"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
            >
              Annuler
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium"
            >
              <Save class="w-4 h-4 mr-2" />
              <span v-if="form.processing">Modification...</span>
              <span v-else>Sauvegarder</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>