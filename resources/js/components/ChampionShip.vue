<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Parceria "ChampionShip"</h1>

    <div class="row">
      <div class="col-md-6">
        <div class="card shadow-sm p-4 mb-4 h-100">
          <h5 class="card-title mb-3">Adicionar Nova Venda</h5>
          <form @submit.prevent="addSale">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="saleNumber" class="form-label">Número da Camisa</label>
                <input
                  type="number"
                  id="saleNumber"
                  v-model="newSale.number"
                  class="form-control"
                  required
                />
              </div>
              <div class="col-md-6">
                <label for="saleDescription" class="form-label">Descrição da Venda</label>
                <input
                  type="text"
                  id="saleDescription"
                  v-model="newSale.description"
                  class="form-control"
                  placeholder="Ex: Camisa 10 (Marcus, Teresina)"
                  required
                />
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-success">Adicionar</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm p-4 h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Tabela de Vendas</h5>
            <button @click="exportToPdf" class="btn btn-danger btn-sm">
              <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </button>
          </div>
          
          <div id="pdf-content">
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th scope="col" style="width: 30%;">Número da Camisa</th>
                    <th scope="col" style="width: 70%;">Descrição da Venda</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="sales.length === 0">
                    <td colspan="2" class="text-center">Nenhuma venda registrada ainda.</td>
                  </tr>
                  <template v-for="(sale, index) in sales" :key="sale.id">
                    <tr :class="{'text-success': isQualifyingSale(sale)}">
                      <td>{{ sale.number }}</td>
                      <td>{{ sale.description }}</td>
                    </tr>
                    <tr v-if="isQualifyingSale(sale) && sale.description !== 'Camisa recebida pela parceria'">
                      <td colspan="2" class="text-center text-success fw-bold">
                        <button @click="markFreeShirtRedeemed()" class="btn btn-sm btn-outline-success">
                          <i class="bi bi-gift me-2"></i> Direito a resgatar uma camisa
                        </button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            
            <div class="mt-3 text-center">
              <p class="lead"><strong>Regra:</strong> A cada 8 vendas feitas, o parceiro ganha uma camisa grátis.</p>
              <p v-if="freeShirtStatusMessage" class="text-info">{{ freeShirtStatusMessage }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import ThemeToggle from './ThemeToggle.vue';
import html2pdf from 'html2pdf.js';
import axios from 'axios';

const sales = ref([]);
const newSale = ref({ number: '', description: '' });

// Lógica para contar camisas vendidas (excluindo as resgatadas)
const soldShirts = computed(() => {
    // Cria um array de camisas vendidas em ordem cronológica (da mais antiga para a mais nova)
    // para encontrar a posição correta. O .reverse() é crucial aqui.
    return [...sales.value].reverse().filter(s => s.description !== 'Camisa recebida pela parceria');
});

const freeShirtStatusMessage = computed(() => {
    const redeemedCount = sales.value.filter(s => s.description === 'Camisa recebida pela parceria').length;
    const earnedCount = Math.floor(soldShirts.value.length / 8);
    const availableCount = earnedCount - redeemedCount;
    return availableCount > 0 ? `Parceiro tem direito a resgatar ${availableCount} camisa(s).` : '';
});

const fetchSales = () => {
  axios.get('/api/championship-sales')
    .then(response => {
      sales.value = response.data;
    })
    .catch(error => {
      console.error('Erro ao buscar as vendas:', error);
    });
};

const addSale = () => {
  if (newSale.value.number && newSale.value.description) {
    axios.post('/api/championship-sales', newSale.value)
      .then(response => {
        newSale.value.number = '';
        newSale.value.description = '';
        fetchSales();
      })
      .catch(error => {
        console.error('Erro ao adicionar a venda:', error);
      });
  }
};

const markFreeShirtRedeemed = () => {
    const confirmed = confirm('Tem certeza que deseja marcar esta camisa como resgatada? Isso irá adicionar uma nova entrada na tabela.');
    if (confirmed) {
        axios.post('/api/championship-sales', { number: 'GRATUITA', description: 'Camisa recebida pela parceria' })
            .then(response => {
                fetchSales(); // Recarrega a lista para atualizar a visualização
            })
            .catch(error => {
                console.error('Erro ao marcar camisa como recebida:', error);
            });
    }
};

const isQualifyingSale = (sale) => {
    // Encontra o índice da venda atual no array de camisas vendidas (não gratuitas)
    // na ordem cronológica (da mais antiga para a mais nova)
    const soldShirtsIndex = soldShirts.value.findIndex(s => s.id === sale.id);
    
    // Se for uma camisa vendida, verifica se sua posição no array + 1 é um múltiplo de 8
    // A contagem começa do 0, então +1 a torna cronológica
    return soldShirtsIndex !== -1 && (soldShirtsIndex) % 8 === 0;
};

const exportToPdf = () => {
    const element = document.getElementById('pdf-content');
    html2pdf(element, {
        margin: 10,
        filename: 'ChampionShip.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    });
};

onMounted(() => {
  fetchSales();
});
</script>

<style scoped>
.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.table-responsive {
    overflow-x: auto;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: var(--bs-table-striped-bg);
}
.text-success {
    color: var(--bs-success) !important;
}
</style>