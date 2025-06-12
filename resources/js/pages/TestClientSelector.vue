<script setup lang="ts">
import { ref } from 'vue'
import ClientSelectorDual from '@/components/ClientSelectorDual.vue'
import type { Client } from '@/types/client'

const selectedClient = ref<Client | null>(null)

const onClientSelected = (client: Client | null) => {
  console.log('Client sélectionné:', client)
  selectedClient.value = client
}

const onAddClient = (clientData: { nom: string }) => {
  console.log('Ajouter nouveau client:', clientData)
  alert(`Fonctionnalité pour ajouter: ${clientData.nom}`)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
      <h1 class="text-2xl font-bold text-gray-900 mb-8">
        Test - Sélecteur Client Dual
      </h1>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Sélection du client
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

        <!-- Debug Info -->
        <div v-if="selectedClient" class="mt-6 p-4 bg-gray-100 rounded-md">
          <h3 class="font-medium text-gray-900 mb-2">Client sélectionné (debug):</h3>
          <pre class="text-sm text-gray-600">{{ JSON.stringify(selectedClient, null, 2) }}</pre>
        </div>

        <!-- Formulaire d'adresse de test -->
        <div v-if="selectedClient" class="mt-6 space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Adresse de facturation</h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Adresse
              </label>
              <input
                :value="selectedClient.adresse"
                type="text"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Complément d'adresse
              </label>
              <input
                :value="selectedClient.complement_adresse || ''"
                type="text"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Code postal
              </label>
              <input
                :value="selectedClient.code_postal"
                type="text"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Ville
              </label>
              <input
                :value="selectedClient.ville"
                type="text"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>