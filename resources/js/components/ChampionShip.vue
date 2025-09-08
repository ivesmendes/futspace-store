<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Parceria "ChampionShip"</h1>

    <div class="row">
      <!-- Formulário -->
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

      <!-- Tabela -->
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
                  <tr v-if="visibleSales.length === 0">
                    <td colspan="2" class="text-center">Nenhuma venda registrada ainda.</td>
                  </tr>

                  <!-- Renderiza apenas vendas (exclui entradas de resgate) -->
                  <template v-for="sale in visibleSales" :key="sale.id">
                    <!-- Banner vem ANTES da venda qualificada (lista desc) -->
                    <tr v-if="qualifyingSlot(sale) !== null">
                      <td colspan="2" class="text-center">
                        <!-- Já resgatou este slot? -->
                        <span
                          v-if="qualifyingSlot(sale) <= redeemedCount"
                          class="btn btn-sm btn-outline-danger disabled fw-bold"
                        >
                          <i class="bi bi-gift me-2"></i> Camisa resgatada pela parceria
                        </span>

                        <!-- Ainda não resgatou -->
                        <button
                          v-else
                          @click="markFreeShirtRedeemed"
                          class="btn btn-sm btn-outline-success fw-bold"
                        >
                          <i class="bi bi-gift me-2"></i> Direito a resgatar uma camisa
                        </button>
                      </td>
                    </tr>

                    <tr :class="{'text-success': isQualifyingSale(sale)}">
                      <td>{{ sale.number }}</td>
                      <td>{{ sale.description }}</td>
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

/** Entradas de venda reais (exclui resgates) — respeita a ordem vinda da API (desc). */
const visibleSales = computed(() =>
  sales.value.filter(s => s.description !== 'Camisa recebida pela parceria')
);

/** Para calcular as posições (8ª, 16ª...) precisamos das vendas em ordem cronológica asc. */
const soldShirts = computed(() =>
  [...sales.value]
    .reverse()
    .filter(s => s.description !== 'Camisa recebida pela parceria')
);

/** Quantidade de resgates efetuados (conta as entradas especiais) */
const redeemedCount = computed(() =>
  sales.value.filter(s => s.description === 'Camisa recebida pela parceria').length
);

/** Quantas já foram “ganhas” por múltiplos de 8 */
const earnedCount = computed(() => Math.floor(soldShirts.value.length / 8));

const freeShirtStatusMessage = computed(() => {
  const available = earnedCount.value - redeemedCount.value;
  return available > 0 ? `Parceiro tem direito a resgatar ${available} camisa(s).` : '';
});

const fetchSales = () => {
  axios.get('/api/championship-sales')
    .then(r => { sales.value = r.data; })
    .catch(err => console.error('Erro ao buscar as vendas:', err));
};

const addSale = () => {
  if (!newSale.value.number || !newSale.value.description) return;
  axios.post('/api/championship-sales', newSale.value)
    .then(() => {
      newSale.value.number = '';
      newSale.value.description = '';
      fetchSales();
    })
    .catch(err => console.error('Erro ao adicionar a venda:', err));
};

/** Marca o resgate (cria uma entrada especial no back para manter o histórico/contagem) */
const markFreeShirtRedeemed = () => {
  const confirmed = confirm('Confirmar resgate de uma camisa gratuita pela parceria?');
  if (!confirmed) return;

  axios.post('/api/championship-sales', {
    number: 0, // inteiro para passar na validação
    description: 'Camisa recebida pela parceria'
  })
  .then(() => fetchSales())
  .catch(err => console.error('Erro ao marcar camisa como recebida:', err));
};

/** Slot da camisa grátis que esta venda fecha: 1 (8ª), 2 (16ª), 3 (24ª)..., ou null */
const qualifyingSlot = (sale) => {
  const idx = soldShirts.value.findIndex(s => s.id === sale.id);
  if (idx === -1) return null;
  const pos = idx + 1; // 1-based
  return (pos % 8 === 0) ? (pos / 8) : null;
};

const isQualifyingSale = (sale) => qualifyingSlot(sale) !== null;

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

onMounted(fetchSales);
</script>

<style scoped>
.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.table-responsive { overflow-x: auto; }
.table-striped tbody tr:nth-of-type(odd) { background-color: var(--bs-table-striped-bg); }
.text-success { color: var(--bs-success) !important; }
</style>
