<script setup lang="ts">
import { computed } from 'vue';
import { StatutLabels, StatutColors, type Demande } from '@/types/index.d';
import { Badge } from '@/components/ui/badge';

interface Props {
  statut: Demande['statut'];
}

const props = defineProps<Props>();

const label = computed(() => StatutLabels[props.statut]);
const className = computed(() => StatutColors[props.statut]);
</script>

<template>
  <Badge :class="className">
    {{ label }}
  </Badge>
</template>