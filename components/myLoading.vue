<!-- components/LoadingScreen.vue -->
<script setup>
import { onMounted, ref, watch } from "vue";

const props = defineProps({
  show: { type: Boolean, default: null } // null = gestione automatica
});

const visible = ref(true);

onMounted(() => {
  if (props.show === null) {
    // gestione automatica → sparisce quando il componente è montato
    visible.value = false;
  } else {
    // gestione manuale → resta finché show non diventa false
    visible.value = props.show;
  }
});

watch(() => props.show, (val) => {
  if (props.show !== null) {
    visible.value = val;
  }
});
</script>

<template>
  <transition name="fade">
    <div v-if="visible" class="loading-overlay">
      <div class="spinner"></div>
    </div>
  </transition>
</template>

<style scoped>
.loading-overlay {
  position: fixed;
  inset: 0;
  background: white;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #800000;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

/* Fade in/out */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
