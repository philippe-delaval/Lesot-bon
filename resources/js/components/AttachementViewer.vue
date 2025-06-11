<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import jsPDF from 'jspdf'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import { X, Download, Mail } from 'lucide-vue-next'

interface Attachement {
  id: number
  numero_dossier: string
  client_nom: string
  client_email: string
  client_adresse_facturation: string
  lieu_intervention: string
  date_intervention: string
  designation_travaux: string
  fournitures_travaux: Array<{
    designation: string
    quantite: number
    observations: string
  }>
  temps_total_passe: number
  signature_entreprise_url: string
  signature_client_url: string
  created_at: string
}

const props = defineProps<{
  attachementId: number | null
}>()

const emit = defineEmits<{
  close: []
}>()

const attachement = ref<Attachement | null>(null)
const loading = ref(false)
const pdfUrl = ref('')

// Charger l'attachement
const loadAttachement = async () => {
  if (!props.attachementId) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/attachements/${props.attachementId}`)
    const data = await response.json()
    attachement.value = data.data
    
    // Générer le PDF après chargement
    if (attachement.value) {
      await generatePDF()
    }
  } catch (error) {
    console.error('Erreur chargement attachement:', error)
  } finally {
    loading.value = false
  }
}

// Générer le PDF
const generatePDF = async () => {
  if (!attachement.value) return
  
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
  pdf.text(`N°${attachement.value.numero_dossier}`, 170, 40)
  
  // Cadre CLIENT
  let yPos = 55
  pdf.rect(20, yPos, 170, 35)
  pdf.setFontSize(10)
  pdf.setFont('helvetica', 'bold')
  pdf.text('CLIENT', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(attachement.value.client_nom, 22, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('Adresse de facturation :', 22, yPos + 19)
  pdf.setFont('helvetica', 'normal')
  const adresseLines = pdf.splitTextToSize(attachement.value.client_adresse_facturation, 80)
  adresseLines.forEach((line: string, index: number) => {
    pdf.text(line, 22, yPos + 25 + (index * 5))
  })
  
  // Date et N° dossier
  pdf.setFont('helvetica', 'bold')
  pdf.text('Date', 130, yPos + 12)
  pdf.setFont('helvetica', 'normal')
  pdf.text(format(new Date(attachement.value.date_intervention), 'dd/MM/yyyy', { locale: fr }), 145, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('N° dossier', 130, yPos + 20)
  pdf.setFont('helvetica', 'normal')
  pdf.text(attachement.value.numero_dossier, 153, yPos + 20)
  
  // Lieu de l'intervention
  yPos = 95
  pdf.rect(20, yPos, 170, 15)
  pdf.setFont('helvetica', 'bold')
  pdf.text('Lieu de l\'intervention :', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(attachement.value.lieu_intervention, 65, yPos + 5)
  
  // Désignation des travaux (ajout de cette section)
  yPos = 112
  pdf.setFont('helvetica', 'bold')
  pdf.text('Désignation des travaux :', 22, yPos)
  pdf.setFont('helvetica', 'normal')
  const travauxLines = pdf.splitTextToSize(attachement.value.designation_travaux, 165)
  travauxLines.forEach((line: string, index: number) => {
    pdf.text(line, 22, yPos + 5 + (index * 5))
  })
  
  // Tableau DESIGNATION / OUVRAGE
  yPos = 125 + (travauxLines.length * 5)
  
  // En-têtes du tableau
  pdf.rect(20, yPos, 170, 10)
  pdf.line(105, yPos, 105, yPos + 10)
  pdf.setFont('helvetica', 'bold')
  pdf.text('DESIGNATION détaillée des', 62.5, yPos + 4, { align: 'center' })
  pdf.text('FOURNITURES de TRAVAUX EXECUTES', 62.5, yPos + 8, { align: 'center' })
  pdf.text('OUVRAGE :', 147.5, yPos + 6, { align: 'center' })
  
  // Sous-colonnes
  yPos += 10
  pdf.rect(20, yPos, 85, 8)
  pdf.rect(105, yPos, 42.5, 8)
  pdf.rect(147.5, yPos, 42.5, 8)
  pdf.setFontSize(9)
  pdf.text('Quantités', 126.25, yPos + 5.5, { align: 'center' })
  pdf.text('Observations', 168.75, yPos + 5.5, { align: 'center' })
  
  // Lignes du tableau
  yPos += 8
  const startTableY = yPos
  const lineHeight = 7
  const maxLines = 15
  
  // Dessiner toutes les lignes vides
  for (let i = 0; i < maxLines; i++) {
    pdf.rect(20, yPos + (i * lineHeight), 85, lineHeight)
    pdf.rect(105, yPos + (i * lineHeight), 42.5, lineHeight)
    pdf.rect(147.5, yPos + (i * lineHeight), 42.5, lineHeight)
  }
  
  // Remplir avec les données
  pdf.setFont('helvetica', 'normal')
  pdf.setFontSize(9)
  attachement.value.fournitures_travaux.forEach((ligne, index) => {
    if (index < maxLines && ligne.designation) {
      const currentY = yPos + (index * lineHeight) + 5
      
      // Désignation
      const designation = pdf.splitTextToSize(ligne.designation, 80)
      designation.forEach((line: string, lineIndex: number) => {
        if (lineIndex === 0) {
          pdf.text(line, 22, currentY)
        }
      })
      
      // Quantité
      pdf.text(ligne.quantite.toString(), 126.25, currentY, { align: 'center' })
      
      // Observations
      if (ligne.observations) {
        const obs = pdf.splitTextToSize(ligne.observations, 40)
        pdf.text(obs[0], 149, currentY)
      }
    }
  })
  
  // TEMPS TOTAL PASSÉ
  yPos = startTableY + (maxLines * lineHeight) + 5
  pdf.setFont('helvetica', 'bold')
  pdf.text(`TEMPS TOTAL PASSÉ : ${attachement.value.temps_total_passe} heures`, 20, yPos)
  
  // Zone signatures
  yPos += 10
  const signatureBoxHeight = 40
  
  // Cadre signature entreprise
  pdf.rect(20, yPos, 80, signatureBoxHeight)
  pdf.setFontSize(10)
  pdf.text('Pour l\'Entreprise,', 22, yPos + 5)
  pdf.text(`date ${format(new Date(attachement.value.date_intervention), 'dd/MM/yyyy', { locale: fr })}`, 22, yPos + 35)
  
  // TODO: Ajouter les signatures depuis les URLs
  
  pdf.text('M.________________Visa', 22, yPos + 38)
  
  // Cadre signature client
  pdf.rect(110, yPos, 80, signatureBoxHeight)
  pdf.text('Pour le client,', 112, yPos + 5)
  pdf.text(`date ${format(new Date(attachement.value.date_intervention), 'dd/MM/yyyy', { locale: fr })}`, 112, yPos + 35)
  
  pdf.text('M.________________Visa', 112, yPos + 38)
  
  // Créer l'URL du PDF
  const pdfBlob = pdf.output('blob')
  pdfUrl.value = URL.createObjectURL(pdfBlob)
}

// Télécharger le PDF
const downloadPDF = () => {
  if (!attachement.value) return
  
  const link = document.createElement('a')
  link.href = pdfUrl.value
  link.download = `attachement_${attachement.value.numero_dossier}.pdf`
  link.click()
}

// Observer les changements d'ID
watch(() => props.attachementId, (newId) => {
  if (newId) {
    loadAttachement()
  }
})

onMounted(() => {
  if (props.attachementId) {
    loadAttachement()
  }
})
</script>

<template>
  <div v-if="attachementId" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="emit('close')"></div>
    
    <!-- Modal -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <h2 class="text-xl font-semibold">
            {{ attachement ? `Attachement N°${attachement.numero_dossier}` : 'Chargement...' }}
          </h2>
          
          <div class="flex items-center gap-2">
            <button
              v-if="attachement"
              @click="downloadPDF"
              class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
            >
              <Download class="w-4 h-4 mr-1" />
              Télécharger
            </button>
            
            <button
              @click="emit('close')"
              class="p-1 hover:bg-gray-100 rounded transition-colors"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        
        <!-- Content -->
        <div class="p-4 overflow-y-auto" style="max-height: calc(90vh - 80px)">
          <div v-if="loading" class="flex justify-center py-12">
            <div class="text-gray-500">Chargement...</div>
          </div>
          
          <iframe
            v-else-if="pdfUrl"
            :src="pdfUrl"
            :title="`Aperçu PDF - Attachement N°${attachement?.numero_dossier || 'N/A'}`"
            class="w-full h-[700px] border border-gray-300 rounded"
          ></iframe>
        </div>
      </div>
    </div>
  </div>
</template> 