<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import SignaturePad from 'signature_pad'
import jsPDF from 'jspdf'
import { format } from 'date-fns/format'
import { fr } from 'date-fns/locale/fr'
import emailjs from '@emailjs/browser'
import { EMAILJS_CONFIG } from '@/config/emailjs'



// Refs
const signatureEntrepriseCanvas = ref<HTMLCanvasElement>()
const signatureClientCanvas = ref<HTMLCanvasElement>()
const formRef = ref<HTMLFormElement>()

let signatureEntreprisePad: SignaturePad | null = null
let signatureClientPad: SignaturePad | null = null

// Props et emits
defineProps<{
  onCancel?: () => void
  onSuccess?: () => void
}>()

const emit = defineEmits<{
  cancel: []
  success: []
}>()

// Form data avec Inertia
const form = useForm({
  client_nom: '',
  client_email: '',
  client_adresse_facturation: '',
  numero_dossier: '',
  lieu_intervention: '',
  date_intervention: format(new Date(), 'yyyy-MM-dd'),
  designation_travaux: '',
  fournitures_travaux: [
    {
      designation: '',
      quantite: '',
      observations: ''
    }
  ],
  temps_total_passe: '',
  signature_entreprise: '',
  signature_client: '',
  geolocation: null as any,
  pdf: null as File | null
})

// State management
const errors = ref<string[]>([])
const success = ref('')

// Computed properties
const currentDate = computed(() => format(new Date(), 'yyyy-MM-dd'))

// Geolocation data
const geolocationData = ref({
  latitude: null as number | null,
  longitude: null as number | null,
  timestamp: null as string | null
})

// Initialize signature pads
onMounted(() => {
  if (signatureEntrepriseCanvas.value) {
    signatureEntreprisePad = new SignaturePad(signatureEntrepriseCanvas.value, {
      backgroundColor: 'rgb(255, 255, 255)',
      penColor: 'rgb(0, 0, 0)',
      minWidth: 1,
      maxWidth: 3
    })
  }

  if (signatureClientCanvas.value) {
    signatureClientPad = new SignaturePad(signatureClientCanvas.value, {
      backgroundColor: 'rgb(255, 255, 255)',
      penColor: 'rgb(0, 0, 0)',
      minWidth: 1,
      maxWidth: 3
    })
  }

  // Get geolocation
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        geolocationData.value.latitude = position.coords.latitude
        geolocationData.value.longitude = position.coords.longitude
        geolocationData.value.timestamp = new Date().toISOString()
      },
      (error) => {
        console.error('Erreur de géolocalisation:', error)
      }
    )
  }

  // Initialize EmailJS
  emailjs.init(EMAILJS_CONFIG.PUBLIC_KEY)
})

// Methods
const ajouterLigne = () => {
  form.fournitures_travaux.push({
    designation: '',
    quantite: '',
    observations: ''
  })
}

const supprimerLigne = (index: number) => {
  if (form.fournitures_travaux.length > 1) {
    form.fournitures_travaux.splice(index, 1)
  }
}

const clearSignature = (type: 'entreprise' | 'client') => {
  if (type === 'entreprise' && signatureEntreprisePad) {
    signatureEntreprisePad.clear()
  } else if (type === 'client' && signatureClientPad) {
    signatureClientPad.clear()
  }
}

const generatePDFAndPrint = () => {
  window.print()
}

