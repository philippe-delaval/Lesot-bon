<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/core'
import { onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import AttachementFormImproved from '@/components/AttachementFormImproved.vue'

// Vérifier l'authentification
onMounted(() => {
  const page = usePage()
  if (!page.props.auth?.user) {
    // Rediriger vers login si non connecté
    router.visit('/login')
  }
})
</script>

<template>
  <AppLayout>
    <Head title="Nouvel Attachement" />
    
    <AttachementFormImproved />
  </AppLayout>
</template> 