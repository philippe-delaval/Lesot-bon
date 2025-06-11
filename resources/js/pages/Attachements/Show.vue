<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import AppShell from '@/components/AppShell.vue'
import jsPDF from 'jspdf'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import { ArrowLeft, Download, Mail } from 'lucide-vue-next'

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
  pdf_url: string
  created_at: string
  creator?: {
    name: string
  }
}

const props = defineProps<{
  attachement: Attachement
}>()

const pdfUrl = ref('')
const loading = ref(true)

const generatePDF = async () => {
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
  pdf.text(`N°${props.attachement.numero_dossier}`, 170, 40)
  
  // Cadre CLIENT
  let yPos = 55
  pdf.rect(20, yPos, 170, 35)
  pdf.setFontSize(10)
  pdf.setFont('helvetica', 'bold')
  pdf.text('CLIENT', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(props.attachement.client_nom, 22, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('Adresse de facturation :', 22, yPos + 19)
  pdf.setFont('helvetica', 'normal')
  const adresseLines = pdf.splitTextToSize(props.attachement.client_adresse_facturation, 80)
  adresseLines.forEach((line: string, index: number) => {
    pdf.text(line, 22, yPos + 25 + (index * 5))
  })
  
  // Date et N° dossier
  pdf.setFont('helvetica', 'bold')
  pdf.text('Date', 130, yPos + 12)
  pdf.setFont('helvetica', 'normal')
  pdf.text(format(new Date(props.attachement.date_intervention), 'dd/MM/yyyy', { locale: fr }), 145, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('N° dossier', 130, yPos + 20)
  pdf.setFont('helvetica', 'normal')
  pdf.text(props.attachement.numero_dossier, 153, yPos + 20)
  
  // Lieu de l'intervention
  yPos = 95
  pdf.rect(20, yPos, 170, 15)
  pdf.setFont('helvetica', 'bold')
  pdf.text('Lieu de l\'intervention :', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(props.attachement.lieu_intervention, 65, yPos + 5)
  
  // Tableau DESIGNATION / OUVRAGE
  yPos = 115
  
  // En-têtes du tableau
  pdf.rect(20, yPos, 170, 10)
  pdf.line(105, yPos, 105, yPos + 10)
  pdf.setFont('helvetica', 'bold')
  pdf.text('DESIGNATION détaillée des', 62.5, yPos + 4, { align: 'center' })
  pdf.text('FOURNITURES de TRAVAUX EXECUTES', 62.5, yPos + 8, { align: 'center' })
  pdf.text('OUVRAGE :', 147.5, yPos + 6, { align: 'center' })
  
  // Sous-colonnes Quantités et Observations
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
  props.attachement.fournitures_travaux.forEach((ligne, index) => {
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
  pdf.text(`TEMPS TOTAL PASSÉ : ${props.attachement.temps_total_passe} heures`, 20, yPos)
  
  // Zone signatures
  yPos += 10
  const signatureBoxHeight = 40
  
  // Cadre signature entreprise
  pdf.rect(20, yPos, 80, signatureBoxHeight)
  pdf.setFontSize(10)
  pdf.text('Pour l\'Entreprise,', 22, yPos + 5)
  pdf.text(`date ${format(new Date(props.attachement.date_intervention), 'dd/MM/yyyy', { locale: fr })}`, 22, yPos + 35)
  
  // Charger et ajouter la signature entreprise
  if (props.attachement.signature_entreprise_url) {
    try {
      const img = new Image()
      img.crossOrigin = 'anonymous'
      await new Promise((resolve, reject) => {
        img.onload = resolve
        img.onerror = reject
        img.src = props.attachement.signature_entreprise_url
      })
      pdf.addImage(img, 'PNG', 25, yPos + 8, 70, 22)
    } catch (error) {
      console.error('Erreur chargement signature entreprise:', error)
    }
  }
  
  pdf.text('M.________________Visa', 22, yPos + 38)
  
  // Cadre signature client
  pdf.rect(110, yPos, 80, signatureBoxHeight)
  pdf.text('Pour le client,', 112, yPos + 5)
  pdf.text(`date ${format(new Date(props.attachement.date_intervention), 'dd/MM/yyyy', { locale: fr })}`, 112, yPos + 35)
  
  // Charger et ajouter la signature client
  if (props.attachement.signature_client_url) {
    try {
      const img = new Image()
      img.crossOrigin = 'anonymous'
      await new Promise((resolve, reject) => {
        img.onload = resolve
        img.onerror = reject
        img.src = props.attachement.signature_client_url
      })
      pdf.addImage(img, 'PNG', 115, yPos + 8, 70, 22)
    } catch (error) {
      console.error('Erreur chargement signature client:', error)
    }
  }
  
  pdf.text('M.________________Visa', 112, yPos + 38)
  
  // Créer l'URL du PDF pour l'affichage
  const pdfBlob = pdf.output('blob')
  pdfUrl.value = URL.createObjectURL(pdfBlob)
  loading.value = false
}

onMounted(() => {
  generatePDF()
})

const downloadPDF = () => {
  const link = document.createElement('a')
  link.href = pdfUrl.value
  link.download = `attachement_${props.attachement.numero_dossier}.pdf`
  link.click()
}
</script>

<template>
  <AppShell>
    <Head :title="`Attachement ${attachement.numero_dossier}`" />
    
    <div class="container mx-auto py-6 px-4">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <Link
            :href="route('dashboard')"
            class="inline-flex items-center text-gray-600 hover:text-gray-900"
          >
            <ArrowLeft class="w-5 h-5 mr-2" />
            Retour
          </Link>
          <h1 class="text-2xl font-bold text-gray-900">
            Attachement N°{{ attachement.numero_dossier }}
          </h1>
        </div>
        
        <div class="flex gap-2">
          <button
            @click="downloadPDF"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            <Download class="w-5 h-5 mr-2" />
            Télécharger PDF
          </button>
          
          <Link
            :href="`mailto:${attachement.client_email}`"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Mail class="w-5 h-5 mr-2" />
            Envoyer par email
          </Link>
        </div>
      </div>
      
      <!-- Informations -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h3 class="font-semibold text-gray-700">Client</h3>
            <p class="text-gray-900">{{ attachement.client_nom }}</p>
            <p class="text-gray-600">{{ attachement.client_email }}</p>
          </div>
          
          <div>
            <h3 class="font-semibold text-gray-700">Intervention</h3>
            <p class="text-gray-900">{{ attachement.lieu_intervention }}</p>
            <p class="text-gray-600">{{ format(new Date(attachement.date_intervention), 'dd/MM/yyyy', { locale: fr }) }}</p>
          </div>
          
          <div>
            <h3 class="font-semibold text-gray-700">Temps total</h3>
            <p class="text-gray-900">{{ attachement.temps_total_passe }} heures</p>
          </div>
          
          <div>
            <h3 class="font-semibold text-gray-700">Créé le</h3>
            <p class="text-gray-900">{{ format(new Date(attachement.created_at), 'dd/MM/yyyy HH:mm', { locale: fr }) }}</p>
            <p class="text-gray-600" v-if="attachement.creator">par {{ attachement.creator.name }}</p>
          </div>
        </div>
      </div>
      
      <!-- Visualisation PDF -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">Aperçu du document</h2>
        
        <div v-if="loading" class="flex justify-center py-12">
          <div class="text-gray-500">Génération du PDF en cours...</div>
        </div>
        
        <iframe
          v-else
          :src="pdfUrl"
          :title="`Aperçu PDF - Attachement N°${attachement.numero_dossier}`"
          class="w-full h-[800px] border border-gray-300 rounded"
        ></iframe>
      </div>
    </div>
  </AppShell>
</template> 