import './bootstrap'; // Seus scripts de bootstrap (ex: axios configs)
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router'; // Importar do vue-router

// Importar os componentes de página
import Dashboard from './components/Dashboard.vue';
import Clients from './components/Clients.vue';
import Stock from './components/Stock.vue'; // O novo componente de estoque
import Sidebar from './components/Sidebar.vue'; // O novo componente da sidebar
import ChampionShip from './components/ChampionShip.vue';
import Losses from './components/Losses.vue';

import { toggleTheme } from './theme'; // Importar a função de alternar tema

// Definir as rotas do seu aplicativo Vue
const routes = [
    { path: '/', component: Dashboard },
    { path: '/clients', component: Clients },
    { path: '/stock', component: Stock }, // Nova rota para estoque
    { path: '/championship', component: ChampionShip },
    { path: '/losses', component: Losses }, 
];

// Criar o roteador
const router = createRouter({
    history: createWebHistory(), // Usa o modo de histórico para URLs limpas (sem #)
    routes,
});

// Criar o componente principal que contém o layout com sidebar
const AppLayout = {
    components: {
        Sidebar,
        // Dashboard, Clients, Stock não precisam ser registrados aqui se são renderizados pelo router-view
        // O router-view automaticamente injeta o componente correto
    },
    template: `
        <div class="d-flex" style="min-height: 100vh;">
            <Sidebar />
            <main class="flex-grow-1 p-4">
                <router-view /> </main>
        </div>
    `
};

// Criar e montar a aplicação Vue
const app = createApp(AppLayout); // Monta o AppLayout como componente principal
app.use(router); // Usar o roteador na aplicação
app.mount('#app'); // Montar no elemento com id="app" no seu Blade

// Disponibiliza a função de alternar tema globalmente (mantido)
window.toggleTheme = toggleTheme;