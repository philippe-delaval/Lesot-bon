export interface FournituresTravaux {
  designation: string
  quantite: string
  observations: string
}

export interface Geolocation {
  latitude: number | null
  longitude: number | null
  timestamp?: string | null
}

export interface AttachementFormData {
  // Informations client
  client_id: number | null
  client_nom: string
  nom_signataire_client: string
  client_email: string
  client_adresse: string
  client_complement_adresse: string
  client_code_postal: string
  client_ville: string

  // Informations de l'intervention
  numero_dossier: string
  lieu_intervention: string
  date_intervention: string
  designation_travaux: string

  // Fournitures et travaux
  fournitures_travaux: FournituresTravaux[]

  // Temps et signatures
  temps_total_passe: string | number
  signature_entreprise: string
  signature_client: string

  // Données optionnelles
  geolocation: Geolocation | null
  pdf: File | null
}

export interface Attachement {
  id: number
  client_id: number | null
  numero_dossier: string
  client_nom: string
  nom_signataire_client: string | null
  client_email: string
  client_adresse_facturation: string
  lieu_intervention: string
  date_intervention: string
  designation_travaux: string
  fournitures_travaux: FournituresTravaux[]
  temps_total_passe: number
  signature_entreprise_path: string
  signature_client_path: string
  pdf_path: string
  geolocation: Geolocation | null
  created_by: number
  created_at: string
  updated_at: string
  
  // Relations
  creator?: {
    id: number
    name: string
    email: string
  }
  client?: {
    id: number
    nom: string
    email: string
    adresse: string
    code_postal: string
    ville: string
  }

  // Computed attributes
  pdf_url?: string
  signature_entreprise_url?: string
  signature_client_url?: string
  formatted_geolocation?: string
}

export interface AttachementListItem {
  id: number
  numero_dossier: string
  client_nom: string
  nom_signataire_client: string | null
  lieu_intervention: string
  date_intervention: string
  created_at: string
  pdf_url: string
}

export interface AttachementOptions {
  autoGenerateNumero?: boolean
  requireGeolocation?: boolean
  maxFournituresLines?: number
  signatureFormat?: 'png' | 'jpeg' | 'svg'
}

export interface SignatureState {
  entreprise: {
    signed: boolean
    data: string | null
    timestamp: string | null
  }
  client: {
    signed: boolean
    data: string | null
    timestamp: string | null
    signataire_nom: string
  }
}

export interface AttachementValidationErrors {
  [key: string]: string[]
}