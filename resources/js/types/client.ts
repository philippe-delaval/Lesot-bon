export interface Client {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string | null
  code_postal: string
  ville: string
  telephone?: string | null
  notes?: string | null
  created_at?: string
  updated_at?: string
}

export interface ClientFormData {
  nom: string
  email: string
  adresse: string
  complement_adresse?: string
  code_postal: string
  ville: string
  telephone?: string
  notes?: string
}

export interface ClientSearchResult {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string | null
  code_postal: string
  ville: string
}

export interface ClientSelectOptions {
  showCreateButton?: boolean
  placeholder?: string
  searchPlaceholder?: string
  maxResults?: number
}

export interface ClientSelectionState {
  selectedClient: Client | null
  searchTerm: string
  isSearchFocused: boolean
  loading: boolean
  error: string | null
}