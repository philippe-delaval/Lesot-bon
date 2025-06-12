<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Search, Plus, Eye, Edit, Trash2, Users } from 'lucide-vue-next'

interface Client {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string
  code_postal: string
  ville: string
  telephone?: string
  created_at: string
}

interface Props {
  clients: {
    data: Client[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')

const applyFilters = () => {
  router.get(route('clients.index'), {
    search: search.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  applyFilters()
}

const deleteClient = (client: Client) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer le client "${client.nom}" ?`)) {
    router.delete(route('clients.destroy', client.id))
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Clients" />
    
    <div class="flex flex-1 flex-col gap-4 p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Users class="h-8 w-8 text-blue-600" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Clients</h1>
            <p class="text-sm text-gray-500">Gestion de la base clients</p>
          </div>
        </div>
        <Link
          :href="route('clients.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          <Plus class="w-5 h-5 mr-2" />
          Nouveau Client
        </Link>
      </div>

      <!-- Messages flash -->
      <div v-if="$page.props.flash?.success" class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        {{ $page.props.flash.success }}
      </div>
      
      <!-- Filtres -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="Nom, email, ville..."
                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keyup.enter="applyFilters"
              />
              <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
            </div>
          </div>
          
          <div class="flex items-end gap-2">
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
            >
              Filtrer
            </button>
            <button
              @click="resetFilters"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
            >
              Réinitialiser
            </button>
          </div>
        </div>
      </div>
      
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <Users class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total clients</p>
              <p class="text-2xl font-bold text-gray-900">{{ clients.total }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Client
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contact
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Adresse
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ client.nom }}</div>
                    <div class="text-sm text-gray-500">ID: {{ client.id }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm text-gray-900">{{ client.email }}</div>
                    <div v-if="client.telephone" class="text-sm text-gray-500">{{ client.telephone }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ client.adresse }}</div>
                  <div v-if="client.complement_adresse" class="text-sm text-gray-500">{{ client.complement_adresse }}</div>
                  <div class="text-sm text-gray-500">{{ client.code_postal }} {{ client.ville }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('clients.show', client.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Voir"
                    >
                      <Eye class="w-5 h-5" />
                    </Link>
                    <Link
                      :href="route('clients.edit', client.id)"
                      class="text-gray-600 hover:text-gray-900"
                      title="Modifier"
                    >
                      <Edit class="w-5 h-5" />
                    </Link>
                    <button
                      @click="deleteClient(client)"
                      class="text-red-600 hover:text-red-900"
                      title="Supprimer"
                    >
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
              
              <tr v-if="clients.data.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                  Aucun client trouvé
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="clients.last_page > 1" class="bg-gray-50 px-6 py-3 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Affichage de {{ (clients.current_page - 1) * clients.per_page + 1 }} à 
            {{ Math.min(clients.current_page * clients.per_page, clients.total) }} sur 
            {{ clients.total }} résultats
          </div>
          
          <div class="flex gap-2">
            <Link
              v-for="page in clients.last_page"
              :key="page"
              :href="`${route('clients.index')}?page=${page}`"
              :class="[
                'px-3 py-1 rounded',
                page === clients.current_page
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-100'
              ]"
              preserve-state
            >
              {{ page }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>