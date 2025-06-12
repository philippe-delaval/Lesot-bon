<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { CheckCircle, XCircle, AlertCircle, Info, X } from 'lucide-vue-next'

interface Props {
  show: boolean
  type?: 'success' | 'error' | 'warning' | 'info'
  title?: string
  message: string
  autoClose?: boolean
  duration?: number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  autoClose: true,
  duration: 5000,
})

const emit = defineEmits<{
  close: []
}>()

const visible = ref(false)

const iconMap = {
  success: CheckCircle,
  error: XCircle,
  warning: AlertCircle,
  info: Info,
}

const colorMap = {
  success: {
    bg: 'bg-green-50',
    border: 'border-green-200',
    icon: 'text-green-400',
    title: 'text-green-800',
    message: 'text-green-700',
    button: 'text-green-500 hover:text-green-600',
  },
  error: {
    bg: 'bg-red-50',
    border: 'border-red-200',
    icon: 'text-red-400',
    title: 'text-red-800',
    message: 'text-red-700',
    button: 'text-red-500 hover:text-red-600',
  },
  warning: {
    bg: 'bg-yellow-50',
    border: 'border-yellow-200',
    icon: 'text-yellow-400',
    title: 'text-yellow-800',
    message: 'text-yellow-700',
    button: 'text-yellow-500 hover:text-yellow-600',
  },
  info: {
    bg: 'bg-blue-50',
    border: 'border-blue-200',
    icon: 'text-blue-400',
    title: 'text-blue-800',
    message: 'text-blue-700',
    button: 'text-blue-500 hover:text-blue-600',
  },
}

const close = () => {
  visible.value = false
  setTimeout(() => emit('close'), 300)
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    visible.value = true
    if (props.autoClose) {
      setTimeout(close, props.duration)
    }
  } else {
    visible.value = false
  }
}, { immediate: true })

onMounted(() => {
  if (props.show && props.autoClose) {
    setTimeout(close, props.duration)
  }
})
</script>

<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="visible"
      :class="[
        'fixed top-4 right-4 max-w-sm w-full rounded-lg border p-4 shadow-lg z-50',
        colorMap[type].bg,
        colorMap[type].border
      ]"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <component
            :is="iconMap[type]"
            :class="['h-5 w-5', colorMap[type].icon]"
          />
        </div>
        <div class="ml-3 w-0 flex-1">
          <p
            v-if="title"
            :class="['text-sm font-medium', colorMap[type].title]"
          >
            {{ title }}
          </p>
          <p
            :class="[
              'text-sm',
              colorMap[type].message,
              title ? 'mt-1' : ''
            ]"
          >
            {{ message }}
          </p>
        </div>
        <div class="ml-4 flex flex-shrink-0">
          <button
            @click="close"
            :class="[
              'inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
              colorMap[type].button
            ]"
          >
            <span class="sr-only">Fermer</span>
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>