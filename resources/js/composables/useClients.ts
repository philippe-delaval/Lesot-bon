import { ref, computed, watch, type Ref } from 'vue'
import type { Client, ClientSearchResult } from '@/types/client'

interface UseClientsOptions {
  immediate?: boolean
  debounceMs?: number
  maxResults?: number
}

export function useClients(options: UseClientsOptions = {}) {
  const {
    immediate = false,
    debounceMs = 300,
    maxResults = 10
  } = options

  // État réactif
  const allClients: Ref<Client[]> = ref([])
  const searchResults: Ref<ClientSearchResult[]> = ref([])
  const selectedClient: Ref<Client | null> = ref(null)
  const searchTerm = ref('')
  const dropdownSelection: Ref<Client | null> = ref(null)
  const loading = ref(false)
  const error: Ref<string | null> = ref(null)

  // États UI
  const isDropdownOpen = ref(false)
  const isSearchOpen = ref(false)
  const searchInputRef: Ref<HTMLInputElement | null> = ref(null)

  // Computed
  const hasSelection = computed(() => selectedClient.value !== null)
  const canSearch = computed(() => searchTerm.value.length >= 2)
  const hasSearchResults = computed(() => searchResults.value.length > 0)
  
  const displayValue = computed(() => {
    if (selectedClient.value) {
      return selectedClient.value.nom
    }
    return searchTerm.value
  })

  // Filtrage des clients pour la recherche locale (fallback)
  const filteredClients = computed(() => {
    if (!searchTerm.value || searchTerm.value.length < 2) {
      return []
    }
    
    const term = searchTerm.value.toLowerCase()
    return allClients.value
      .filter(client =>
        client.nom.toLowerCase().includes(term) ||
        client.email.toLowerCase().includes(term) ||
        client.ville.toLowerCase().includes(term)
      )
      .slice(0, maxResults)
  })

  // Actions
  const fetchAllClients = async (): Promise<void> => {
    try {
      loading.value = true
      error.value = null
      
      const response = await fetch('/test/api/clients', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      allClients.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors du chargement des clients'
      console.error('Erreur lors du chargement des clients:', err)
    } finally {
      loading.value = false
    }
  }

  const searchClients = async (term: string): Promise<void> => {
    if (!term || term.length < 2) {
      searchResults.value = []
      return
    }

    try {
      loading.value = true
      error.value = null
      
      const response = await fetch(`/test/api/clients/search?q=${encodeURIComponent(term)}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      searchResults.value = data.slice(0, maxResults)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la recherche'
      console.error('Erreur lors de la recherche de clients:', err)
      
      // Fallback sur la recherche locale
      searchResults.value = filteredClients.value
    } finally {
      loading.value = false
    }
  }

  const selectFromDropdown = (client: Client): void => {
    selectedClient.value = client
    dropdownSelection.value = client
    searchTerm.value = ''
    isDropdownOpen.value = false
    isSearchOpen.value = false
    error.value = null
  }

  const selectFromSearch = (client: Client): void => {
    selectedClient.value = client
    dropdownSelection.value = null
    searchTerm.value = client.nom
    isSearchOpen.value = false
    isDropdownOpen.value = false
    error.value = null
  }

  const clearSelection = (): void => {
    selectedClient.value = null
    dropdownSelection.value = null
    searchTerm.value = ''
    searchResults.value = []
    isDropdownOpen.value = false
    isSearchOpen.value = false
    error.value = null
  }

  const clearDropdownSelection = (): void => {
    dropdownSelection.value = null
    if (searchTerm.value && selectedClient.value?.nom !== searchTerm.value) {
      // L'utilisateur tape dans la recherche
      selectedClient.value = null
    }
  }

  const clearSearchSelection = (): void => {
    if (dropdownSelection.value) {
      // Il y a une sélection dans le dropdown, on la garde
      selectedClient.value = dropdownSelection.value
      searchTerm.value = ''
    } else {
      selectedClient.value = null
    }
    searchResults.value = []
  }

  const openDropdown = (): void => {
    isDropdownOpen.value = true
    isSearchOpen.value = false
    if (allClients.value.length === 0) {
      fetchAllClients()
    }
  }

  const openSearch = (): void => {
    isSearchOpen.value = true
    isDropdownOpen.value = false
    
    // Déclencher la recherche si il y a déjà du texte
    if (searchTerm.value.length >= 2) {
      searchClients(searchTerm.value)
    }
  }

  const closeDropdowns = (): void => {
    setTimeout(() => {
      isDropdownOpen.value = false
      isSearchOpen.value = false
    }, 200) // Délai pour permettre les clics
  }

  // Debounced search
  let searchTimeout: NodeJS.Timeout | null = null
  const debouncedSearch = (term: string): void => {
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
    
    searchTimeout = setTimeout(() => {
      searchClients(term)
    }, debounceMs)
  }

  // Watchers
  watch(searchTerm, (newValue, oldValue) => {
    // Si l'utilisateur tape dans la recherche, vider la sélection dropdown
    if (newValue !== oldValue && newValue !== selectedClient.value?.nom) {
      clearDropdownSelection()
    }
    
    // Déclencher la recherche avec debounce
    if (newValue && newValue.length >= 2) {
      debouncedSearch(newValue)
    } else {
      searchResults.value = []
    }
  })

  watch(dropdownSelection, (newValue) => {
    // Si une sélection dropdown est faite, vider la recherche
    if (newValue) {
      searchTerm.value = ''
      searchResults.value = []
    }
  })

  // Charger les clients au montage si immediate = true
  if (immediate) {
    fetchAllClients()
  }

  return {
    // État
    allClients,
    searchResults,
    selectedClient,
    searchTerm,
    dropdownSelection,
    loading,
    error,
    isDropdownOpen,
    isSearchOpen,
    searchInputRef,

    // Computed
    hasSelection,
    canSearch,
    hasSearchResults,
    displayValue,
    filteredClients,

    // Actions
    fetchAllClients,
    searchClients,
    selectFromDropdown,
    selectFromSearch,
    clearSelection,
    clearDropdownSelection,
    clearSearchSelection,
    openDropdown,
    openSearch,
    closeDropdowns
  }
}