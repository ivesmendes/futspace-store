// resources/js/app.js

import './bootstrap'; // Importa o arquivo bootstrap.js (provavelmente contém o JS do Bootstrap)

// --- Importa o CSS do Bootstrap via npm (melhor prática com Vite) ---
import 'bootstrap/dist/css/bootstrap.min.css';
// ---

// Se você tiver algum CSS customizado em app.css que não seja Tailwind, mantenha esta linha:
// import '../css/app.css'; // Comentado se app.css estiver vazio ou você não tiver customizações

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'
import Clients from './components/Clients.vue'
// import { toggleTheme } from './theme' // REMOVIDO: A lógica do tema agora está no ThemeToggle.vue

// Monta o componente Vue baseado na URL
const url = window.location.pathname
if (url.startsWith('/clients')) {
  createApp(Clients).mount('#app')
} else {
  createApp(Dashboard).mount('#app')
}

// REMOVIDO: Não precisamos expor window.toggleTheme globalmente,
// a lógica está contida no componente ThemeToggle.vue
// window.toggleTheme = toggleTheme