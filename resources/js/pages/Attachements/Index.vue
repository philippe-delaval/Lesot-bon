<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/core'
import { ref, onMounted } from 'vue'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import AppShell from '@/components/AppShell.vue'
import { Search, Download, Eye, Plus, Calendar } from 'lucide-vue-next'

// Vérifier l'authentification
onMounted(() => {
  const page = usePage()
  if (!page.props.auth?.user) {
    // Rediriger vers login si non connecté
    router.visit('/login')
  }
})

interface Attachement {
  id: number
  numero_dossier: string
  client_nom: string
  lieu_intervention: string
  date_intervention: string
  temps_total_passe: number
  created_at: string
  creator?: {
    name: string
  }
}

interface Props {
  attachements: {
    data: Attachement[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
    date_from?: string
    date_to?: string
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')

const applyFilters = () => {
  router.get('/attachements', {
    search: search.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  applyFilters()
}

const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: fr })
}

const formatDateTime = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy HH:mm', { locale: fr })
}
</script>

<template>
  <AppShell>
    <Head title="Attachements de Travaux" />
    
    <div class="container mx-auto py-6 px-4">
      <!-- Messages flash -->
      <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        {{ $page.props.flash.success }}
      </div>
      
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Attachements de Travaux</h1>
        <Link
          :href="route('attachements.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <Plus class="w-5 h-5 mr-2" />
          Nouvel Attachement
        </Link>
      </div>
      
      <!-- Filtres -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="N° dossier, client, lieu..."
                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keyup.enter="applyFilters"
              />
              <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
            <input
              v-model="dateFrom"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
            <input
              v-model="dateTo"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
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
      
      <!-- Tableau -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  N° Dossier
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Client
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lieu d'intervention
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date intervention
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Temps (h)
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Créé le
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="attachement in attachements.data" :key="attachement.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ attachement.numero_dossier }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ attachement.client_nom }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ attachement.lieu_intervention }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center">
                    <Calendar class="w-4 h-4 mr-1 text-gray-400" />
                    {{ formatDate(attachement.date_intervention) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ attachement.temps_total_passe }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div>
                    {{ formatDateTime(attachement.created_at) }}
                  </div>
                  <div v-if="attachement.creator" class="text-xs text-gray-400">
                    par {{ attachement.creator.name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('attachements.show', attachement.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Voir"
                    >
                      <Eye class="w-5 h-5" />
                    </Link>
                    <Link
                      :href="route('attachements.download-pdf', attachement.id)"
                      class="text-green-600 hover:text-green-900"
                      title="Télécharger PDF"
                    >
                      <Download class="w-5 h-5" />
                    </Link>
                  </div>
                </td>
              </tr>
              
              <tr v-if="attachements.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  Aucun attachement trouvé
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="attachements.last_page > 1" class="bg-gray-50 px-6 py-3 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Affichage de {{ (attachements.current_page - 1) * attachements.per_page + 1 }} à 
            {{ Math.min(attachements.current_page * attachements.per_page, attachements.total) }} sur 
            {{ attachements.total }} résultats
          </div>
          
          <div class="flex gap-2">
            <Link
              v-for="page in attachements.last_page"
              :key="page"
              :href="`/attachements?page=${page}`"
              :class="[
                'px-3 py-1 rounded',
                page === attachements.current_page
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
  </AppShell>
</template> 