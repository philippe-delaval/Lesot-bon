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
    
    <!-- Container responsive avec marges mobile-first -->
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-4 md:space-y-6 lg:space-y-8">
        <!-- Header responsive -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="bg-blue-100 p-2 rounded-lg">
              <Users class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">Clients</h1>
              <p class="text-sm text-gray-500">Gestion de la base clients</p>
            </div>
          </div>
          <Link
            :href="route('clients.create')"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <Plus class="w-5 h-5 mr-2" />
            Nouveau Client
          </Link>
        </div>

        <!-- Messages flash -->
        <div v-if="$page.props.flash?.success" class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          {{ $page.props.flash.success }}
        </div>
        
        <!-- Barre de recherche et statistiques responsive -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
          <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
              <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                  <div class="relative">
                    <input
                      v-model="search"
                      type="text"
                      placeholder="Nom, email, ville..."
                      class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      @keyup.enter="applyFilters"
                    />
                    <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                  </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-end gap-2">
                  <button
                    @click="applyFilters"
                    class="w-full sm:w-auto px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors"
                  >
                    Filtrer
                  </button>
                  <button
                    @click="resetFilters"
                    class="w-full sm:w-auto px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    Réinitialiser
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Statistique responsive -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ clients.total }}</div>
            <div class="text-sm text-gray-600">Total clients</div>
          </div>
        </div>

        <!-- Clients responsive -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Version mobile : Cartes -->
          <div class="block lg:hidden divide-y divide-gray-200">
            <div v-for="client in clients.data" :key="client.id" class="p-4 hover:bg-gray-50">
              <div class="flex justify-between items-start mb-2">
                <div class="font-medium text-gray-900">{{ client.nom }}</div>
                <div class="flex gap-2">
                  <Link
                    :href="route('clients.show', client.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Voir"
                  >
                    <Eye class="w-4 h-4" />
                  </Link>
                  <Link
                    :href="route('clients.edit', client.id)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Modifier"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="deleteClient(client)"
                    class="text-red-600 hover:text-red-900"
                    title="Supprimer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div class="space-y-1">
                <div class="text-sm text-gray-600">{{ client.email }}</div>
                <div v-if="client.telephone" class="text-sm text-gray-600">{{ client.telephone }}</div>
                <div class="text-sm text-gray-500">{{ client.adresse }}</div>
                <div v-if="client.complement_adresse" class="text-sm text-gray-500">{{ client.complement_adresse }}</div>
                <div class="text-sm text-gray-500">{{ client.code_postal }} {{ client.ville }}</div>
              </div>
            </div>
            
            <div v-if="clients.data.length === 0" class="p-6 text-center text-gray-500">
              Aucun client trouvé
            </div>
          </div>
          
          <!-- Version desktop : Tableau -->
          <div class="hidden lg:block overflow-x-auto">
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
          
          <!-- Pagination responsive -->
          <div v-if="clients.last_page > 1" class="bg-gray-50 px-4 md:px-6 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="text-sm text-gray-700 text-center sm:text-left">
                Affichage de {{ (clients.current_page - 1) * clients.per_page + 1 }} à 
                {{ Math.min(clients.current_page * clients.per_page, clients.total) }} sur 
                {{ clients.total }} résultats
              </div>
              
              <div class="flex gap-1 justify-center">
                <Link
                  v-for="page in clients.last_page"
                  :key="page"
                  :href="`${route('clients.index')}?page=${page}`"
                  :class="[
                    'px-3 py-1 rounded text-sm',
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
      </div>
    </div>
  </AppLayout>
</template>