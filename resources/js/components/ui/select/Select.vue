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
</script>

<template>
  <SelectRoot v-bind="delegatedProps" v-on="emits">
    <slot />
  </SelectRoot>
</template>