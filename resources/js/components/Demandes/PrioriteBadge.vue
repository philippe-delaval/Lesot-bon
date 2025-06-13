<script setup lang="ts">
import { computed } from 'vue';
import { PrioriteLabels, PrioriteColors, type Demande } from '@/types/index.d';
import { Badge } from '@/components/ui/badge';

interface Props {
  priorite: Demande['priorite'];
}

const props = defineProps<Props>();

const label = computed(() => PrioriteLabels[props.priorite]);
const className = computed(() => PrioriteColors[props.priorite]);
</script>

<template>
  <Badge :class="className">
    {{ label }}
  </Badge>
</template>