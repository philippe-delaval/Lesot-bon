<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import AttachementForm from '@/components/AttachementForm.vue'
import AttachementsList from '@/components/AttachementsList.vue'
import AttachementViewer from '@/components/AttachementViewer.vue'
import { FileText, Plus, List } from 'lucide-vue-next'

const activeTab = ref('overview')
const viewingAttachementId = ref<number | null>(null)

const handleCancel = () => {
  activeTab.value = 'overview'
}

const handleSuccess = () => {
  activeTab.value = 'list'
}
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />
    
    <div class="flex flex-1 flex-col gap-4 p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
      </div>
      
      <!-- Tabs -->
      <div class="w-full">
        <!-- Tab buttons -->
        <div class="flex border-b border-gray-200">
          <button
            @click="activeTab = 'overview'"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'overview'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <div class="flex items-center gap-2">
              <FileText class="w-4 h-4" />
              Vue d'ensemble
            </div>
          </button>
          
          <button
            @click="activeTab = 'create'"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'create'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <div class="flex items-center gap-2">
              <Plus class="w-4 h-4" />
              Nouvel Attachement
            </div>
          </button>
          
          <button
            @click="activeTab = 'list'"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'list'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            <div class="flex items-center gap-2">
              <List class="w-4 h-4" />
              Liste des Attachements
            </div>
          </button>
        </div>
        
        <!-- Tab content -->
        <div class="mt-6">
          <!-- Overview tab -->
          <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              <!-- Card 1 -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Attachements du jour</h3>
                <p class="text-xs text-gray-400 mt-1">Nombre d'attachements créés aujourd'hui</p>
                <div class="text-3xl font-bold mt-4">0</div>
              </div>
              
              <!-- Card 2 -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Cette semaine</h3>
                <p class="text-xs text-gray-400 mt-1">Total des attachements de la semaine</p>
                <div class="text-3xl font-bold mt-4">0</div>
              </div>
              
              <!-- Card 3 -->
              <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Ce mois</h3>
                <p class="text-xs text-gray-400 mt-1">Total des attachements du mois</p>
                <div class="text-3xl font-bold mt-4">0</div>
              </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
              <div class="flex gap-4">
                <button
                  @click="activeTab = 'create'"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  <Plus class="w-5 h-5 mr-2" />
                  Créer un nouvel attachement
                </button>
                
                <button
                  @click="activeTab = 'list'"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                  <List class="w-5 h-5 mr-2" />
                  Voir tous les attachements
                </button>
              </div>
            </div>
          </div>
          
          <!-- Create tab -->
          <div v-if="activeTab === 'create'">
            <AttachementForm 
              @cancel="handleCancel"
              @success="handleSuccess"
            />
          </div>
          
          <!-- List tab -->
          <div v-if="activeTab === 'list'">
            <AttachementsList @view="(id) => viewingAttachementId = id" />
          </div>
        </div>
      </div>
    </div>
    
    <!-- Viewer Modal -->
    <AttachementViewer 
      :attachement-id="viewingAttachementId"
      @close="viewingAttachementId = null"
    />
  </AppLayout>
</template>
