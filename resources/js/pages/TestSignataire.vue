<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ClientSelectorDual from '@/components/ClientSelectorDual.vue'
import type { Client } from '@/types/client'
import type { AttachementFormData } from '@/types/attachement'

const selectedClient = ref<Client | null>(null)

// Form minimal pour tester la logique de nom_signataire_client
const form = useForm({
  client_id: null as number | null,
  client_nom: '',
  nom_signataire_client: '',
  client_email: '',
} as Partial<AttachementFormData>)

const onClientSelected = (client: Client | null) => {
  selectedClient.value = client
  if (client) {
    form.client_id = client.id
    form.client_nom = client.nom
    form.client_email = client.email
    
    // Ne pas pré-remplir le nom du signataire - laissé vide pour saisie manuelle
  } else {
    form.client_id = null
    form.client_nom = ''
    form.client_email = ''
    // Ne pas vider nom_signataire_client pour préserver la saisie utilisateur
  }
}

const onAddClient = (clientData: { nom: string }) => {
  console.log('Ajouter nouveau client:', clientData)
  alert(`Fonctionnalité pour ajouter: ${clientData.nom}`)
}

const clearSignataire = () => {
  form.nom_signataire_client = ''
}

const resetForm = () => {
  selectedClient.value = null
  form.reset()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <h1 class="text-2xl font-bold text-gray-900 mb-8">
        Test - Champ Nom Signataire Modifiable
      </h1>

      <div class="bg-white rounded-lg shadow p-6 space-y-8">
        <!-- Section Client -->
        <div>
          <h2 class="text-lg font-medium text-gray-900 mb-4">
            1. Sélection du client
          </h2>
          
          <ClientSelectorDual
            :model-value="selectedClient"
            @update:model-value="onClientSelected"
            @client-selected="onClientSelected"
            @add-client="onAddClient"
            :options="{
              showCreateButton: true,
              placeholder: 'Choisir un client dans la liste',
              searchPlaceholder: 'Rechercher un client existant...',
              maxResults: 8
            }"
          />
        </div>

        <!-- Section Informations -->
        <div v-if="selectedClient" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-md font-medium text-gray-900 mb-4">Informations client</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nom du client (référence)
                </label>
                <input
                  v-model="form.client_nom"
                  type="text"
                  readonly
                  class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Email
                </label>
                <input
                  v-model="form.client_email"
                  type="email"
                  readonly
                  class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                />
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-md font-medium text-gray-900 mb-4">Section SIGNATURES</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Nom de la personne qui signe <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.nom_signataire_client"
                  type="text"
                  required
                  placeholder="Nom de la personne qui signe"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                />
                <p class="mt-1 text-xs text-gray-500">
                  ✨ Champ toujours vide - Saisie manuelle obligatoire - Cette personne peut être différente du client principal
                </p>
              </div>
              
              <div class="flex space-x-2">
                <button
                  type="button"
                  @click="clearSignataire"
                  class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                >
                  Vider le signataire
                </button>
                <button
                  type="button"
                  @click="resetForm"
                  class="px-3 py-1 text-sm bg-red-200 text-red-700 rounded-md hover:bg-red-300 transition-colors"
                >
                  Reset complet
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Debug Info -->
        <div class="bg-gray-100 rounded-md p-4">
          <h3 class="font-medium text-gray-900 mb-2">Debug - État du formulaire :</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <strong>Client sélectionné :</strong>
              <pre class="mt-1 text-xs bg-white p-2 rounded overflow-auto">{{ selectedClient ? selectedClient.nom : 'Aucun' }}</pre>
            </div>
            <div>
              <strong>Nom signataire :</strong>
              <pre class="mt-1 text-xs bg-white p-2 rounded overflow-auto">{{ form.nom_signataire_client || 'Vide' }}</pre>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <h3 class="font-medium text-blue-900 mb-2">🧪 Instructions de test :</h3>
          <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
            <li><strong>Sélectionner un client</strong> → Le nom du signataire reste VIDE (pas d'auto-complétion)</li>
            <li><strong>Saisir manuellement le nom du signataire</strong> → Le champ accepte la saisie</li>
            <li><strong>Changer de client</strong> → Le nom du signataire ne change PAS (préservé)</li>
            <li><strong>Vider le signataire</strong> → Le champ redevient vide</li>
            <li><strong>Reset complet</strong> → Tout se vide pour recommencer</li>
          </ol>
        </div>

        <!-- Résultats attendus -->
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
          <h3 class="font-medium text-green-900 mb-2">✅ Comportement attendu :</h3>
          <ul class="text-sm text-green-800 space-y-1 list-disc list-inside">
            <li>Le champ "Nom de la personne qui signe" est toujours <strong>modifiable</strong></li>
            <li>Il ne se pré-remplit <strong>JAMAIS automatiquement</strong> lors de la sélection client</li>
            <li>La saisie manuelle est <strong>obligatoire</strong> pour chaque attachement</li>
            <li>La modification manuelle est <strong>préservée</strong> même si on change de client</li>
            <li>Interface intuitive avec placeholder et aide contextuelle</li>
            <li>Validation côté client (champ obligatoire)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
pre {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 11px;
  max-height: 100px;
}
</style>