<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { User, ArrowLeft, Edit, Shield, Users, Calendar } from 'lucide-vue-next'

interface Employe {
  id: number
  nom: string
  prenom: string
  email: string
  telephone?: string
  matricule: string
  statut: string
  role_hierarchique: string
  habilitations_electriques?: string[]
  disponibilite: string
  nom_complet: string
  initiales: string
  niveau_experience: string
  notes?: string
}

interface Props {
  employe: Employe
}

const props = defineProps<Props>()

const getStatutColor = (statut: string) => {
  return statut === 'permanent' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-blue-100 text-blue-800'
}

const getDisponibiliteColor = (disponibilite: string) => {
  switch (disponibilite) {
    case 'disponible': return 'bg-green-100 text-green-800'
    case 'occupe': return 'bg-red-100 text-red-800'
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
    <Head :title="`Employé - ${employe.nom_complet}`" />
    
    <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link
              :href="route('employes.index')"
              class="flex items-center gap-2 text-gray-600 hover:text-gray-900"
            >
              <ArrowLeft class="w-5 h-5" />
              Retour aux employés
            </Link>
          </div>
          <Link
            :href="route('employes.edit', employe.id)"
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
                <span class="text-blue-600 font-medium text-lg">{{ employe.initiales }}</span>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ employe.nom_complet }}</h1>
                <p class="text-gray-600">{{ employe.matricule }} • {{ employe.niveau_experience }}</p>
                <div class="flex gap-2 mt-2">
                  <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatutColor(employe.statut)]">
                    {{ employe.statut }}
                  </span>
                  <span :class="['px-3 py-1 rounded-full text-sm font-medium', getDisponibiliteColor(employe.disponibilite)]">
                    {{ employe.disponibilite }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3">Contact</h3>
              <div class="space-y-2 text-gray-600">
                <p><strong>Email :</strong> {{ employe.email }}</p>
                <p v-if="employe.telephone"><strong>Téléphone :</strong> {{ employe.telephone }}</p>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                <Shield class="w-5 h-5" />
                Habilitations électriques
              </h3>
              <p class="text-gray-600">{{ formatHabilitations(employe.habilitations_electriques) }}</p>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="employe.notes">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Notes</h3>
            <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">{{ employe.notes }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>