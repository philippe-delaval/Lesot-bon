<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/core'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import { Search, Download, Eye, Calendar } from 'lucide-vue-next'
import jsPDF from 'jspdf'

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
  attachements?: Attachement[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  view: [id: number]
}>()

const search = ref('')
const loading = ref(false)
const attachementsList = ref<Attachement[]>(props.attachements || [])

// Charger les attachements depuis l'API
const loadAttachements = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/attachements')
    const data = await response.json()
    attachementsList.value = data.data || []
  } catch (error) {
    console.error('Erreur chargement attachements:', error)
  } finally {
    loading.value = false
  }
}

// Filtrer les attachements
const filteredAttachements = computed(() => {
  if (!search.value) return attachementsList.value
  
  const searchLower = search.value.toLowerCase()
  return attachementsList.value.filter(att => 
    att.numero_dossier.toLowerCase().includes(searchLower) ||
    att.client_nom.toLowerCase().includes(searchLower) ||
    att.lieu_intervention.toLowerCase().includes(searchLower)
  )
})

const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: fr })
}

const formatDateTime = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy HH:mm', { locale: fr })
}

// Générer et télécharger le PDF
const generatePDF = async (attachement: Attachement) => {
  const pdf = new jsPDF('p', 'mm', 'a4')
  
  // Titre centré
  pdf.setFontSize(16)
  pdf.setFont('helvetica', 'bold')
  pdf.text('Lesot', 105, 15, { align: 'center' })
  
  // Informations entreprise
  pdf.setFontSize(10)
  pdf.setFont('helvetica', 'normal')
  pdf.text('Saint-Laurent-Blangy', 20, 25)
  pdf.text('Tél. : (-) 321.215.200', 20, 30)
  
  // Titre ATTACHEMENT DE TRAVAUX
  pdf.setFontSize(14)
  pdf.setFont('helvetica', 'bold')
  pdf.text('ATTACHEMENT', 105, 40, { align: 'center' })
  pdf.text('DE TRAVAUX', 105, 47, { align: 'center' })
  
  // Numéro en haut à droite
  pdf.setFontSize(11)
  pdf.text(`N°${attachement.numero_dossier}`, 170, 40)
  
  // Télécharger
  pdf.save(`attachement_${attachement.numero_dossier}.pdf`)
}

// Charger les attachements au montage
onMounted(() => {
  if (!props.attachements) {
    loadAttachements()
  }
})
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Barre de recherche -->
    <div class="p-4 border-b border-gray-200">
      <div class="relative">
        <input
          v-model="search"
          type="text"
          placeholder="Rechercher par N° dossier, client, lieu..."
          class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <Search class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
      </div>
    </div>
    
    <!-- Tableau -->
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
              Lieu
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Temps (h)
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
              Chargement...
            </td>
          </tr>
          
          <tr v-else-if="filteredAttachements.length === 0">
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
              Aucun attachement trouvé
            </td>
          </tr>
          
          <tr v-else v-for="attachement in filteredAttachements" :key="attachement.id" class="hover:bg-gray-50">
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
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="emit('view', attachement.id)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Voir"
                >
                  <Eye class="w-5 h-5" />
                </button>
                <button
                  @click="generatePDF(attachement)"
                  class="text-green-600 hover:text-green-900"
                  title="Télécharger PDF"
                >
                  <Download class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template> 