<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />
    
    <div class="flex flex-1 flex-col gap-4 p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">Vue d'ensemble</h1>
      </div>
      
      <div class="space-y-6">
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
      </div>
    </div>
  </AppLayout>
</template>