const generatePDF = async (): Promise<Blob> => {
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
  pdf.text(`N°${form.numero_dossier || '3329'}`, 170, 40)
  
  // Cadre CLIENT
  let yPos = 55
  pdf.rect(20, yPos, 170, 35)
  pdf.setFontSize(10)
  pdf.setFont('helvetica', 'bold')
  pdf.text('CLIENT', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(form.client_nom, 22, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('Adresse de facturation :', 22, yPos + 19)
  pdf.setFont('helvetica', 'normal')
  const adresseLines = pdf.splitTextToSize(form.client_adresse_facturation, 80)
  adresseLines.forEach((line: string, index: number) => {
    pdf.text(line, 22, yPos + 25 + (index * 5))
  })
  
  // Date et N° dossier
  pdf.setFont('helvetica', 'bold')
  pdf.text('Date', 130, yPos + 12)
  pdf.setFont('helvetica', 'normal')
  pdf.text(format(new Date(form.date_intervention), 'dd/MM/yyyy', { locale: fr }), 145, yPos + 12)
  
  pdf.setFont('helvetica', 'bold')
  pdf.text('N° dossier', 130, yPos + 20)
  pdf.setFont('helvetica', 'normal')
  pdf.text(form.numero_dossier || '', 153, yPos + 20)
  
  // Lieu de l'intervention
  yPos = 95
  pdf.rect(20, yPos, 170, 15)
  pdf.setFont('helvetica', 'bold')
  pdf.text('Lieu de l\'intervention :', 22, yPos + 5)
  pdf.setFont('helvetica', 'normal')
  pdf.text(form.lieu_intervention, 65, yPos + 5)
  
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
  const maxLines = 15 // Nombre de lignes comme sur le document papier
  
  // Dessiner toutes les lignes vides d'abord
  for (let i = 0; i < maxLines; i++) {
    pdf.rect(20, yPos + (i * lineHeight), 85, lineHeight)
    pdf.rect(105, yPos + (i * lineHeight), 42.5, lineHeight)
    pdf.rect(147.5, yPos + (i * lineHeight), 42.5, lineHeight)
  }
  
  // Remplir avec les données
  pdf.setFont('helvetica', 'normal')
  pdf.setFontSize(9)
  form.fournitures_travaux.forEach((ligne: any, index: number) => {
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
  pdf.text(`TEMPS TOTAL PASSÉ : ${form.temps_total_passe} heures`, 20, yPos)
  
  // Zone signatures
  yPos += 10
  const signatureBoxHeight = 40
  
  // Cadre signature entreprise
  pdf.rect(20, yPos, 80, signatureBoxHeight)
  pdf.setFontSize(10)
  pdf.text('Pour l\'Entreprise,', 22, yPos + 5)
  pdf.text(`date ${format(new Date(), 'dd/MM/yyyy', { locale: fr })}`, 22, yPos + 35)
  
  // Signature entreprise
  if (signatureEntreprisePad && !signatureEntreprisePad.isEmpty()) {
    const signatureEntrepriseData = signatureEntreprisePad.toDataURL()
    pdf.addImage(signatureEntrepriseData, 'PNG', 25, yPos + 8, 70, 22)
  }
  
  pdf.text('M.________________Visa', 22, yPos + 38)
  
  // Cadre signature client
  pdf.rect(110, yPos, 80, signatureBoxHeight)
  pdf.text('Pour le client,', 112, yPos + 5)
  pdf.text(`date ${format(new Date(), 'dd/MM/yyyy', { locale: fr })}`, 112, yPos + 35)
  
  // Signature client
  if (signatureClientPad && !signatureClientPad.isEmpty()) {
    const signatureClientData = signatureClientPad.toDataURL()
    pdf.addImage(signatureClientData, 'PNG', 115, yPos + 8, 70, 22)
  }
  
  pdf.text('M.________________Visa', 112, yPos + 38)
  
  // Retourner le PDF comme Blob
  return pdf.output('blob')
}

const sendEmail = async (pdfBlob: Blob) => {
  // Convertir le Blob en base64
  const reader = new FileReader()
  reader.readAsDataURL(pdfBlob)
  
  return new Promise((resolve, reject) => {
    reader.onloadend = async () => {
      const base64data = reader.result as string
      
      // Paramètres pour EmailJS
      const templateParams = {
        to_email: form.client_email,
        to_name: form.client_nom,
        from_name: 'Lesot', // À personnaliser
        numero_dossier: form.numero_dossier || 'N/A',
        date_intervention: format(new Date(form.date_intervention), 'dd/MM/yyyy', { locale: fr }),
        lieu_intervention: form.lieu_intervention,
        attachment: base64data.split(',')[1] // Enlever le préfixe data:application/pdf;base64,
      }
      
      try {
        // Envoi de l'email via EmailJS
        await emailjs.send(EMAILJS_CONFIG.SERVICE_ID, EMAILJS_CONFIG.TEMPLATE_ID, templateParams)
        resolve(true)
      } catch (error) {
        reject(error)
      }
    }
  })
}

const submitForm = async () => {
  errors.value = []
  success.value = ''
  
  try {
    // Validation des signatures
    if (!signatureEntreprisePad || signatureEntreprisePad.isEmpty()) {
      errors.value.push('La signature de l\'entreprise est requise')
      return
    }
    
    if (!signatureClientPad || signatureClientPad.isEmpty()) {
      errors.value.push('La signature du client est requise')
      return
    }
    
    // Générer le PDF
    const pdfBlob = await generatePDF()
    
    // Préparer les données pour Inertia
    form.signature_entreprise = signatureEntreprisePad.toDataURL()
    form.signature_client = signatureClientPad.toDataURL()
    form.geolocation = geolocationData.value
    form.fournitures_travaux = JSON.stringify(form.fournitures_travaux) as any
    
    // Convertir le PDF en File pour Inertia
    const pdfFile = new File([pdfBlob], `attachement_${form.numero_dossier || 'nouveau'}.pdf`, {
      type: 'application/pdf'
    })
    form.pdf = pdfFile
    
    // Soumission via Inertia
    form.post(route('attachements.store'), {
      onSuccess: async () => {
        // Envoyer l'email au client
        try {
          await sendEmail(pdfBlob)
          success.value = 'Attachement créé et envoyé par email avec succès!'
        } catch (emailError) {
          console.error('Erreur envoi email:', emailError)
          success.value = 'Attachement créé avec succès! (Erreur lors de l\'envoi de l\'email)'
        }
        
        // Réinitialiser le formulaire
        resetForm()
        
        // Émettre l'événement de succès
        emit('success')
      },
      onError: (formErrors: any) => {
        if (formErrors) {
          errors.value = Object.values(formErrors).flat() as string[]
        } else {
          errors.value = ['Une erreur est survenue lors de la création']
        }
      }
    })
    
  } catch (error) {
    console.error('Erreur:', error)
    errors.value = ['Une erreur est survenue lors du traitement']
  }
}

const resetForm = () => {
  // Réinitialiser les données du formulaire
  form.reset()
  form.client_nom = ''
  form.client_email = ''
  form.client_adresse_facturation = ''
  form.numero_dossier = ''
  form.lieu_intervention = ''
  form.date_intervention = format(new Date(), 'yyyy-MM-dd')
  form.designation_travaux = ''
  form.fournitures_travaux = [
    {
      designation: '',
      quantite: '',
      observations: ''
    }
  ]
  form.temps_total_passe = ''
  
  // Effacer les signatures
  clearSignature('entreprise')
  clearSignature('client')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Attachement de Travaux</h1>
              <p class="text-sm text-gray-500">N° {{ form.numero_dossier || '3329' }}</p>
            </div>
          </div>
          <div class="flex space-x-2">
            <button
              @click="generatePDFAndPrint"
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Imprimer/PDF
            </button>
            <button 
              @click="submitForm"
              :disabled="form.processing"
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:bg-gray-400"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <span v-if="form.processing">Traitement...</span>
              <span v-else>Sauvegarder</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div v-if="success" class="max-w-7xl mx-auto px-4 py-4">
      <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        {{ success }}
      </div>
    </div>
    
    <div v-if="errors.length > 0" class="max-w-7xl mx-auto px-4 py-4">
      <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <ul class="list-disc list-inside">
          <li v-for="error in errors" :key="error">{{ error }}</li>
        </ul>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="bg-white rounded-lg shadow-lg" ref="printRef">
        <!-- Company Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-t-lg">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-2xl font-bold">LESOT</h2>
              <p class="text-blue-100">Saint-Laurent-Blangy</p>
              <p class="text-blue-100">Tél. 03 21 215 200</p>
            </div>
            <div class="text-right">
              <h3 class="text-xl font-semibold">ATTACHEMENT DE TRAVAUX</h3>
              <p class="text-blue-100">N° {{ form.numero_dossier || '3329' }}</p>
            </div>
          </div>
        </div>

        <form ref="formRef" @submit.prevent="submitForm" class="p-6 space-y-6">
          <!-- Client Information -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="flex items-center space-x-2 text-lg font-semibold text-gray-800">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>CLIENT</span>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nom du client
                  </label>
                  <input
                    v-model="form.client_nom"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email du client
                  </label>
                  <input
                    v-model="form.client_email"
                    type="email"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Adresse de facturation
                  </label>
                  <textarea
                    v-model="form.client_adresse_facturation"
                    rows="3"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Date
                  </label>
                  <input
                    v-model="form.date_intervention"
                    type="date"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    N° dossier
                  </label>
                  <input
                    v-model="form.numero_dossier"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Intervention Details -->
          <div class="space-y-4">
            <div class="flex items-center space-x-2 text-lg font-semibold text-gray-800">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>INTERVENTION</span>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Lieu de l'intervention
                </label>
                <input
                  v-model="form.lieu_intervention"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  OUVRAGE
                </label>
                <input
                  v-model="form.designation_travaux"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Work Items Table -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-800">
                DÉSIGNATION détaillée des FOURNITURES de TRAVAUX EXÉCUTÉS
              </h3>
              <button
                type="button"
                @click="ajouterLigne"
                class="flex items-center px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
              >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Ajouter
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse border border-gray-300">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="border border-gray-300 px-4 py-3 text-left font-semibold">
                      Désignation
                    </th>
                    <th class="border border-gray-300 px-4 py-3 text-center font-semibold w-32">
                      Quantités
                    </th>
                    <th class="border border-gray-300 px-4 py-3 text-left font-semibold w-48">
                      Observations
                    </th>
                    <th class="border border-gray-300 px-4 py-3 text-center font-semibold w-16">
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(ligne, index) in form.fournitures_travaux" :key="index" class="hover:bg-gray-50">
                    <td class="border border-gray-300 px-4 py-3">
                      <textarea
                        v-model="ligne.designation"
                        rows="2"
                        required
                        class="w-full border-none resize-none focus:ring-2 focus:ring-blue-500 rounded"
                        placeholder="Description des travaux..."
                      ></textarea>
                    </td>
                    <td class="border border-gray-300 px-4 py-3">
                      <input
                        v-model="ligne.quantite"
                        type="text"
                        required
                        class="w-full border-none text-center focus:ring-2 focus:ring-blue-500 rounded"
                        placeholder="Qté"
                      />
                    </td>
                    <td class="border border-gray-300 px-4 py-3">
                      <textarea
                        v-model="ligne.observations"
                        rows="2"
                        class="w-full border-none resize-none focus:ring-2 focus:ring-blue-500 rounded"
                        placeholder="Observations..."
                      ></textarea>
                    </td>
                    <td class="border border-gray-300 px-4 py-3 text-center">
                      <button
                        v-if="form.fournitures_travaux.length > 1"
                        type="button"
                        @click="supprimerLigne(index)"
                        class="text-red-600 hover:text-red-800"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Total Time -->
          <div class="space-y-2">
            <label class="block text-lg font-semibold text-gray-800">
              TEMPS TOTAL PASSÉ :
            </label>
            <input
              v-model="form.temps_total_passe"
              type="text"
              required
              class="w-64 border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ex: 8h30"
            />
          </div>

          <!-- Signatures -->
          <div class="grid md:grid-cols-2 gap-6 pt-6 border-t">
            <div class="space-y-4">
              <h4 class="font-semibold text-gray-800">Pour l'Entreprise</h4>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Date</label>
                  <input
                    type="date"
                    :value="currentDate"
                    readonly
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                  />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Nom</label>
                  <input
                    type="text"
                    placeholder="Nom du représentant"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Signature Entreprise <span class="text-red-500">*</span>
                  </label>
                  <div class="border-2 border-dashed border-gray-300 rounded-md">
                    <canvas
                      ref="signatureEntrepriseCanvas"
                      width="400"
                      height="120"
                      class="w-full"
                    ></canvas>
                  </div>
                  <button
                    type="button"
                    @click="clearSignature('entreprise')"
                    class="mt-2 px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                  >
                    Effacer
                  </button>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <h4 class="font-semibold text-gray-800">Pour le client</h4>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Date</label>
                  <input
                    type="date"
                    :value="currentDate"
                    readonly
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                  />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Nom</label>
                  <input
                    v-model="form.client_nom"
                    type="text"
                    readonly
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Signature Client <span class="text-red-500">*</span>
                  </label>
                  <div class="border-2 border-dashed border-gray-300 rounded-md">
                    <canvas
                      ref="signatureClientCanvas"
                      width="400"
                      height="120"
                      class="w-full"
                    ></canvas>
                  </div>
                  <button
                    type="button"
                    @click="clearSignature('client')"
                    class="mt-2 px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                  >
                    Effacer
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Print styles */
@media print {
  .bg-white {
    background: white !important;
  }
  
  .shadow-lg {
    box-shadow: none !important;
  }
  
  .sticky {
    position: static !important;
  }
  
  .bg-gray-50 {
    background: white !important;
  }
  
  /* Hide header and buttons when printing */
  .bg-white.shadow-sm.border-b,
  .flex.space-x-2 {
    display: none !important;
  }
}

/* Custom canvas styles */
canvas {
  background: white;
  touch-action: none;
}
</style>
