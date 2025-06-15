<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Calendar, ArrowLeft, Edit, Clock, MapPin, User } from 'lucide-vue-next'

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
  planning: Planning
}

const props = defineProps<Props>()

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

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatHeure = (heure?: string) => {
  if (!heure) return '-'
  return heure.substring(0, 5)
}
</script>

<template>
  <AppLayout>
    <Head :title="`Planning - ${typesAffectation[planning.type_affectation]}`" />
    
    <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link
              :href="route('planning.index')"
              class="flex items-center gap-2 text-gray-600 hover:text-gray-900"
            >
              <ArrowLeft class="w-5 h-5" />
              Retour au planning
            </Link>
          </div>
          <Link
            :href="route('planning.edit', planning.id)"
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
                <Calendar class="w-8 h-8 text-blue-600" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">
                  {{ planning.demande?.titre || typesAffectation[planning.type_affectation] }}
                </h1>
                <p class="text-gray-600">{{ planning.description_tache }}</p>
                <span :class="['px-3 py-1 rounded-full text-sm font-medium mt-2 inline-block', getStatutColor(planning.statut)]">
                  {{ statuts[planning.statut] }}
                </span>
              </div>
            </div>
          </div>

          <!-- Informations détaillées -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <Clock class="w-5 h-5" />
                Date et horaires
              </h3>
              <div class="space-y-2 text-gray-600">
                <p><strong>Date :</strong> {{ formatDate(planning.date_debut) }}</p>
                <p v-if="planning.heure_debut_prevue">
                  <strong>Horaires prévus :</strong> 
                  {{ formatHeure(planning.heure_debut_prevue) }} - {{ formatHeure(planning.heure_fin_prevue) }}
                </p>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <User class="w-5 h-5" />
                Assignation
              </h3>
              <div class="space-y-2 text-gray-600">
                <p><strong>Employé :</strong> {{ planning.employe.prenom }} {{ planning.employe.nom }}</p>
                <p v-if="planning.equipe">
                  <strong>Équipe :</strong> {{ planning.equipe.nom }} ({{ planning.equipe.code }})
                </p>
              </div>
            </div>

            <div v-if="planning.lieu_intervention">
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <MapPin class="w-5 h-5" />
                Lieu d'intervention
              </h3>
              <p class="text-gray-600">{{ planning.lieu_intervention }}</p>
            </div>

            <div v-if="planning.demande?.client">
              <h3 class="text-lg font-medium text-gray-900 mb-3">Client</h3>
              <p class="text-gray-600">{{ planning.demande.client.nom }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>