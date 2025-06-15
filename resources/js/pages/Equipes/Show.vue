<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { UsersRound, ArrowLeft, Edit, Users, Calendar, MapPin, Clock } from 'lucide-vue-next'

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
}

interface Props {
  equipe: Equipe
}

const props = defineProps<Props>()

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

const specialisations: Record<string, string> = {
  'installation_generale': 'Installation générale',
  'maintenance': 'Maintenance',
  'depannage_urgence': 'Dépannage d\'urgence',
  'industriel': 'Industriel',
  'tertiaire': 'Tertiaire',
  'particulier': 'Particulier',
  'eclairage_public': 'Éclairage public'
}
</script>

<template>
  <AppLayout>
    <Head :title="`Équipe ${equipe.nom}`" />
    
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link
              :href="route('equipes.index')"
              class="flex items-center gap-2 text-gray-600 hover:text-gray-900"
            >
              <ArrowLeft class="w-5 h-5" />
              Retour aux équipes
            </Link>
          </div>
          <Link
            :href="route('equipes.edit', equipe.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Edit class="w-4 h-4 mr-2" />
            Modifier
          </Link>
        </div>

        <!-- Informations principales -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                <UsersRound class="w-8 h-8 text-blue-600" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ equipe.nom }}</h1>
                <p class="text-gray-600">{{ equipe.code }}</p>
                <span :class="['px-3 py-1 rounded-full text-sm font-medium mt-2 inline-block', getSpecialisationColor(equipe.specialisation)]">
                  {{ specialisations[equipe.specialisation] }}
                </span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold text-blue-600">{{ equipe.effectif_actuel }}/{{ equipe.capacite_max }}</div>
              <div class="text-sm text-gray-600">Effectif actuel</div>
              <div class="text-sm text-gray-500 mt-1">{{ equipe.taux_occupation }}% d'occupation</div>
            </div>
          </div>

          <div v-if="equipe.description" class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Description</h3>
            <p class="text-gray-600">{{ equipe.description }}</p>
          </div>

          <!-- Informations détaillées -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <Clock class="w-5 h-5" />
                Horaires de travail
              </h3>
              <p class="text-gray-600">{{ equipe.horaire_debut }} - {{ equipe.horaire_fin }}</p>
            </div>

            <div v-if="equipe.charge_projet">
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <Users class="w-5 h-5" />
                Chargé de projet
              </h3>
              <p class="text-gray-600">{{ equipe.charge_projet.prenom }} {{ equipe.charge_projet.nom }}</p>
            </div>
          </div>
        </div>

        <!-- Membres de l'équipe -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Membres de l'équipe</h2>
          
          <div v-if="equipe.employes_actifs && equipe.employes_actifs.length > 0" class="space-y-3">
            <div
              v-for="employe in equipe.employes_actifs"
              :key="employe.nom + employe.prenom"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-medium text-sm">
                    {{ employe.prenom.charAt(0) }}{{ employe.nom.charAt(0) }}
                  </span>
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ employe.prenom }} {{ employe.nom }}</div>
                  <div class="text-sm text-gray-500 capitalize">{{ employe.pivot.role_equipe.replace('_', ' ') }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500">
            <UsersRound class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p>Aucun membre assigné à cette équipe</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>