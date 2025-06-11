<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/core'
import { onMounted } from 'vue'
import AppShell from '@/components/AppShell.vue'
import AttachementForm from '@/components/AttachementForm.vue'

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
  <AppShell>
    <Head title="Nouvel Attachement" />
    
    <div class="container mx-auto py-6">
      <AttachementForm />
    </div>
  </AppShell>
</template> 