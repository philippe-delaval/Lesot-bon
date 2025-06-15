<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Search, Plus, Eye, Edit, Trash2, User, Shield, Users, Calendar, Filter } from 'lucide-vue-next'

interface Employe {
  id: number
  nom: string
  prenom: string
  email: string
  telephone?: string
  matricule: string
  statut: string
  type_contrat: string
  role_hierarchique: string
  habilitations_electriques?: string[]
  disponibilite: string
  nom_complet: string
  initiales: string
  niveau_experience: string
  charge_projet?: {
    nom: string
    prenom: string
  }
  gestionnaire?: {
    nom: string
    prenom: string
  }
  equipe_active?: Array<{
    nom: string
    code: string
  }>
}

interface Props {
  employes: {
    data: Employe[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
    statut?: string
    habilitation?: string
    disponibilite?: string
  }
  statistiques: {
    total: number
    permanents: number
    interimaires: number
    disponibles: number
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const statut = ref(props.filters.statut || '')
const habilitation = ref(props.filters.habilitation || '')
const disponibilite = ref(props.filters.disponibilite || '')

const applyFilters = () => {
  router.get(route('employes.index'), {
    search: search.value,
    statut: statut.value,
    habilitation: habilitation.value,
    disponibilite: disponibilite.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  statut.value = ''
  habilitation.value = ''
  disponibilite.value = ''
  applyFilters()
}

const deleteEmploye = (employe: Employe) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer l'employé "${employe.nom_complet}" ?`)) {
    router.delete(route('employes.destroy', employe.id))
  }
}

const getStatutColor = (statut: string) => {
  return statut === 'permanent' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-blue-100 text-blue-800'
}

const getDisponibiliteColor = (disponibilite: string) => {
  switch (disponibilite) {
    case 'disponible': return 'bg-green-100 text-green-800'
    case 'indisponible': return 'bg-red-100 text-red-800'
    case 'conge': return 'bg-yellow-100 text-yellow-800'
    case 'arret_maladie': return 'bg-red-100 text-red-800'
    case 'formation': return 'bg-purple-100 text-purple-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatHabilitations = (habilitations?: string[]) => {
  if (!habilitations || habilitations.length === 0) return 'Aucune'
  return habilitations.join(', ')
}
</script>

<template>
  <AppLayout>
    <Head title="Employés" />
    
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-4 md:space-y-6 lg:space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="bg-blue-100 p-2 rounded-lg">
              <User class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">Employés</h1>
              <p class="text-sm text-gray-500">Gestion du personnel électrique</p>
            </div>
          </div>
          <Link
            :href="route('employes.create')"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <Plus class="w-5 h-5 mr-2" />
            Nouvel Employé
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ statistiques.total }}</div>
            <div class="text-sm text-gray-600">Total employés</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ statistiques.permanents }}</div>
            <div class="text-sm text-gray-600">Permanents</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ statistiques.interimaires }}</div>
            <div class="text-sm text-gray-600">Intérimaires</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ statistiques.disponibles }}</div>
            <div class="text-sm text-gray-600">Disponibles</div>
          </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
              <div class="relative">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Nom, prénom, email, matricule..."
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  @keyup.enter="applyFilters"
                />
                <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select
                v-model="statut"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Tous</option>
                <option value="permanent">Permanent</option>
                <option value="interimaire">Intérimaire</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Disponibilité</label>
              <select
                v-model="disponibilite"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Toutes</option>
                <option value="disponible">Disponible</option>
                <option value="indisponible">Indisponible</option>
                <option value="conge">Congé</option>
                <option value="formation">Formation</option>
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

        <!-- Employés -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Version mobile : Cartes -->
          <div class="block lg:hidden divide-y divide-gray-200">
            <div v-for="employe in employes.data" :key="employe.id" class="p-4 hover:bg-gray-50">
              <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-medium text-sm">{{ employe.initiales }}</span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ employe.nom_complet }}</div>
                    <div class="text-sm text-gray-500">{{ employe.matricule }}</div>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Link
                    :href="route('employes.show', employe.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Voir"
                  >
                    <Eye class="w-4 h-4" />
                  </Link>
                  <Link
                    :href="route('employes.edit', employe.id)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Modifier"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="deleteEmploye(employe)"
                    class="text-red-600 hover:text-red-900"
                    title="Supprimer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(employe.statut)]">
                    {{ employe.statut }}
                  </span>
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getDisponibiliteColor(employe.disponibilite)]">
                    {{ employe.disponibilite }}
                  </span>
                </div>
                <div class="text-sm text-gray-600">{{ employe.email }}</div>
                <div v-if="employe.habilitations_electriques?.length" class="text-sm text-gray-500">
                  Habilitations: {{ formatHabilitations(employe.habilitations_electriques) }}
                </div>
                <div v-if="employe.equipe_active?.length" class="text-sm text-gray-500">
                  Équipe: {{ employe.equipe_active[0].nom }}
                </div>
              </div>
            </div>
            
            <div v-if="employes.data.length === 0" class="p-6 text-center text-gray-500">
              Aucun employé trouvé
            </div>
          </div>
          
          <!-- Version desktop : Tableau -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employé
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Habilitations
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Équipe
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="employe in employes.data" :key="employe.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-medium text-sm">{{ employe.initiales }}</span>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ employe.nom_complet }}</div>
                        <div class="text-sm text-gray-500">{{ employe.matricule }} • {{ employe.niveau_experience }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm text-gray-900">{{ employe.email }}</div>
                      <div v-if="employe.telephone" class="text-sm text-gray-500">{{ employe.telephone }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="space-y-1">
                      <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(employe.statut)]">
                        {{ employe.statut }}
                      </span>
                      <br>
                      <span :class="['px-2 py-1 rounded-full text-xs font-medium', getDisponibiliteColor(employe.disponibilite)]">
                        {{ employe.disponibilite }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ formatHabilitations(employe.habilitations_electriques) }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="employe.equipe_active?.length" class="text-sm text-gray-900">
                      {{ employe.equipe_active[0].nom }}
                      <div class="text-xs text-gray-500">{{ employe.equipe_active[0].code }}</div>
                    </div>
                    <div v-else class="text-sm text-gray-500">-</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('employes.show', employe.id)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Voir"
                      >
                        <Eye class="w-5 h-5" />
                      </Link>
                      <Link
                        :href="route('employes.edit', employe.id)"
                        class="text-gray-600 hover:text-gray-900"
                        title="Modifier"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="deleteEmploye(employe)"
                        class="text-red-600 hover:text-red-900"
                        title="Supprimer"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
                
                <tr v-if="employes.data.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Aucun employé trouvé
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="employes.last_page > 1" class="bg-gray-50 px-4 md:px-6 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="text-sm text-gray-700 text-center sm:text-left">
                Affichage de {{ (employes.current_page - 1) * employes.per_page + 1 }} à 
                {{ Math.min(employes.current_page * employes.per_page, employes.total) }} sur 
                {{ employes.total }} résultats
              </div>
              
              <div class="flex gap-1 justify-center">
                <Link
                  v-for="page in employes.last_page"
                  :key="page"
                  :href="`${route('employes.index')}?page=${page}`"
                  :class="[
                    'px-3 py-1 rounded text-sm',
                    page === employes.current_page
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