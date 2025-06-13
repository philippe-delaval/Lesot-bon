<script setup lang="ts">
import { computed } from 'vue';
import { SelectValue, type SelectValueProps } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props extends SelectValueProps {
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

<template>
  <SelectValue
    v-bind="delegatedProps"
    :class="cn('pointer-events-none', props.class)"
  />
</template>