<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Calendar, Plus, Eye, Edit, Trash2, Filter, Clock, User, AlertTriangle } from 'lucide-vue-next'

interface Planning {
  id: number
  date_debut: string
  date_fin: string
  heure_debut_prevue?: string
  heure_fin_prevue?: string
  type_affectation: string
  statut: string
  lieu_intervention?: string
  description_tache?: string
  employe: {
    nom: string
    prenom: string
    nom_complet: string
  }
  demande?: {
    titre: string
    client: {
      nom: string
    }
  }
  equipe?: {
    nom: string
    code: string
  }
}

interface Props {
  plannings: {
    data: Planning[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  employes: Array<{
    id: number
    nom: string
    prenom: string
  }>
  equipes: Array<{
    id: number
    nom: string
    code: string
  }>
  filters: {
    vue?: string
    date_debut?: string
    date_fin?: string
    employe?: string
    equipe?: string
    statut?: string
    type?: string
  }
  statistiques: {
    total_planifie: number
    en_cours: number
    termines_aujourdhui: number
    en_retard: number
  }
}

const props = defineProps<Props>()

const vue = ref(props.filters.vue || 'liste')
const dateDebut = ref(props.filters.date_debut || '')
const dateFin = ref(props.filters.date_fin || '')
const employe = ref(props.filters.employe || '')
const equipe = ref(props.filters.equipe || '')
const statut = ref(props.filters.statut || '')
const type = ref(props.filters.type || '')

const applyFilters = () => {
  router.get(route('planning.index'), {
    vue: vue.value,
    date_debut: dateDebut.value,
    date_fin: dateFin.value,
    employe: employe.value,
    equipe: equipe.value,
    statut: statut.value,
    type: type.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  dateDebut.value = ''
  dateFin.value = ''
  employe.value = ''
  equipe.value = ''
  statut.value = ''
  type.value = ''
  applyFilters()
}

const deletePlanning = (planning: Planning) => {
  if (confirm(`Êtes-vous sûr de vouloir annuler cette planification ?`)) {
    router.delete(route('planning.destroy', planning.id))
  }
}

const getStatutColor = (statut: string) => {
  const colors: Record<string, string> = {
    'planifie': 'bg-blue-100 text-blue-800',
    'en_cours': 'bg-yellow-100 text-yellow-800',
    'termine': 'bg-green-100 text-green-800',
    'annule': 'bg-red-100 text-red-800',
    'reporte': 'bg-orange-100 text-orange-800',
    'en_attente': 'bg-gray-100 text-gray-800'
  }
  return colors[statut] || 'bg-gray-100 text-gray-800'
}

const getTypeColor = (typeAffectation: string) => {
  const colors: Record<string, string> = {
    'intervention': 'bg-blue-500',
    'maintenance': 'bg-green-500',
    'formation': 'bg-purple-500',
    'conge': 'bg-yellow-500',
    'arret_maladie': 'bg-red-500',
    'deplacement': 'bg-indigo-500',
    'administratif': 'bg-gray-500',
    'astreinte': 'bg-orange-500'
  }
  return colors[typeAffectation] || 'bg-gray-400'
}

const getTypeIcon = (typeAffectation: string) => {
  switch (typeAffectation) {
    case 'intervention': return '🔧'
    case 'maintenance': return '🛠️'
    case 'formation': return '📚'
    case 'conge': return '🏖️'
    case 'arret_maladie': return '🏥'
    case 'deplacement': return '🚗'
    case 'administratif': return '📋'
    case 'astreinte': return '📞'
    default: return '📅'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatHeure = (heure?: string) => {
  if (!heure) return '-'
  return heure.substring(0, 5)
}

const typesAffectation: Record<string, string> = {
  'intervention': 'Intervention',
  'maintenance': 'Maintenance',
  'formation': 'Formation',
  'conge': 'Congé',
  'arret_maladie': 'Arrêt maladie',
  'deplacement': 'Déplacement',
  'administratif': 'Administratif',
  'astreinte': 'Astreinte'
}

const statuts: Record<string, string> = {
  'planifie': 'Planifié',
  'en_cours': 'En cours',
  'termine': 'Terminé',
  'annule': 'Annulé',
  'reporte': 'Reporté',
  'en_attente': 'En attente'
}
</script>

<template>
  <AppLayout>
    <Head title="Planning" />
    
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-4 md:space-y-6 lg:space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="bg-blue-100 p-2 rounded-lg">
              <Calendar class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">📅 Planning</h1>
              <p class="text-sm text-gray-500">Planification des interventions et congés</p>
            </div>
          </div>
          <Link
            :href="route('planning.create')"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            <Plus class="w-5 h-5 mr-2" />
            Nouvelle Planification
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
            <div class="text-2xl font-bold text-blue-600">{{ statistiques.total_planifie }}</div>
            <div class="text-sm text-gray-600">Planifiés</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ statistiques.en_cours }}</div>
            <div class="text-sm text-gray-600">En cours</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ statistiques.termines_aujourdhui }}</div>
            <div class="text-sm text-gray-600">Terminés aujourd'hui</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-red-600 flex items-center justify-center gap-1">
              <AlertTriangle class="w-5 h-5" />
              {{ statistiques.en_retard }}
            </div>
            <div class="text-sm text-gray-600">En retard</div>
          </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
              <input
                v-model="dateDebut"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
              <input
                v-model="dateFin"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employé</label>
              <select
                v-model="employe"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Tous</option>
                <option v-for="emp in employes" :key="emp.id" :value="emp.id">
                  {{ emp.prenom }} {{ emp.nom }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Équipe</label>
              <select
                v-model="equipe"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Toutes</option>
                <option v-for="eq in equipes" :key="eq.id" :value="eq.id">
                  {{ eq.nom }} ({{ eq.code }})
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select
                v-model="statut"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Tous</option>
                <option v-for="(label, value) in statuts" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select
                v-model="type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Tous</option>
                <option v-for="(label, value) in typesAffectation" :key="value" :value="value">
                  {{ label }}
                </option>
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

        <!-- Planning -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Version mobile : Cartes -->
          <div class="block lg:hidden divide-y divide-gray-200">
            <div v-for="planning in plannings.data || []" :key="planning.id" class="p-4 hover:bg-gray-50">
              <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-3">
                  <div :class="['w-3 h-3 rounded-full', getTypeColor(planning.type_affectation)]"></div>
                  <div>
                    <div class="font-medium text-gray-900 flex items-center gap-2">
                      {{ getTypeIcon(planning.type_affectation) }}
                      {{ planning.demande?.titre || typesAffectation[planning.type_affectation] }}
                    </div>
                    <div class="text-sm text-gray-500">{{ planning.employe.nom_complet }}</div>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Link
                    :href="route('planning.show', planning.id)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Voir"
                  >
                    <Eye class="w-4 h-4" />
                  </Link>
                  <Link
                    :href="route('planning.edit', planning.id)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Modifier"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="deletePlanning(planning)"
                    class="text-red-600 hover:text-red-900"
                    title="Annuler"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <Clock class="w-4 h-4 text-gray-400" />
                  <span class="text-sm text-gray-600">
                    {{ formatDate(planning.date_debut) }} 
                    {{ formatHeure(planning.heure_debut_prevue) }}-{{ formatHeure(planning.heure_fin_prevue) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-2">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(planning.statut)]">
                    {{ statuts[planning.statut] }}
                  </span>
                </div>
                <div v-if="planning.lieu_intervention" class="text-sm text-gray-500">
                  📍 {{ planning.lieu_intervention }}
                </div>
                <div v-if="planning.demande?.client" class="text-sm text-gray-500">
                  Client: {{ planning.demande.client.nom }}
                </div>
              </div>
            </div>
            
            <div v-if="!plannings.data || plannings.data.length === 0" class="p-6 text-center text-gray-500">
              Aucune planification trouvée
            </div>
          </div>
          
          <!-- Version desktop : Tableau -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Planification
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employé
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date & Heure
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Lieu
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="planning in plannings.data || []" :key="planning.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div :class="['w-3 h-3 rounded-full mr-3', getTypeColor(planning.type_affectation)]"></div>
                      <div>
                        <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                          {{ getTypeIcon(planning.type_affectation) }}
                          {{ planning.demande?.titre || typesAffectation[planning.type_affectation] }}
                        </div>
                        <div v-if="planning.demande?.client" class="text-sm text-gray-500">
                          Client: {{ planning.demande.client.nom }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ planning.employe.nom_complet }}</div>
                    <div v-if="planning.equipe" class="text-sm text-gray-500">
                      Équipe: {{ planning.equipe.nom }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(planning.date_debut) }}</div>
                    <div class="text-sm text-gray-500">
                      {{ formatHeure(planning.heure_debut_prevue) }} - {{ formatHeure(planning.heure_fin_prevue) }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                      {{ planning.lieu_intervention || '-' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatutColor(planning.statut)]">
                      {{ statuts[planning.statut] }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('planning.show', planning.id)"
                        class="text-blue-600 hover:text-blue-900"
                        title="Voir"
                      >
                        <Eye class="w-5 h-5" />
                      </Link>
                      <Link
                        :href="route('planning.edit', planning.id)"
                        class="text-gray-600 hover:text-gray-900"
                        title="Modifier"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="deletePlanning(planning)"
                        class="text-red-600 hover:text-red-900"
                        title="Annuler"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
                
                <tr v-if="!plannings.data || plannings.data.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Aucune planification trouvée
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="plannings && plannings.last_page > 1" class="bg-gray-50 px-4 md:px-6 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="text-sm text-gray-700 text-center sm:text-left">
                Affichage de {{ plannings ? (plannings.current_page - 1) * plannings.per_page + 1 : 0 }} à 
                {{ plannings ? Math.min(plannings.current_page * plannings.per_page, plannings.total) : 0 }} sur 
                {{ plannings?.total || 0 }} résultats
              </div>
              
              <div class="flex gap-1 justify-center">
                <Link
                  v-for="page in (plannings?.last_page || 0)"
                  :key="page"
                  :href="`${route('planning.index')}?page=${page}`"
                  :class="[
                    'px-3 py-1 rounded text-sm',
                    page === plannings?.current_page
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