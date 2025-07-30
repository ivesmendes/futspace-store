<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Dashboard</h1>

    <div class="row text-center">
      <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h5 class="card-title">Faturamento</h5>
            <p class="card-text fs-3">R$ {{ totalRevenue.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-info text-dark">
          <div class="card-body">
            <h5 class="card-title">Custo</h5>
            <p class="card-text fs-3">R$ {{ totalCost.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5 class="card-title">Lucro</h5>
            <p class="card-text fs-3">R$ {{ totalProfit.toFixed(2) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-center mt-5">
      <a href="/clients" class="btn btn-primary btn-lg">Gerenciar Clientes &rarr;</a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ThemeToggle from './ThemeToggle.vue';

const customers = ref([]);

// Funções computadas para calcular os totais
const totalRevenue = computed(() => {
  return customers.value.reduce((sum, customer) => {
    // Certifique-se de que order_value é um número
    return sum + parseFloat(customer.order_value || 0);
  }, 0);
});

const totalCost = computed(() => {
  return customers.value.reduce((sum, customer) => {
    // Certifique-se de que wholesale_value é um número
    return sum + parseFloat(customer.wholesale_value || 0);
  }, 0);
});

const totalProfit = computed(() => {
  return totalRevenue.value - totalCost.value;
});

// Função para buscar todos os clientes (sem paginação para os cálculos do dashboard)
const fetchAllCustomers = () => {
  // Passamos um parâmetro 'all=1' para que o backend saiba que queremos todos os dados
  axios.get('/api/customers?all=1')
    .then(response => {
      // A resposta paginada vem como um objeto, e os dados estão em response.data.data
      // Se a API retornar todos os dados diretamente sem paginação (sem 'data' aninhado),
      // então seria apenas response.data
      customers.value = response.data.data || response.data; // Adapta para ambas as possibilidades
    })
    .catch(error => {
      console.error('Erro ao buscar clientes:', error);
      customers.value = []; // Garante que customers.value seja um array em caso de erro
    });
};

onMounted(() => {
  fetchAllCustomers();
});
</script>