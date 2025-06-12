<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Search, Plus, Check } from 'lucide-vue-next'

interface Client {
  id: number
  nom: string
  email: string
  adresse: string
  complement_adresse?: string
  code_postal: string
  ville: string
}

interface Props {
  modelValue?: Client | null
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Rechercher ou sélectionner un client'
})

const emit = defineEmits<{
  'update:modelValue': [value: Client | null]
  'add-client': [clientData: Partial<Client>]
}>()

const searchTerm = ref('')
const isOpen = ref(false)
const clients = ref<Client[]>([])
const loading = ref(false)

const selectedClient = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const filteredClients = computed(() => {
  if (!searchTerm.value) return clients.value
  
  return clients.value.filter(client =>
    client.nom.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    client.email.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    client.ville.toLowerCase().includes(searchTerm.value.toLowerCase())
  )
})

const displayValue = computed(() => {
  if (selectedClient.value) {
    return selectedClient.value.nom
  }
  return searchTerm.value
})

const searchClients = async () => {
  if (searchTerm.value.length < 2) {
    clients.value = []
    return
  }

  loading.value = true
  try {
    const response = await fetch(`/api/clients?search=${encodeURIComponent(searchTerm.value)}`)
    const data = await response.json()
    clients.value = data
  } catch (error) {
    console.error('Erreur lors de la recherche de clients:', error)
    clients.value = []
  } finally {
    loading.value = false
  }
}

const selectClient = (client: Client) => {
  selectedClient.value = client
  searchTerm.value = client.nom
  isOpen.value = false
}

const clearSelection = () => {
  selectedClient.value = null
  searchTerm.value = ''
  clients.value = []
  isOpen.value = false
}

const addNewClient = () => {
  const [nom, ...rest] = searchTerm.value.split(' ')
  emit('add-client', {
    nom: searchTerm.value,
    email: '',
    adresse: '',
    code_postal: '',
    ville: ''
  })
}

watch(searchTerm, (newValue) => {
  if (newValue !== selectedClient.value?.nom) {
    selectedClient.value = null
  }
  searchClients()
}, { debounce: 300 })

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    searchTerm.value = newValue.nom
  }
}, { immediate: true })
</script>

<template>
  <div class="relative">
    <div class="relative">
      <input
        v-model="searchTerm"
        type="text"
        :placeholder="placeholder"
        class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        @focus="isOpen = true"
        @blur="setTimeout(() => isOpen = false, 200)"
      />
      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <Search v-if="!selectedClient" class="h-4 w-4 text-gray-400" />
        <Check v-else class="h-4 w-4 text-green-500" />
      </div>
    </div>

    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen && (filteredClients.length > 0 || searchTerm.length > 0)"
        class="absolute z-10 mt-1 w-full bg-white rounded-md border border-gray-300 shadow-lg max-h-60 overflow-auto"
      >
        <!-- Loading -->
        <div v-if="loading" class="px-4 py-2 text-sm text-gray-500">
          Recherche en cours...
        </div>

        <!-- Clients trouvés -->
        <div v-else-if="filteredClients.length > 0">
          <button
            v-for="client in filteredClients"
            :key="client.id"
            type="button"
            class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0"
            @click="selectClient(client)"
          >
            <div class="font-medium text-gray-900">{{ client.nom }}</div>
            <div class="text-sm text-gray-500">{{ client.email }}</div>
            <div class="text-xs text-gray-400">
              {{ client.adresse }}, {{ client.code_postal }} {{ client.ville }}
            </div>
          </button>
        </div>

        <!-- Ajouter un nouveau client -->
        <div v-else-if="searchTerm.length > 2" class="p-2">
          <button
            type="button"
            class="w-full px-3 py-2 text-left text-blue-600 hover:bg-blue-50 rounded-md flex items-center"
            @click="addNewClient"
          >
            <Plus class="h-4 w-4 mr-2" />
            Ajouter "{{ searchTerm }}" comme nouveau client
          </button>
        </div>

        <!-- Aucun résultat -->
        <div v-else class="px-4 py-2 text-sm text-gray-500">
          Aucun client trouvé
        </div>
      </div>
    </Transition>

    <!-- Client sélectionné -->
    <div v-if="selectedClient" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-md">
      <div class="flex justify-between items-start">
        <div>
          <div class="font-medium text-green-800">{{ selectedClient.nom }}</div>
          <div class="text-sm text-green-600">{{ selectedClient.email }}</div>
          <div class="text-xs text-green-600">
            {{ selectedClient.adresse }}
            <span v-if="selectedClient.complement_adresse">, {{ selectedClient.complement_adresse }}</span>
            <br>{{ selectedClient.code_postal }} {{ selectedClient.ville }}
          </div>
        </div>
        <button
          type="button"
          @click="clearSelection"
          class="text-green-600 hover:text-green-800"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>