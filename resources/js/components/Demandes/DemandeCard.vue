<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { Calendar, MapPin, User, Clock } from 'lucide-vue-next';
import { type Demande } from '@/types/index.d';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StatutBadge from './StatutBadge.vue';
import PrioriteBadge from './PrioriteBadge.vue';

interface Props {
  demande: Demande;
}

const props = defineProps<Props>();

const formattedDate = computed(() => 
  format(new Date(props.demande.date_demande), 'dd MMM yyyy', { locale: fr })
);

const formattedDateSouhaitee = computed(() => 
  props.demande.date_souhaite_intervention
    ? format(new Date(props.demande.date_souhaite_intervention), 'dd MMM yyyy', { locale: fr })
    : null
);

const timeAgo = computed(() => {
  const now = new Date();
  const created = new Date(props.demande.created_at);
  const diffInHours = Math.floor((now.getTime() - created.getTime()) / (1000 * 60 * 60));
  
  if (diffInHours < 1) return 'À l\'instant';
  if (diffInHours < 24) return `Il y a ${diffInHours}h`;
  
  const diffInDays = Math.floor(diffInHours / 24);
  return `Il y a ${diffInDays}j`;
});
</script>

<template>
  <Card class="hover:shadow-md transition-shadow">
    <CardHeader class="pb-3">
      <div class="flex items-start justify-between">
        <div class="space-y-1">
          <CardTitle class="text-lg">
            <Link 
              :href="route('demandes.show', demande.id)"
              class="text-blue-600 hover:text-blue-800 transition-colors"
            >
              {{ demande.titre }}
            </Link>
          </CardTitle>
          <p class="text-sm text-muted-foreground">{{ demande.numero_demande }}</p>
        </div>
        <div class="flex gap-2">
          <PrioriteBadge :priorite="demande.priorite" />
          <StatutBadge :statut="demande.statut" />
        </div>
      </div>
    </CardHeader>

    <CardContent class="space-y-4">
      <p class="text-sm text-gray-600 line-clamp-2">
        {{ demande.description }}
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-500">
        <div class="flex items-center gap-2">
          <User class="h-4 w-4" />
          <span>{{ demande.createur?.name }}</span>
        </div>
        
        <div v-if="demande.receveur" class="flex items-center gap-2">
          <User class="h-4 w-4 text-blue-500" />
          <span>{{ demande.receveur.name }}</span>
        </div>
        
        <div v-if="demande.client" class="flex items-center gap-2">
          <MapPin class="h-4 w-4" />
          <span>{{ demande.client.nom }}</span>
        </div>
        
        <div v-if="demande.lieu_intervention" class="flex items-center gap-2">
          <MapPin class="h-4 w-4" />
          <span>{{ demande.lieu_intervention }}</span>
        </div>
      </div>

      <div class="flex items-center justify-between text-sm text-gray-500 pt-2 border-t">
        <div class="flex items-center gap-2">
          <Calendar class="h-4 w-4" />
          <span>{{ formattedDate }}</span>
        </div>
        
        <div v-if="formattedDateSouhaitee" class="flex items-center gap-2">
          <Clock class="h-4 w-4" />
          <span>Souhaité: {{ formattedDateSouhaitee }}</span>
        </div>
        
        <span class="text-xs">{{ timeAgo }}</span>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>