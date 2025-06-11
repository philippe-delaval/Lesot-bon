<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Attachement de Travaux</h1>
    
    <!-- Messages -->
    <div v-if="success" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
      {{ success }}
    </div>
    
    <div v-if="errors.length > 0" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
      <ul class="list-disc list-inside">
        <li v-for="error in errors" :key="error">{{ error }}</li>
      </ul>
    </div>
    
    <form ref="formRef" @submit.prevent="submitForm" class="space-y-8">
      <!-- Informations client -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Client</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nom du client <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.client_nom"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Email du client <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.client_email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Adresse de facturation <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.client_adresse_facturation"
              rows="3"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              N° dossier
            </label>
            <input
              v-model="form.numero_dossier"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>
      
      <!-- Détails intervention -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails de l'intervention</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Lieu de l'intervention <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.lieu_intervention"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.date_intervention"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Désignation des travaux <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.designation_travaux"
              rows="3"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
        </div>
      </div>
      
      <!-- Fournitures et travaux -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Fournitures et travaux exécutés</h3>
        
        <div class="space-y-3">
          <div
            v-for="(ligne, index) in form.fournitures_travaux"
            :key="index"
            class="bg-gray-50 p-4 rounded-md border border-gray-200"
          >
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
              <div class="md:col-span-5">
                <input
                  v-model="ligne.designation"
                  type="text"
                  placeholder="Désignation"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div class="md:col-span-2">
                <input
                  v-model="ligne.quantite"
                  type="number"
                  step="0.01"
                  placeholder="Quantité"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div class="md:col-span-4">
                <input
                  v-model="ligne.observations"
                  type="text"
                  placeholder="Observations"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div class="md:col-span-1 flex items-center">
                <button
                  v-if="form.fournitures_travaux.length > 1"
                  type="button"
                  @click="supprimerLigne(index)"
                  class="w-full px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <button
          type="button"
          @click="ajouterLigne"
          class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
        >
          Ajouter une ligne
        </button>
      </div>
      
      <!-- Temps total -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Temps total passé (en heures) <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.temps_total_passe"
          type="number"
          min="0.5"
          step="0.5"
          required
          class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      
      <!-- Signatures -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Signatures</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Signature Entreprise <span class="text-red-500">*</span>
            </label>
            <div class="border-2 border-gray-300 rounded-md">
              <canvas
                ref="signatureEntrepriseCanvas"
                width="400"
                height="200"
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
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Signature Client <span class="text-red-500">*</span>
            </label>
            <div class="border-2 border-gray-300 rounded-md">
              <canvas
                ref="signatureClientCanvas"
                width="400"
                height="200"
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
      
      <!-- Boutons de soumission -->
      <div class="flex justify-center gap-4">
        <button
          type="button"
          @click="emit('cancel')"
          class="px-8 py-3 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600 transition-colors"
        >
          Annuler
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Traitement en cours...</span>
          <span v-else>Valider et Envoyer</span>
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.attachement-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    background: #f8f9fa;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.fourniture-line {
    background: white;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.signature-pad {
    border: 2px solid #000;
    border-radius: 4px;
    background: white;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
}

.btn-primary:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-outline-secondary {
    border: 1px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: -5px;
}

.col-md-1, .col-md-2, .col-md-4, .col-md-5, .col-md-6 {
    padding: 5px;
}

.col-md-1 { flex: 0 0 8.333333%; }
.col-md-2 { flex: 0 0 16.666667%; }
.col-md-4 { flex: 0 0 33.333333%; }
.col-md-5 { flex: 0 0 41.666667%; }
.col-md-6 { flex: 0 0 50%; }

.text-center {
    text-align: center;
}

.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
</style>


<style scoped>

</style>
