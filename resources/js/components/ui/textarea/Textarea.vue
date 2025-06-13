<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  class?: string;
  id?: string;
  placeholder?: string;
  rows?: number;
  modelValue?: string;
}

const props = withDefaults(defineProps<Props>(), {
  rows: 3,
});

const emits = defineEmits<{
  'update:modelValue': [value: string];
}>();

const textareaClass = computed(() =>
  cn(
    'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.class
  )
);

const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
  emits('update:modelValue', target.value);
};
</script>

<template>
  <textarea
    :id="id"
    :class="textareaClass"
    :placeholder="placeholder"
    :rows="rows"
    :value="modelValue"
    @input="handleInput"
  />
</template>