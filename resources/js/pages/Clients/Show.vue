<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { Users, ArrowLeft, Edit, Mail, Phone, MapPin, FileText, Calendar } from 'lucide-vue-next'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'

interface Attachement {
  id: number
  numero_dossier: string
  lieu_intervention: string
  date_intervention: string
  created_at: string
}

interface Client {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string
  code_postal: string
  ville: string
  telephone?: string
  notes?: string
  created_at: string
  attachements: Attachement[]
}

interface Props {
  client: Client
}

const props = defineProps<Props>()

const breadcrumbItems = [
  { title: 'Vue d\'ensemble', href: '/dashboard' },
  { title: 'Clients', href: '/clients' },
  { title: props.client.nom, current: true }
]

const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: fr })
}
</script>

<template>
  <AppLayout>
    <Head :title="`Client - ${client.nom}`" />
    
    <div class="flex flex-1 flex-col gap-6 p-4">
      <!-- Breadcrumb -->
      <Breadcrumbs :breadcrumbs="breadcrumbItems" />
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Users class="h-8 w-8 text-blue-600" />
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ client.nom }}</h1>
            <p class="text-sm text-gray-500">Détails du client</p>
          </div>
        </div>
        
        <div class="flex gap-3">
          <Link
            :href="route('clients.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Retour
          </Link>
          <Link
            :href="route('clients.edit', client.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Edit class="w-4 h-4 mr-2" />
            Modifier
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations client -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Coordonnées -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Coordonnées</h2>
            
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <Mail class="h-5 w-5 text-gray-400" />
                <div>
                  <p class="text-sm font-medium text-gray-900">Email</p>
                  <a :href="`mailto:${client.email}`" class="text-blue-600 hover:text-blue-800">
                    {{ client.email }}
                  </a>
                </div>
              </div>

              <div v-if="client.telephone" class="flex items-center gap-3">
                <Phone class="h-5 w-5 text-gray-400" />
                <div>
                  <p class="text-sm font-medium text-gray-900">Téléphone</p>
                  <a :href="`tel:${client.telephone}`" class="text-blue-600 hover:text-blue-800">
                    {{ client.telephone }}
                  </a>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <MapPin class="h-5 w-5 text-gray-400 mt-1" />
                <div>
                  <p class="text-sm font-medium text-gray-900">Adresse</p>
                  <div class="text-gray-700">
                    <p>{{ client.adresse }}</p>
                    <p v-if="client.complement_adresse">{{ client.complement_adresse }}</p>
                    <p>{{ client.code_postal }} {{ client.ville }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="client.notes" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ client.notes }}</p>
          </div>

          <!-- Attachements récents -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Attachements récents</h2>
              <Link
                :href="route('attachements.create')"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                Créer un attachement
              </Link>
            </div>

            <div v-if="client.attachements.length > 0" class="space-y-3">
              <div
                v-for="attachement in client.attachements"
                :key="attachement.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center gap-3">
                  <FileText class="h-5 w-5 text-gray-400" />
                  <div>
                    <p class="font-medium text-gray-900">
                      N° {{ attachement.numero_dossier }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ attachement.lieu_intervention }}
                    </p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">
                    {{ formatDate(attachement.date_intervention) }}
                  </p>
                  <Link
                    :href="route('attachements.show', attachement.id)"
                    class="text-xs text-blue-600 hover:text-blue-800"
                  >
                    Voir détails
                  </Link>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <FileText class="h-12 w-12 mx-auto mb-4 text-gray-300" />
              <p>Aucun attachement pour ce client</p>
            </div>
          </div>
        </div>

        <!-- Sidebar avec infos -->
        <div class="space-y-6">
          <!-- Statistiques -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiques</h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Total attachements</span>
                <span class="font-semibold text-gray-900">{{ client.attachements.length }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Client depuis</span>
                <span class="font-semibold text-gray-900">{{ formatDate(client.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Actions rapides -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
            
            <div class="space-y-3">
              <Link
                :href="route('attachements.create')"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                <FileText class="w-4 h-4 mr-2" />
                Créer un attachement
              </Link>
              
              <a
                :href="`mailto:${client.email}`"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
              >
                <Mail class="w-4 h-4 mr-2" />
                Envoyer un email
              </a>
              
              <a
                v-if="client.telephone"
                :href="`tel:${client.telephone}`"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
              >
                <Phone class="w-4 h-4 mr-2" />
                Appeler
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>