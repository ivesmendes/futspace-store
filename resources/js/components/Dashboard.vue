<template>
  <div class="container py-5">
    <!-- Toggle -->
    <ThemeToggle />

    <h1 class="mb-4">Dashboard</h1>

    <div class="row gy-4">
      <div class="col-md-4" v-for="card in cards" :key="card.title">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">
            <h5 class="card-title">{{ card.title }}</h5>
            <p class="display-6 mb-0">R$ {{ card.value.toFixed(2) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-5">
      <a href="/clients" class="btn btn-primary btn-lg">
        Gerenciar Clientes →
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ThemeToggle from './ThemeToggle.vue'

const customers = ref([])

onMounted(() => {
  axios.get('/api/customers')
    .then(r => customers.value = r.data)
    .catch(console.error)
})

const revenue = computed(() =>
  customers.value.reduce((s, c) => s + Number(c.order_value), 0)
)
const cost = computed(() =>
  customers.value.reduce((s, c) => s + Number(c.wholesale_value), 0)
)
const profit = computed(() => revenue.value - cost.value)

const cards = computed(() => [
  { title: 'Faturamento', value: revenue.value },
  { title: 'Custo',       value: cost.value    },
  { title: 'Lucro',       value: profit.value  },
])
</script>
