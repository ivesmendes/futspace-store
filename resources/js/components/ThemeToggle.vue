<template>
  <button
    @click="toggle"
    class="btn btn-outline-secondary position-fixed top-0 end-0 m-3"
  >
    <span v-if="isDark">🌙</span>
    <span v-else>☀️</span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

// Função para aplicar/remover as classes do Bootstrap no body
function applyThemeClasses(isDarkTheme) {
  const body = document.body;
  if (isDarkTheme) {
    body.classList.remove('bg-light', 'text-dark');
    body.classList.add('bg-dark', 'text-light');
  } else {
    body.classList.remove('bg-dark', 'text-light');
    body.classList.add('bg-light', 'text-dark');
  }
}

// Lógica de alternância do tema
function toggle() {
  isDark.value = !isDark.value
  applyThemeClasses(isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

// Ao montar o componente, verifica o tema salvo no localStorage e aplica
onMounted(() => {
  const saved = localStorage.getItem('theme')
  isDark.value = saved === 'dark' // true se o tema salvo for 'dark'
  applyThemeClasses(isDark.value)
})
</script>