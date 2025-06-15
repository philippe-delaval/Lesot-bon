<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Demandes', href: route('demandes.index') },
];
</script>

<template>
  <Head title="Test Demandes" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 xl:px-12 py-4 md:py-6 lg:py-8">
      <h1 class="text-2xl font-bold">Test - Page des demandes</h1>
      <p>Si vous voyez cette page, le problème vient des composants complexes.</p>
    </div>
  </AppLayout>
</template>