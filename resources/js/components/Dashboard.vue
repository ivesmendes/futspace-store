<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Dashboard</h1>

    <div class="row mb-5">
      <div class="col-md-8 mx-auto">
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white">Status de Faturamento (Potencial)</div>
          <div class="card-body">
            <Pie
              :data="paymentStatusChartData"
              :options="paymentStatusChartOptions"
              v-if="chartDataLoaded"
            />
            <div v-else class="text-center py-5">Carregando gráfico...</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row text-center justify-content-center">
      <div class="col-md-4 mb-3">
        <div class="card text-white bg-dark shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Valor de Estoque Atual</h5>
            <p class="card-text fs-3">
              <span v-if="loadingStockValue">Carregando...</span>
              <span v-else>R$ {{ parseFloat(totalStockValue).toFixed(2) }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Faturamento (Potencial)</h5>
            <p class="card-text fs-3">R$ {{ totalRevenuePotential.toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="card bg-info text-dark shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Custo Total</h5>
            <p class="card-text fs-3">R$ {{ totalCost.toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="card bg-success text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Lucro (Potencial)</h5>
            <p class="card-text fs-3">R$ {{ totalProfitPotential.toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Receita Recebida</h5>
            <p class="card-text fs-3">R$ {{ totalAmountReceived.toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="card bg-danger text-white shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Contas a Receber</h5>
            <p class="card-text fs-3">R$ {{ totalOutstanding.toFixed(2) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-center mt-5 mb-4">
      <button class="btn btn-secondary btn-lg" @click="toggleDetails">
        {{ showDetails ? 'Ocultar Detalhes' : 'Ver Detalhes das Métricas' }}
      </button>
    </div>

    <transition name="fade">
      <div v-if="showDetails" class="card shadow-sm mt-4 details-section">
        <div class="card-header bg-light text-dark">
          <h5 class="mb-0">Como as Métricas São Calculadas</h5>
        </div>
        <div class="card-body">
          <p><strong>Valor de Estoque Atual:</strong> A soma do "Valor Atacado" de todos os itens atualmente registrados no seu estoque. Esta métrica reflete o valor dos bens que você possui, *mas que ainda não foram vendidos ou vinculados a um pedido de cliente*, portanto, não se mistura com as métricas de faturamento e lucro de vendas.</p>
          <p><strong>Faturamento (Potencial):</strong> A soma total do "Valor do Pedido" de *todos* os pedidos de clientes, independentemente de estarem pagos ou não. Representa o faturamento máximo que você pode obter se todos os pedidos forem totalmente pagos.</p>
          <p><strong>Custo Total:</strong> A soma do "Valor Atacado" de todos os pedidos de clientes. Isso representa o custo total dos produtos que foram encomendados pelos seus clientes.</p>
          <p><strong>Lucro (Potencial):</strong> O resultado da subtração do "Custo Total" do "Faturamento (Potencial)". Representa o lucro máximo que você pode obter se todos os pedidos forem pagos em seu valor total.</p>
          <p><strong>Receita Recebida:</strong> A soma de todos os valores que foram *efetivamente pagos* pelos seus clientes até o momento. Isso inclui pagamentos totais e parciais.</p>
          <p><strong>Contas a Receber:</strong> A soma dos valores restantes a serem pagos pelos clientes. É o "Faturamento (Potencial)" menos a "Receita Recebida". Representa o montante que ainda está pendente de recebimento.</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ThemeToggle from './ThemeToggle.vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';

// Registrar os elementos necessários do Chart.js para o gráfico de pizza
ChartJS.register(ArcElement, Tooltip, Legend);

const customers = ref([]);
const chartDataLoaded = ref(false);

const totalStockValue = ref(0.00);
const loadingStockValue = ref(true);

// NOVO: Estado para controlar a visibilidade dos detalhes
const showDetails = ref(false);

// --- Funções Computadas para as Métricas Financeiras ---
const totalRevenuePotential = computed(() => {
  return customers.value.reduce((sum, customer) => {
    return sum + parseFloat(customer.order_value || 0);
  }, 0);
});

const totalCost = computed(() => {
  return customers.value.reduce((sum, customer) => {
    return sum + parseFloat(customer.wholesale_value || 0);
  }, 0);
});

const totalProfitPotential = computed(() => {
  return totalRevenuePotential.value - totalCost.value;
});

const totalAmountReceived = computed(() => {
  return customers.value.reduce((sum, customer) => {
    return sum + parseFloat(customer.amount_paid || 0);
  }, 0);
});

const totalOutstanding = computed(() => {
  return customers.value.reduce((sum, customer) => {
    const remaining = parseFloat(customer.order_value || 0) - parseFloat(customer.amount_paid || 0);
    return sum + (remaining > 0 ? remaining : 0);
  }, 0);
});

// --- Dados e Opções para o Gráfico de Pizza ---
const paymentStatusChartData = computed(() => {
  const fullyPaid = customers.value.filter(c => parseFloat(c.amount_paid || 0) >= parseFloat(c.order_value || 0));
  const partiallyPaid = customers.value.filter(c => parseFloat(c.amount_paid || 0) > 0 && parseFloat(c.amount_paid || 0) < parseFloat(c.order_value || 0));
  const notPaid = customers.value.filter(c => parseFloat(c.amount_paid || 0) === 0 || c.amount_paid === null);

  const fullyPaidRevenue = fullyPaid.reduce((sum, c) => sum + parseFloat(c.order_value || 0), 0);
  const partiallyPaidRevenue = partiallyPaid.reduce((sum, c) => sum + parseFloat(c.order_value || 0), 0);
  const notPaidRevenue = notPaid.reduce((sum, c) => sum + parseFloat(c.order_value || 0), 0);

  return {
    labels: ['Totalmente Pago', 'Parcialmente Pago', 'Não Pago'],
    datasets: [
      {
        backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
        data: [fullyPaidRevenue, partiallyPaidRevenue, notPaidRevenue]
      }
    ]
  };
});

const paymentStatusChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    tooltip: {
      callbacks: {
        label: function(context) {
          let label = context.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed !== null) {
            label += 'R$ ' + context.parsed.toFixed(2);
          }
          return label;
        }
      }
    },
    legend: {
      position: 'bottom',
    }
  }
};

// --- Função de Carregamento de Dados dos Clientes ---
const fetchAllCustomers = () => {
  axios.get('/api/customers?all=1')
    .then(response => {
      customers.value = response.data.data || response.data;
      chartDataLoaded.value = true;
    })
    .catch(error => {
      console.error('Erro ao buscar clientes para o dashboard:', error);
      customers.value = [];
      chartDataLoaded.value = true;
    });
};

// --- Carregamento do Valor Total do Estoque ---
const fetchTotalStockValue = () => {
  loadingStockValue.value = true;
  axios.get('/api/stock-value')
    .then(response => {
      totalStockValue.value = response.data.total_stock_value || 0;
    })
    .catch(error => {
      console.error('Erro ao buscar valor total do estoque:', error);
      totalStockValue.value = 0;
    })
    .finally(() => {
      loadingStockValue.value = false;
    });
};

// --- NOVO: Função para alternar a visibilidade dos detalhes ---
const toggleDetails = () => {
  showDetails.value = !showDetails.value;
};

// --- Ciclo de Vida do Componente ---
onMounted(() => {
  fetchAllCustomers();
  fetchTotalStockValue();
});
</script>

<style scoped>
/* Estilos existentes para os cards */
.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card-body p.fs-3 {
  margin-bottom: 0;
}
.card-header.bg-dark {
  background-color: #343a40 !important;
  border-bottom: 1px solid rgba(0,0,0,.125);
}

/* Cores dos cards - mantidas */
.bg-dark { background-color: #343a40 !important; }
.bg-primary { background-color: #007bff !important; }
.bg-info { background-color: #17a2b8 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-danger { background-color: #dc3545 !important; }

/* Novo estilo para a seção de detalhes (opcional, para transição suave) */
.details-section {
    background-color: var(--bs-body-bg); /* Adapta ao tema */
    border: 1px solid var(--bs-border-color);
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>