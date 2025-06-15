<script setup lang="ts">
import { computed } from 'vue';
import {
  SelectRoot,
  type SelectRootEmits,
  type SelectRootProps,
} from 'radix-vue';

interface Props extends SelectRootProps {}

const props = defineProps<Props>();
const emits = defineEmits<SelectRootEmits>();

const delegatedProps = computed(() => {
  const { ...delegated } = props;
  return delegated;
});

const handleUpdateModelValue = (value: any) => {
  emits('update:modelValue', value);
};
</script>

<template>
  <SelectRoot v-bind="delegatedProps" @update:model-value="handleUpdateModelValue">
    <slot />
  </SelectRoot>
</template>