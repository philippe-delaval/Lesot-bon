<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import SignaturePad from 'signature_pad'
import jsPDF from 'jspdf'
import { format } from 'date-fns/format'
import { fr } from 'date-fns/locale/fr'
import emailjs from '@emailjs/browser'
import { EMAILJS_CONFIG } from '@/config/emailjs'
import { ArrowLeft, Save, FileText, Plus, Trash2, MapPin, User, Building2, Clock, HelpCircle } from 'lucide-vue-next'

import Breadcrumbs from '@/components/Breadcrumbs.vue'
import ClientSelectorDual from '@/components/ClientSelectorDual.vue'
import Toast from '@/components/Toast.vue'

import type { Client } from '@/types/client'

// Props et emits
defineProps<{
  onCancel?: () => void
  onSuccess?: () => void
}>()

const emit = defineEmits<{
  cancel: []
  success: []
}>()

// Refs
const signatureEntrepriseCanvas = ref<HTMLCanvasElement>()
const signatureClientCanvas = ref<HTMLCanvasElement>()

let signatureEntreprisePad: SignaturePad | null = null
let signatureClientPad: SignaturePad | null = null

// Form data
const form = useForm({
  client_id: null as number | null,
  client_nom: '',
  nom_signataire_client: '',
  client_email: '',
  client_adresse: '',
  client_complement_adresse: '',
  client_code_postal: '',
  client_ville: '',
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

// State
const selectedClient = ref<Client | null>(null)
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('info')
const errors = ref<string[]>([])

// Computed
const currentDate = computed(() => format(new Date(), 'yyyy-MM-dd'))

const breadcrumbItems = [
  { title: 'Vue d\'ensemble', href: '/dashboard' },
  { title: 'Attachements', href: '/attachements' },
  { title: 'Nouvel Attachement', current: true }
]

// Geolocation
const geolocationData = ref({
  latitude: null as number | null,
  longitude: null as number | null,
  timestamp: null as string | null
})

// Methods
const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
}

const onClientSelected = (client: Client | null) => {
  selectedClient.value = client
  if (client) {
    form.client_id = client.id
    form.client_nom = client.nom
    form.client_email = client.email
    form.client_adresse = client.adresse
    form.client_complement_adresse = client.complement_adresse || ''
    form.client_code_postal = client.code_postal
    form.client_ville = client.ville
    
    // Ne pas pré-remplir le nom du signataire - laissé vide pour saisie manuelle
  } else {
    form.client_id = null
    form.client_nom = ''
    form.client_email = ''
    form.client_adresse = ''
    form.client_complement_adresse = ''
    form.client_code_postal = ''
    form.client_ville = ''
    // Ne pas vider nom_signataire_client pour préserver la saisie utilisateur
  }
}

const onAddClient = (clientData: Partial<Client>) => {
  // Ouvrir la page de création de client dans un nouvel onglet
  window.open('/clients/create', '_blank')
  showToastMessage('Veuillez créer le client dans l\'onglet ouvert, puis revenez ici pour le sélectionner.', 'info')
}

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
  pdf.text('Tél. : 03 21 215 200', 20, 30)
  
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
  
  // Construire l'adresse complète
  let adresseComplete = form.client_adresse
  if (form.client_complement_adresse) {
    adresseComplete += '\n' + form.client_complement_adresse
  }
  adresseComplete += '\n' + form.client_code_postal + ' ' + form.client_ville
  
  const adresseLines = pdf.splitTextToSize(adresseComplete, 80)
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
  const maxLines = 15
  
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
      
      const designation = pdf.splitTextToSize(ligne.designation, 80)
      designation.forEach((line: string, lineIndex: number) => {
        if (lineIndex === 0) {
          pdf.text(line, 22, currentY)
        }
      })
      
      pdf.text(ligne.quantite.toString(), 126.25, currentY, { align: 'center' })
      
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
  
  if (signatureEntreprisePad && !signatureEntreprisePad.isEmpty()) {
    const signatureEntrepriseData = signatureEntreprisePad.toDataURL()
    pdf.addImage(signatureEntrepriseData, 'PNG', 25, yPos + 8, 70, 22)
  }
  
  pdf.text('M.________________Visa', 22, yPos + 38)
  
  // Cadre signature client
  pdf.rect(110, yPos, 80, signatureBoxHeight)
  pdf.text('Pour le client,', 112, yPos + 5)
  pdf.text(`date ${format(new Date(), 'dd/MM/yyyy', { locale: fr })}`, 112, yPos + 35)
  
  if (signatureClientPad && !signatureClientPad.isEmpty()) {
    const signatureClientData = signatureClientPad.toDataURL()
    pdf.addImage(signatureClientData, 'PNG', 115, yPos + 8, 70, 22)
  }
  
  pdf.text('M.________________Visa', 112, yPos + 38)
  
  return pdf.output('blob')
}

