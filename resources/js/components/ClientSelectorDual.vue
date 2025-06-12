<script setup lang="ts">
import { computed, watch, onMounted, nextTick } from 'vue'
import { ChevronDown, Search, Check, Plus, User, X } from 'lucide-vue-next'
import { useClients } from '@/composables/useClients'
import type { Client, ClientSelectOptions } from '@/types/client'

interface Props {
  modelValue?: Client | null
  options?: ClientSelectOptions
}

interface Emits {
  (e: 'update:modelValue', value: Client | null): void
  (e: 'client-selected', client: Client): void
  (e: 'add-client', clientData: { nom: string }): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  options: () => ({
    showCreateButton: true,
    placeholder: 'Choisir un client dans la liste',
    searchPlaceholder: 'Rechercher un client existant...',
    maxResults: 10
  })
})

const emit = defineEmits<Emits>()

// Utiliser le composable
const {
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
  hasSelection,
  canSearch,
  hasSearchResults,
  displayValue,
  fetchAllClients,
  selectFromDropdown,
  selectFromSearch,
  clearSelection,
  openDropdown,
  openSearch,
  closeDropdowns
} = useClients({ 
  immediate: true, 
  maxResults: props.options?.maxResults || 10 
})

// Computed pour les options
const computedOptions = computed(() => ({
  showCreateButton: true,
  placeholder: 'Choisir un client dans la liste',
  searchPlaceholder: 'Rechercher un client existant...',
  maxResults: 10,
  ...props.options
}))

// Computed pour la valeur sélectionnée
const currentValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value)
    if (value) {
      emit('client-selected', value)
    }
  }
})

// Actions
const handleDropdownSelect = (client: Client) => {
  selectFromDropdown(client)
  currentValue.value = client
}

const handleSearchSelect = (client: Client) => {
  selectFromSearch(client)
  currentValue.value = client
}

const handleClear = () => {
  clearSelection()
  currentValue.value = null
}

const handleAddClient = () => {
  if (searchTerm.value.trim()) {
    emit('add-client', { nom: searchTerm.value.trim() })
  }
}

const focusSearchInput = async () => {
  await nextTick()
  searchInputRef.value?.focus()
}

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue && newValue !== selectedClient.value) {
    selectedClient.value = newValue
    if (newValue.nom) {
      searchTerm.value = newValue.nom
    }
  } else if (!newValue && selectedClient.value) {
    clearSelection()
  }
}, { immediate: true })

watch(selectedClient, (newValue) => {
  if (newValue !== currentValue.value) {
    currentValue.value = newValue
  }
})

onMounted(() => {
  if (props.modelValue) {
    selectedClient.value = props.modelValue
  }
})
</script>

