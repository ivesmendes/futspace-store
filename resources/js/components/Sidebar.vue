<template>
  <nav class="sidebar bg-dark text-white p-3 d-flex flex-column" style="width: 250px; flex-shrink: 0;">
    <h4 class="text-center mb-4 border-bottom pb-3">Menu</h4>
    <ul class="nav flex-column flex-grow-1">
      <li class="nav-item mb-2">
        <router-link to="/" class="nav-link text-white d-flex align-items-center">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </router-link>
      </li>
      <li class="nav-item mb-2">
        <router-link to="/clients" class="nav-link text-white d-flex align-items-center">
          <i class="bi bi-people me-2"></i> Clientes
        </router-link>
      </li>
      <li class="nav-item mb-2">
        <router-link to="/stock" class="nav-link text-white d-flex align-items-center">
          <i class="bi bi-box-seam me-2"></i> Estoque
        </router-link>
      </li>
      <li class="nav-item mb-2">
        <router-link to="/championship" class="nav-link text-white d-flex align-items-center">
          <i class="bi bi-trophy me-2"></i>
            ChampionShip
        </router-link>
      </li>
    </ul>
    <div class="mt-auto pt-3 border-top text-center">
        <ThemeToggle />
    </div>
  </nav>
</template>

<script setup>
import { RouterLink } from 'vue-router'; // Importar RouterLink
import ThemeToggle from './ThemeToggle.vue'; // Importar ThemeToggle

// Nota: Você precisará instalar 'bootstrap-icons' para os ícones: npm install bootstrap-icons
// E incluir o CSS deles no seu app.css ou diretamente no app.blade.php
</script>

<style scoped>
.sidebar {
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}
.sidebar .nav-link {
  padding: 0.75rem 1rem;
  transition: background-color 0.2s ease;
  border-radius: 0.375rem; /* rounded-3 */
}
.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff !important;
}
.sidebar .nav-link.router-link-active {
  font-weight: bold;
}

/* Estilo para ícones do Bootstrap Icons (se você os tiver instalado) */
.bi {
    font-size: 1.1em;
    vertical-align: -0.125em;
}
</style>