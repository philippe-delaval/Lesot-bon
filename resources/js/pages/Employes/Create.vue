<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, User } from 'lucide-vue-next'

interface Props {
  // Props pour la création - on les ajoutera plus tard si nécessaire
}

const props = defineProps<Props>()
</script>

<template>
  <AppLayout>
    <Head title="Créer un employé" />
    
    <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6 lg:py-8">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
          <Link
            :href="route('employes.index')"
            class="flex items-center gap-2 text-gray-600 hover:text-gray-900"
          >
            <ArrowLeft class="w-5 h-5" />
            Retour aux employés
          </Link>
        </div>

        <!-- Titre -->
        <div class="flex items-center gap-3">
          <div class="bg-blue-100 p-2 rounded-lg">
            <User class="h-6 w-6 text-blue-600" />
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">Créer un employé</h1>
            <p class="text-sm text-gray-500">Ajouter un nouvel employé</p>
          </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="text-center py-12 text-gray-500">
            <User class="w-16 h-16 mx-auto mb-4 text-gray-300" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">Formulaire en cours de développement</h3>
            <p>Cette fonctionnalité sera bientôt disponible.</p>
            <Link
              :href="route('employes.index')"
              class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Retour à la liste
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>