<template>
  <div class="space-y-4">
    <!-- Section Client Sélectionné (si il y en a un) -->
    <div v-if="hasSelection" class="p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-start justify-between">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <User class="w-5 h-5 text-green-600" />
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-sm font-medium text-green-900">
              {{ selectedClient?.nom }}
            </h3>
            <p class="text-sm text-green-700">
              {{ selectedClient?.email }}
            </p>
            <p class="text-xs text-green-600 mt-1">
              {{ selectedClient?.adresse }}
              <span v-if="selectedClient?.complement_adresse">, {{ selectedClient.complement_adresse }}</span>
              <br>
              {{ selectedClient?.code_postal }} {{ selectedClient?.ville }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="handleClear"
          class="flex-shrink-0 text-green-600 hover:text-green-800 transition-colors"
          title="Supprimer la sélection"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Section de Sélection (si aucun client sélectionné) -->
    <div v-else class="space-y-4">
      <!-- 1. Liste Déroulante -->
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Sélection rapide
        </label>
        <div class="relative">
          <button
            type="button"
            @click="openDropdown"
            @blur="closeDropdowns"
            class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 text-left focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
            :class="{
              'border-blue-500 ring-1 ring-blue-500': isDropdownOpen,
              'text-gray-500': !dropdownSelection,
              'text-gray-900': dropdownSelection
            }"
          >
            <span v-if="dropdownSelection" class="flex items-center">
              <User class="w-4 h-4 text-gray-400 mr-2" />
              {{ dropdownSelection.nom }}
            </span>
            <span v-else class="flex items-center text-gray-500">
              <User class="w-4 h-4 text-gray-400 mr-2" />
              {{ computedOptions.placeholder }}
            </span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
              <ChevronDown 
                class="w-4 h-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': isDropdownOpen }"
              />
            </span>
          </button>

          <!-- Dropdown Menu -->
          <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="isDropdownOpen"
              class="absolute z-20 mt-1 w-full bg-white rounded-md border border-gray-300 shadow-lg max-h-60 overflow-auto"
            >
              <div v-if="loading" class="px-4 py-3 text-sm text-gray-500 text-center">
                <div class="flex items-center justify-center">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
                  Chargement...
                </div>
              </div>
              
              <div v-else-if="allClients.length > 0" class="divide-y divide-gray-100">
                <button
                  v-for="client in allClients"
                  :key="client.id"
                  type="button"
                  @mousedown.prevent="handleDropdownSelect(client)"
                  class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors"
                >
                  <div class="flex items-center">
                    <User class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-gray-900 truncate">{{ client.nom }}</div>
                      <div class="text-sm text-gray-500 truncate">{{ client.email }}</div>
                      <div class="text-xs text-gray-400 truncate">
                        {{ client.ville }}
                      </div>
                    </div>
                  </div>
                </button>
              </div>
              
              <div v-else class="px-4 py-3 text-sm text-gray-500 text-center">
                Aucun client disponible
              </div>
            </div>
          </Transition>
        </div>
      </div>

      <!-- Séparateur "OU" -->
      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-4 bg-white text-gray-500 font-medium">OU</span>
        </div>
      </div>

      <!-- 2. Recherche Dynamique -->
      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Recherche avancée
        </label>
        <div class="relative">
          <input
            ref="searchInputRef"
            v-model="searchTerm"
            type="text"
            :placeholder="computedOptions.searchPlaceholder"
            @focus="openSearch"
            @blur="closeDropdowns"
            class="w-full border border-gray-300 rounded-md pl-10 pr-10 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
            :class="{
              'border-blue-500 ring-1 ring-blue-500': isSearchOpen
            }"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <Search class="w-4 h-4 text-gray-400" />
          </div>
          <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
            <Check v-else-if="selectedClient && searchTerm" class="w-4 h-4 text-green-500" />
          </div>

          <!-- Résultats de recherche -->
          <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="isSearchOpen && (hasSearchResults || canSearch || searchTerm.length > 0)"
              class="absolute z-20 mt-1 w-full bg-white rounded-md border border-gray-300 shadow-lg max-h-60 overflow-auto"
            >
              <!-- Loading -->
              <div v-if="loading && canSearch" class="px-4 py-3 text-sm text-gray-500 text-center">
                <div class="flex items-center justify-center">
                  <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
                  Recherche en cours...
                </div>
              </div>

              <!-- Résultats trouvés -->
              <div v-else-if="hasSearchResults" class="divide-y divide-gray-100">
                <button
                  v-for="client in searchResults"
                  :key="client.id"
                  type="button"
                  @mousedown.prevent="handleSearchSelect(client)"
                  class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors"
                >
                  <div class="flex items-center">
                    <User class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-gray-900 truncate">{{ client.nom }}</div>
                      <div class="text-sm text-gray-500 truncate">{{ client.email }}</div>
                      <div class="text-xs text-gray-400 truncate">
                        {{ client.adresse }}, {{ client.code_postal }} {{ client.ville }}
                      </div>
                    </div>
                  </div>
                </button>
              </div>

              <!-- Ajouter un nouveau client -->
              <div 
                v-else-if="computedOptions.showCreateButton && searchTerm.length > 2" 
                class="p-2"
              >
                <button
                  type="button"
                  @mousedown.prevent="handleAddClient"
                  class="w-full px-3 py-2 text-left text-blue-600 hover:bg-blue-50 rounded-md flex items-center transition-colors"
                >
                  <Plus class="w-4 h-4 mr-2 flex-shrink-0" />
                  <span class="truncate">Ajouter "{{ searchTerm }}" comme nouveau client</span>
                </button>
              </div>

              <!-- Message d'aide -->
              <div v-else-if="searchTerm.length > 0 && searchTerm.length < 2" class="px-4 py-3 text-sm text-gray-500 text-center">
                Tapez au moins 2 caractères pour rechercher
              </div>

              <!-- Aucun résultat -->
              <div v-else-if="searchTerm.length >= 2" class="px-4 py-3 text-sm text-gray-500 text-center">
                Aucun client trouvé pour "{{ searchTerm }}"
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- Message d'erreur -->
    <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-md">
      <p class="text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<style scoped>
/* Animations pour les transitions */
.v-enter-active,
.v-leave-active {
  transition: all 0.3s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>