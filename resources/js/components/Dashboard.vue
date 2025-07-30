<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Dashboard</h1>

    <div class="row text-center">
      <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h5 class="card-title">Faturamento (Potencial)</h5>
            <p class="card-text fs-3">R$ {{ totalRevenuePotential.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-info text-dark">
          <div class="card-body">
            <h5 class="card-title">Custo Total</h5>
            <p class="card-text fs-3">R$ {{ totalCost.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5 class="card-title">Lucro (Potencial)</h5>
            <p class="card-text fs-3">R$ {{ totalProfitPotential.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-warning text-dark">
          <div class="card-body">
            <h5 class="card-title">Receita Recebida</h5>
            <p class="card-text fs-3">R$ {{ totalAmountReceived.toFixed(2) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <h5 class="card-title">Contas a Receber</h5>
            <p class="card-text fs-3">R$ {{ totalOutstanding.toFixed(2) }}</p>
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

// NOVO: Total de dinheiro efetivamente recebido
const totalAmountReceived = computed(() => {
  return customers.value.reduce((sum, customer) => {
    return sum + parseFloat(customer.amount_paid || 0);
  }, 0);
});

// NOVO: Total de contas a receber
const totalOutstanding = computed(() => {
  return customers.value.reduce((sum, customer) => {
    const remaining = parseFloat(customer.order_value || 0) - parseFloat(customer.amount_paid || 0);
    return sum + (remaining > 0 ? remaining : 0); // Só soma se ainda há algo a receber
  }, 0);
});


// Função para buscar todos os clientes para os cálculos do dashboard
const fetchAllCustomers = () => {
  axios.get('/api/customers?all=1')
    .then(response => {
      customers.value = response.data.data || response.data;
    })
    .catch(error => {
      console.error('Erro ao buscar clientes para o dashboard:', error);
      customers.value = [];
    });
};

onMounted(() => {
  fetchAllCustomers();
});
</script>