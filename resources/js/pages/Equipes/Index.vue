<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Search, Plus, Eye, Edit, Trash2, UsersRound, Filter, User } from 'lucide-vue-next'

interface Equipe {
  id: number
  nom: string
  code: string
  description?: string
  specialisation: string
  capacite_max: number
  active: boolean
  horaire_debut: string
  horaire_fin: string
  effectif_actuel: number
  taux_occupation: number
  est_complete: boolean
  charge_projet?: {
    nom: string
    prenom: string
  }
  employes_actifs?: Array<{
    nom: string
    prenom: string
    pivot: {
      role_equipe: string
    }
  }>
  chef_equipe?: Array<{
    nom: string
    prenom: string
  }>
}

interface Props {
  equipes: {
    data: Equipe[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
    specialisation?: string
    active?: string
  }
  specialisations: Record<string, string>
  statistiques: {
    total: number
    actives: number
    completes: number
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const specialisation = ref(props.filters.specialisation || '')
const active = ref(props.filters.active || '')

const applyFilters = () => {
  router.get(route('equipes.index'), {
    search: search.value,
    specialisation: specialisation.value,
    active: active.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  specialisation.value = ''
  active.value = ''
  applyFilters()
}

const deleteEquipe = (equipe: Equipe) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer l'équipe "${equipe.nom}" ?`)) {
    router.delete(route('equipes.destroy', equipe.id))
  }
}

const getSpecialisationColor = (spec: string) => {
  const colors: Record<string, string> = {
    'installation_generale': 'bg-blue-100 text-blue-800',
    'maintenance': 'bg-green-100 text-green-800',
    'depannage_urgence': 'bg-red-100 text-red-800',
    'industriel': 'bg-purple-100 text-purple-800',
    'tertiaire': 'bg-indigo-100 text-indigo-800',
    'particulier': 'bg-yellow-100 text-yellow-800',
    'eclairage_public': 'bg-orange-100 text-orange-800'
  }
  return colors[spec] || 'bg-gray-100 text-gray-800'
}

const getStatutEquipe = (equipe: Equipe) => {
  if (!equipe.active) return 'Inactive'
  const ratio = equipe.effectif_actuel / equipe.capacite_max
  if (ratio === 0) return 'Vide'
  if (ratio < 0.5) return 'Sous-effectif'
  if (ratio < 1) return 'Partielle'
  if (ratio === 1) return 'Complète'
  return 'Sur-effectif'
}

const getStatutColor = (equipe: Equipe) => {
  const statut = getStatutEquipe(equipe)
  const colors: Record<string, string> = {
    'Inactive': 'bg-gray-100 text-gray-800',
    'Vide': 'bg-red-100 text-red-800',
    'Sous-effectif': 'bg-orange-100 text-orange-800',
    'Partielle': 'bg-yellow-100 text-yellow-800',
    'Complète': 'bg-green-100 text-green-800',
    'Sur-effectif': 'bg-purple-100 text-purple-800'
  }
  return colors[statut] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <AppLayout>
    <Head title="Équipes" />
    
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-4 md:space-y-6 lg:space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="bg-blue-100 p-2 rounded-lg">
              <UsersRound class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">👥 Équipes</h1>
              <p class="text-sm text-gray-500">Gestion des équipes électriques</p>
            </div>
          </div>
          <Link
            :href="route('equipes.create')"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <Plus class="w-5 h-5 mr-2" />
            Nouvelle Équipe
          </Link>
        </div>

        <!-- Messages flash -->
        <div v-if="$page.props.flash?.success" class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          {{ $page.props.flash.error }}
        </div>
        
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ statistiques.total }}</div>
            <div class="text-sm text-gray-600">Total équipes</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ statistiques.actives }}</div>
            <div class="text-sm text-gray-600">Équipes actives</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ statistiques.completes }}</div>
            <div class="text-sm text-gray-600">Équipes complètes</div>
          </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
              <div class="relative">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Nom, code, description..."
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  @keyup.enter="applyFilters"
                />
                <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Spécialisation</label>
              <select
                v-model="specialisation"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Toutes</option>
                <option v-for="(label, value) in specialisations" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select
                v-model="active"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Toutes</option>
                <option value="true">Actives</option>
                <option value="false">Inactives</option>
              </select>
            </div>
            
            <div class="flex flex-col justify-end gap-2">
              <button
                @click="applyFilters"
                class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors flex items-center justify-center"
              >
                <Filter class="w-4 h-4 mr-2" />
                Filtrer
              </button>
              <button
                @click="resetFilters"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Réinitialiser
              </button>
            </div>
          </div>
        </div>

        <!-- Équipes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Version mobile : Cartes -->
          <div class="block lg:hidden divide-y divide-gray-200">
            <div v-for="equipe in equipes.data" :key="equipe.id" class="p-4 hover:bg-gray-50">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <div class="font-medium text-gray-900 flex items-center gap-2">
                    {{ equipe.nom }}
                    <span class="text-sm text-gray-500">({{ equipe.code }})</span>
                  </div>
                  <div class="text-sm text-gray-500 mt-1">
                    {{ equipe.horaire_debut }} - {{ equipe.horaire_fin }}
                  </div>
                </div>
                <div class="flex gap-2">
                  <Link
                    :href="route('equipes.show', equipe.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Voir"
                  >
                    <Eye class="w-4 h-4" />
                  </Link>
                  <Link
                    :href="route('equipes.edit', equipe.id)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Modifier"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="deleteEquipe(equipe)"
                    class="text-red-600 hover:text-red-900"
                    title="Supprimer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getSpecialisationColor(equipe.specialisation)]">
                    {{ specialisations[equipe.specialisation] }}
                  </span>
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(equipe)]">
                    {{ getStatutEquipe(equipe) }}
                  </span>
                </div>
                <div class="text-sm text-gray-600">
                  Effectif: {{ equipe.effectif_actuel }}/{{ equipe.capacite_max }} 
                  ({{ equipe.taux_occupation }}%)
                </div>
                <div v-if="equipe.charge_projet" class="text-sm text-gray-500">
                  Chargé: {{ equipe.charge_projet.prenom }} {{ equipe.charge_projet.nom }}
                </div>
                <div v-if="equipe.chef_equipe?.length" class="text-sm text-gray-500">
                  Chef: {{ equipe.chef_equipe[0].prenom }} {{ equipe.chef_equipe[0].nom }}
                </div>
              </div>
            </div>
            
            <div v-if="equipes.data.length === 0" class="p-6 text-center text-gray-500">
              Aucune équipe trouvée
            </div>
          </div>
          
          <!-- Version desktop : Tableau -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Équipe
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Spécialisation
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Effectif
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Horaires
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Responsable
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="equipe in equipes.data" :key="equipe.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <UsersRound class="w-5 h-5 text-blue-600" />
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ equipe.nom }}</div>
                        <div class="text-sm text-gray-500">{{ equipe.code }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getSpecialisationColor(equipe.specialisation)]">
                      {{ specialisations[equipe.specialisation] }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm text-gray-900">{{ equipe.effectif_actuel }}/{{ equipe.capacite_max }}</div>
                      <div class="ml-2">
                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(equipe)]">
                          {{ equipe.taux_occupation }}%
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ equipe.horaire_debut }} - {{ equipe.horaire_fin }}
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="equipe.charge_projet" class="text-sm text-gray-900">
                      {{ equipe.charge_projet.prenom }} {{ equipe.charge_projet.nom }}
                      <div v-if="equipe.chef_equipe?.length" class="text-xs text-gray-500">
                        Chef: {{ equipe.chef_equipe[0].prenom }} {{ equipe.chef_equipe[0].nom }}
                      </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">-</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('equipes.show', equipe.id)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Voir"
                      >
                        <Eye class="w-5 h-5" />
                      </Link>
                      <Link
                        :href="route('equipes.edit', equipe.id)"
                        class="text-gray-600 hover:text-gray-900"
                        title="Modifier"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="deleteEquipe(equipe)"
                        class="text-red-600 hover:text-red-900"
                        title="Supprimer"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
                
                <tr v-if="equipes.data.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Aucune équipe trouvée
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="equipes.last_page > 1" class="bg-gray-50 px-4 md:px-6 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="text-sm text-gray-700 text-center sm:text-left">
                Affichage de {{ (equipes.current_page - 1) * equipes.per_page + 1 }} à 
                {{ Math.min(equipes.current_page * equipes.per_page, equipes.total) }} sur 
                {{ equipes.total }} résultats
              </div>
              
              <div class="flex gap-1 justify-center">
                <Link
                  v-for="page in equipes.last_page"
                  :key="page"
                  :href="`${route('equipes.index')}?page=${page}`"
                  :class="[
                    'px-3 py-1 rounded text-sm',
                    page === equipes.current_page
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