const sendEmail = async (pdfBlob: Blob) => {
  const reader = new FileReader()
  reader.readAsDataURL(pdfBlob)
  
  return new Promise((resolve, reject) => {
    reader.onloadend = async () => {
      const base64data = reader.result as string
      
      const templateParams = {
        to_email: form.client_email,
        to_name: form.client_nom,
        from_name: 'Lesot',
        numero_dossier: form.numero_dossier || 'N/A',
        date_intervention: format(new Date(form.date_intervention), 'dd/MM/yyyy', { locale: fr }),
        lieu_intervention: form.lieu_intervention,
        attachment: base64data.split(',')[1]
      }
      
      try {
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
    
    // Validation du client
    if (!form.client_nom || !form.client_email) {
      errors.value.push('Les informations client sont requises')
      return
    }
    
    showToastMessage('Génération du PDF en cours...', 'info')
    
    // Générer le PDF
    const pdfBlob = await generatePDF()
    
    // Préparer les données
    form.signature_entreprise = signatureEntreprisePad.toDataURL()
    form.signature_client = signatureClientPad.toDataURL()
    form.geolocation = geolocationData.value
    form.fournitures_travaux = JSON.stringify(form.fournitures_travaux) as any
    
    const pdfFile = new File([pdfBlob], `attachement_${form.numero_dossier || 'nouveau'}.pdf`, {
      type: 'application/pdf'
    })
    form.pdf = pdfFile
    
    // Soumission
    form.post(route('attachements.store'), {
      onSuccess: async () => {
        try {
          await sendEmail(pdfBlob)
          showToastMessage('Attachement créé et envoyé par email avec succès!', 'success')
        } catch (emailError) {
          console.error('Erreur envoi email:', emailError)
          showToastMessage('Attachement créé avec succès! (Erreur lors de l\'envoi de l\'email)', 'warning')
        }
        
        resetForm()
        emit('success')
      },
      onError: (formErrors: any) => {
        if (formErrors) {
          errors.value = Object.values(formErrors).flat() as string[]
          showToastMessage('Erreur lors de la création', 'error')
        }
      }
    })
    
  } catch (error) {
    console.error('Erreur:', error)
    errors.value = ['Une erreur est survenue lors du traitement']
    showToastMessage('Une erreur est survenue', 'error')
  }
}

const resetForm = () => {
  form.reset()
  selectedClient.value = null
  clearSignature('entreprise')
  clearSignature('client')
  form.date_intervention = format(new Date(), 'yyyy-MM-dd')
  form.fournitures_travaux = [{ designation: '', quantite: '', observations: '' }]
}

// Initialize
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

  // Géolocalisation
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

  emailjs.init(EMAILJS_CONFIG.PUBLIC_KEY)
})
</script>

<template>
  <div class="flex flex-1 flex-col gap-6 p-4">
    <!-- Breadcrumb -->
    <Breadcrumbs :breadcrumbs="breadcrumbItems" />
    
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <FileText class="h-8 w-8 text-blue-600" />
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">Nouvel Attachement</h1>
          <p class="text-sm text-gray-500">Création d'un attachement de travaux</p>
        </div>
      </div>
      
      <Link
        :href="route('dashboard')"
        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
      >
        <ArrowLeft class="w-4 h-4 mr-2" />
        Retour
      </Link>
    </div>

    <!-- Messages d'erreur -->
    <div v-if="errors.length > 0" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      <ul class="list-disc list-inside space-y-1">
        <li v-for="error in errors" :key="error">{{ error }}</li>
      </ul>
    </div>

    <!-- Formulaire -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <form @submit.prevent="submitForm" class="divide-y divide-gray-200">
        
        <!-- Section CLIENT -->
        <div class="p-6 space-y-6">
          <div class="flex items-center gap-3 pb-4">
            <div class="p-2 bg-blue-100 rounded-lg">
              <User class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">CLIENT</h2>
              <p class="text-sm text-gray-500">Informations du client et facturation</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Sélection du client -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Sélection du client <span class="text-red-500">*</span>
                </label>
                <ClientSelectorDual
                  :model-value="selectedClient"
                  @update:model-value="onClientSelected"
                  @client-selected="onClientSelected"
                  @add-client="onAddClient"
                  :options="{
                    showCreateButton: true,
                    placeholder: 'Choisir un client dans la liste',
                    searchPlaceholder: 'Rechercher un client existant...',
                    maxResults: 10
                  }"
                />
              </div>
            </div>

            <!-- Adresse de facturation -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-gray-900">Adresse de facturation</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Adresse <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.client_adresse"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Numéro et nom de rue"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Complément d'adresse
                </label>
                <input
                  v-model="form.client_complement_adresse"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Bâtiment, étage, appartement..."
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Code postal <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.client_code_postal"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="62000"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ville <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.client_ville"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Arras"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section INTERVENTION -->
        <div class="p-6 space-y-6">
          <div class="flex items-center gap-3 pb-4">
            <div class="p-2 bg-green-100 rounded-lg">
              <MapPin class="h-6 w-6 text-green-600" />
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">INTERVENTION</h2>
              <p class="text-sm text-gray-500">Détails de l'intervention et des travaux</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Date d'intervention <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.date_intervention"
                type="date"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                N° dossier
                <button type="button" class="ml-1 text-gray-400 hover:text-gray-600" title="Numéro de dossier interne">
                  <HelpCircle class="h-4 w-4 inline" />
                </button>
              </label>
              <input
                v-model="form.numero_dossier"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Ex: 3329"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Temps total passé <span class="text-red-500">*</span>
                <Clock class="h-4 w-4 inline ml-1 text-gray-400" />
              </label>
              <input
                v-model="form.temps_total_passe"
                type="text"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Ex: 8h30"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Lieu de l'intervention <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.lieu_intervention"
                type="text"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Adresse complète du lieu d'intervention"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Type d'ouvrage <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.designation_travaux"
                type="text"
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Description générale des travaux"
              />
            </div>
          </div>
        </div>

        <!-- Section FOURNITURES ET TRAVAUX -->
        <div class="p-6 space-y-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-purple-100 rounded-lg">
                <Building2 class="h-6 w-6 text-purple-600" />
              </div>
              <div>
                <h2 class="text-xl font-semibold text-gray-900">FOURNITURES ET TRAVAUX EXÉCUTÉS</h2>
                <p class="text-sm text-gray-500">Détail des travaux réalisés</p>
              </div>
            </div>
            
            <button
              type="button"
              @click="ajouterLigne"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              <Plus class="h-4 w-4 mr-2" />
              Ajouter une ligne
            </button>
          </div>

          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 border-b border-gray-200">
                    Désignation des travaux
                  </th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 border-b border-gray-200 w-32">
                    Quantités
                  </th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 border-b border-gray-200 w-48">
                    Observations
                  </th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 border-b border-gray-200 w-16">
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(ligne, index) in form.fournitures_travaux" :key="index" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <textarea
                      v-model="ligne.designation"
                      rows="2"
                      required
                      class="w-full border-0 resize-none focus:ring-2 focus:ring-blue-500 rounded text-sm"
                      placeholder="Description détaillée des travaux..."
                    ></textarea>
                  </td>
                  <td class="px-4 py-3">
                    <input
                      v-model="ligne.quantite"
                      type="text"
                      required
                      class="w-full border-0 text-center focus:ring-2 focus:ring-blue-500 rounded text-sm"
                      placeholder="Qté"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <textarea
                      v-model="ligne.observations"
                      rows="2"
                      class="w-full border-0 resize-none focus:ring-2 focus:ring-blue-500 rounded text-sm"
                      placeholder="Observations éventuelles..."
                    ></textarea>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button
                      v-if="form.fournitures_travaux.length > 1"
                      type="button"
                      @click="supprimerLigne(index)"
                      class="text-red-600 hover:text-red-800 p-1"
                      title="Supprimer cette ligne"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Section SIGNATURES -->
        <div class="p-6 space-y-6">
          <div class="flex items-center gap-3 pb-4">
            <div class="p-2 bg-orange-100 rounded-lg">
              <FileText class="h-6 w-6 text-orange-600" />
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">SIGNATURES</h2>
              <p class="text-sm text-gray-500">Signatures numériques requises</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Signature entreprise -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-gray-900">Signature de l'entreprise</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Date
                </label>
                <input
                  type="date"
                  :value="currentDate"
                  readonly
                  class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nom du représentant
                </label>
                <input
                  type="text"
                  placeholder="Nom du représentant de l'entreprise"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Signature <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-md p-2 bg-gray-50">
                  <canvas
                    ref="signatureEntrepriseCanvas"
                    width="400"
                    height="120"
                    class="w-full bg-white rounded cursor-crosshair"
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

            <!-- Signature client -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-gray-900">Signature du client</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Date
                </label>
                <input
                  type="date"
                  :value="currentDate"
                  readonly
                  class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nom du client (référence)
                </label>
                <input
                  v-model="form.client_nom"
                  type="text"
                  readonly
                  class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nom de la personne qui signe <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.nom_signataire_client"
                  type="text"
                  required
                  placeholder="Nom de la personne qui signe"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  :class="{
                    'border-red-300 focus:ring-red-500': form.errors.nom_signataire_client
                  }"
                />
                <div v-if="form.errors.nom_signataire_client" class="mt-1 text-sm text-red-600">
                  {{ form.errors.nom_signataire_client }}
                </div>
                <p class="mt-1 text-xs text-gray-500">
                  Cette personne peut être différente du client principal
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Signature <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-md p-2 bg-gray-50">
                  <canvas
                    ref="signatureClientCanvas"
                    width="400"
                    height="120"
                    class="w-full bg-white rounded cursor-crosshair"
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

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
          <button
            type="button"
            @click="generatePDFAndPrint"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
          >
            <FileText class="h-4 w-4 mr-2" />
            Aperçu/Imprimer
          </button>

          <div class="flex gap-3">
            <Link
              :href="route('dashboard')"
              class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Annuler
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 font-semibold text-lg shadow-lg"
            >
              <Save class="h-5 w-5 mr-2" />
              <span v-if="form.processing">Sauvegarde...</span>
              <span v-else>Sauvegarder</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Toast -->
    <Toast
      :show="showToast"
      :type="toastType"
      :message="toastMessage"
      @close="showToast = false"
    />
  </div>
</template>

<style scoped>
canvas {
  touch-action: none;
}

@media print {
  .no-print {
    display: none !important;
  }
}

/* Responsive adaptations */
@media (max-width: 768px) {
  .grid-cols-1.lg\\:grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .grid-cols-1.lg\\:grid-cols-3 {
    grid-template-columns: 1fr;
  }
  
  .px-8 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
  
  .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }
}
</style>