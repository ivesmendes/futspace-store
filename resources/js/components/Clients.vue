<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Clientes</h1>

    <div class="row">
      <div class="col-md-8">
        <button @click="openFormModal(false)" class="btn btn-primary mb-4">
          Adicionar Novo Cliente
        </button>

        <ul class="list-group">
          <li
            v-for="c in customers"
            :key="c.id"
            class="list-group-item d-flex justify-content-between align-items-start"
          >
            <div>
              <div class="fw-bold">{{ c.name }}</div>
              <div style="white-space: pre-wrap;">{{ c.order_description }}</div>
              <div class="mt-1">
                Pedido: R$ {{ parseFloat(c.order_value).toFixed(2) }} —
                Atacado: R$ {{ parseFloat(c.wholesale_value).toFixed(2) }}
              </div>
              <div class="mt-1">
                Pago: R$ {{ parseFloat(c.amount_paid || 0).toFixed(2) }}
                <span v-if="c.order_value - (c.amount_paid || 0) > 0" class="text-danger small">
                  (Falta R$ {{ (c.order_value - (c.amount_paid || 0)).toFixed(2) }})
                </span>
              </div>
              <div v-if="c.order_date" class="small text-muted">
                Pedido em: {{ formatDate(c.order_date) }}
              </div>
              <div v-if="c.city" class="small text-muted">
                Cidade: {{ c.city }}
              </div>
              <div class="mt-2">
                <span
                  class="badge me-1"
                  :class="{
                    'bg-success': c.amount_paid >= c.order_value,
                    'bg-info': c.amount_paid > 0 && c.amount_paid < c.order_value,
                    'bg-warning text-dark': c.amount_paid == 0 || c.amount_paid == null,
                  }"
                >
                  {{
                    c.amount_paid >= c.order_value
                      ? 'Pago Totalmente'
                      : c.amount_paid > 0
                      ? 'Pago Parcialmente'
                      : 'Não Pago'
                  }}
                </span>
                <span
                  class="badge"
                  :class="c.delivered ? 'bg-info' : 'bg-secondary'"
                >
                  {{ c.delivered ? 'Entregue' : 'Não Entregue' }}
                </span>
              </div>
            </div>
            <div class="btn-group btn-group-sm">
              <button
                @click="startEdit(c)"
                class="btn btn-outline-warning"
                title="Editar"
              >
                <i class="bi bi-pencil"></i>
              </button>
              <button
                @click="remove(c.id)"
                class="btn btn-outline-danger"
                title="Excluir"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </li>
        </ul>

        <!-- Paginação compacta -->
        <nav v-if="pagination.lastPage > 1" class="mt-4">
          <ul class="pagination pagination-sm justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
              <button class="page-link" @click="goToPage(pagination.currentPage - 1)">
                Anterior
              </button>
            </li>

            <li
              v-for="(p, idx) in compactPages"
              :key="idx + '-' + p"
              class="page-item"
              :class="{ active: p === pagination.currentPage, disabled: p === '…' }"
            >
              <button
                v-if="p !== '…'"
                class="page-link"
                @click="goToPage(p)"
              >
                {{ p }}
              </button>
              <span v-else class="page-link">…</span>
            </li>

            <li
              class="page-item"
              :class="{ disabled: pagination.currentPage === pagination.lastPage }"
            >
              <button class="page-link" @click="goToPage(pagination.currentPage + 1)">
                Próximo
              </button>
            </li>
          </ul>
        </nav>
      </div>

      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-header">Contadores</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label d-block text-center">Clientes que já fizeram pedidos</label>
              <div class="input-group">
                <span class="form-control text-center bg-body-tertiary fs-5 fw-bold">{{ pagination.total }}</span>
              </div>
            </div>
            <div>
              <label class="form-label d-block text-center">Total de camisas pedidas (manual)</label>
              <div class="input-group">
                <button class="btn btn-outline-secondary" @click="decrementShirtCount">-</button>
                <span class="form-control text-center bg-body-tertiary fs-5">{{ shirtCount }}</span>
                <button class="btn btn-outline-secondary" @click="incrementShirtCount">+</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header">Filtros</div>
          <div class="card-body">
            <div class="mb-3">
              <label for="search" class="form-label">Pesquisar</label>
              <input
                v-model="filters.search"
                type="text"
                class="form-control"
                id="search"
                placeholder="Nome, descrição ou cidade"
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Status Pagamento</label>
              <select
                v-model="filters.payment_status"
                class="form-select"
              >
                <option :value="null">Todos</option>
                <option value="fully_paid">Pago Totalmente</option>
                <option value="partially_paid">Pago Parcialmente</option>
                <option value="not_paid">Não Pago</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Status Entrega</label>
              <select
                v-model="filters.delivered"
                class="form-select"
              >
                <option :value="null">Todos</option>
                <option value="1">Entregue</option>
                <option value="0">Não Entregue</option>
              </select>
            </div>
            <div class="d-grid gap-2">
              <button @click="applyFilters" class="btn btn-info">
                Pesquisar
              </button>
              <button @click="clearFilters" class="btn btn-outline-secondary">
                Limpar Filtros
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="clientFormModal"
      tabindex="-1"
      aria-labelledby="clientFormModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="clientFormModalLabel">
              {{ isEditing ? 'Editar Cliente' : 'Adicionar Novo Cliente' }}
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <form @submit.prevent="isEditing ? update() : add()">
            <div class="modal-body">
              <div class="row gy-3">
                <div class="col-md-6">
                  <label for="clientName" class="form-label">Nome do Cliente</label>
                  <input
                    v-model="form.name"
                    class="form-control"
                    id="clientName"
                    placeholder="Nome do Cliente"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="orderValue" class="form-label">Valor do Pedido</label>
                  <input
                    v-model="form.order_value"
                    @input="sanitizeNumber('order_value')"
                    type="text"
                    class="form-control"
                    id="orderValue"
                    placeholder="e.g. 153,74"
                    required
                  />
                </div>
                <div class="col-12">
                  <label for="orderDescription" class="form-label">Descrição do Pedido</label>
                  <textarea
                    v-model="form.order_description"
                    class="form-control"
                    id="orderDescription"
                    placeholder="Descrição do Pedido"
                    required
                    rows="3"
                  ></textarea>
                </div>
                <div class="col-md-6">
                  <label for="wholesaleValue" class="form-label">Valor Atacado</label>
                  <input
                    v-model="form.wholesale_value"
                    @input="sanitizeNumber('wholesale_value')"
                    type="text"
                    class="form-control"
                    id="wholesaleValue"
                    placeholder="e.g. 120,50"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="amountPaid" class="form-label">Valor Pago</label>
                  <input
                    v-model="form.amount_paid"
                    @input="sanitizeNumber('amount_paid')"
                    type="text"
                    class="form-control"
                    id="amountPaid"
                    placeholder="e.g. 50,00 (se parcial)"
                  />
                </div>
                <div class="col-md-6">
                  <label for="clientDate" class="form-label">Data do Pedido</label>
                  <input
                    v-model="form.order_date"
                    type="date"
                    class="form-control"
                    id="clientDate"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="clientCity" class="form-label">Cidade</label>
                  <input
                    v-model="form.city"
                    class="form-control"
                    id="clientCity"
                    placeholder="Nome da Cidade"
                  />
                </div>
                <div class="col-md-3 form-check">
                  <input
                    v-model="form.delivered"
                    type="checkbox"
                    id="delivered"
                    class="form-check-input"
                  />
                  <label for="delivered" class="form-check-label">Entregue</label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
                @click="cancelEdit"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn"
                :class="isEditing ? 'btn-success' : 'btn-primary'"
              >
                {{ isEditing ? 'Salvar Alterações' : 'Adicionar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import ThemeToggle from './ThemeToggle.vue';
import { Modal } from 'bootstrap';

const customers = ref([]);
const form = ref({
  name: '',
  order_description: '',
  order_value: '',
  wholesale_value: '',
  amount_paid: '',
  order_date: '',
  city: '',
  delivered: false,
});
const isEditing = ref(false);
const editId = ref(null);
let clientFormModal = null;

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
});

const filters = ref({
  search: '',
  payment_status: null,
  delivered: null,
});

// Contador agora será atualizado pela API
const shirtCount = ref(0);

// Função para atualizar o contador no backend
const updateShirtCountOnBackend = async (newCount) => {
  try {
    const response = await axios.post('/api/shirt-counter', { count: newCount });
    shirtCount.value = response.data.count;
  } catch (error) {
    console.error('Erro ao atualizar o contador de camisas:', error);
  }
};

const incrementShirtCount = () => updateShirtCountOnBackend(shirtCount.value + 1);
const decrementShirtCount = () => {
  if (shirtCount.value > 0) updateShirtCountOnBackend(shirtCount.value - 1);
};

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString + 'T00:00:00');
  return date.toLocaleDateString('pt-BR');
}

function openFormModal(editing = false) {
  isEditing.value = editing;
  if (!editing) {
    resetForm();
    form.value.order_date = new Date().toISOString().slice(0, 10);
  }
  clientFormModal.show();
}

function closeFormModal() {
  clientFormModal.hide();
}

function fetch(page = 1) {
  const params = {
    page,
    search: filters.value.search,
    payment_status: filters.value.payment_status,
    delivered: filters.value.delivered,
  };

  Object.keys(params).forEach((key) => {
    if (params[key] === '' || params[key] === null) {
      delete params[key];
    }
  });

  axios
    .get('/api/customers', { params })
    .then((r) => {
      customers.value = r.data.data;
      pagination.value.currentPage = r.data.current_page;
      pagination.value.lastPage = r.data.last_page;
      pagination.value.total = r.data.total;
    })
    .catch(console.error);
}

function applyFilters() {
  fetch(1);
}

function clearFilters() {
  filters.value.search = '';
  filters.value.payment_status = null;
  filters.value.delivered = null;
  fetch(1);
}

function goToPage(page) {
  if (page >= 1 && page <= pagination.value.lastPage) {
    fetch(page);
  }
}

function sanitizeNumber(field) {
  let v = form.value[field].replace(/,/g, '.').replace(/[^0-9.]/g, '');
  const parts = v.split('.');
  if (parts.length > 2) v = parts.shift() + '.' + parts.join('');
  form.value[field] = v;
}

function resetForm() {
  form.value = {
    name: '',
    order_description: '',
    order_value: '',
    wholesale_value: '',
    amount_paid: '',
    order_date: '',
    city: '',
    delivered: false,
  };
}

function add() {
  const payload = {
    ...form.value,
    order_value: parseFloat(form.value.order_value) || 0,
    wholesale_value: parseFloat(form.value.wholesale_value) || 0,
    amount_paid: parseFloat(form.value.amount_paid) || 0,
  };
  axios
    .post('/api/customers', payload)
    .then(() => {
      fetch(1);
      closeFormModal();
      resetForm();
    })
    .catch(console.error);
}

function startEdit(c) {
  isEditing.value = true;
  editId.value = c.id;
  form.value = {
    name: c.name,
    order_description: c.order_description,
    order_value: String(c.order_value),
    wholesale_value: String(c.wholesale_value),
    amount_paid: String(c.amount_paid || 0),
    order_date: c.order_date || '',
    city: c.city || '',
    delivered: Boolean(c.delivered),
  };
  openFormModal(true);
}

function cancelEdit() {
  isEditing.value = false;
  editId.value = null;
  resetForm();
  closeFormModal();
}

function update() {
  const payload = {
    ...form.value,
    order_value: parseFloat(form.value.order_value) || 0,
    wholesale_value: parseFloat(form.value.wholesale_value) || 0,
    amount_paid: parseFloat(form.value.amount_paid) || 0,
  };
  axios
    .put(`/api/customers/${editId.value}`, payload)
    .then(() => {
      fetch(pagination.value.currentPage);
      closeFormModal();
      resetForm();
      isEditing.value = false;
      editId.value = null;
    })
    .catch(console.error);
}

function remove(id) {
  if (!confirm('Deseja excluir este cliente?')) return;
  axios
    .delete(`/api/customers/${id}`)
    .then(() => fetch(pagination.value.currentPage))
    .catch(console.error);
}

// Páginas compactas: 1 … (current-2) (current-1) current (current+1) (current+2) … last
const compactPages = computed(() => {
  const total = Number(pagination.value.lastPage || 1);
  const current = Number(pagination.value.currentPage || 1);

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const windowSet = new Set([
    1,
    total,
    current - 2,
    current - 1,
    current,
    current + 1,
    current + 2,
  ]);

  const pages = [...windowSet]
    .filter((p) => p >= 1 && p <= total)
    .sort((a, b) => a - b);

  const out = [];
  for (let i = 0; i < pages.length; i++) {
    const p = pages[i];
    if (i === 0) {
      out.push(p);
    } else {
      const prev = pages[i - 1];
      if (p - prev === 1) out.push(p);
      else out.push('…', p);
    }
  }
  return out;
});

onMounted(() => {
  fetch();
  clientFormModal = new Modal(document.getElementById('clientFormModal'));

  axios
    .get('/api/shirt-counter')
    .then((response) => {
      shirtCount.value = response.data.count;
    })
    .catch((error) => {
      console.error('Erro ao carregar o contador de camisas:', error);
    });
});
</script>

<style scoped>
/* Seus estilos existentes */
.list-group-item {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
  margin-bottom: 10px;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.list-group-item .fw-bold {
  color: var(--bs-heading-color);
}
.list-group-item .text-muted {
  color: var(--bs-secondary-color) !important;
}

/* Ajuste de margem para o botão "Adicionar Novo Cliente" caso necessário */
.btn-primary.mb-4 {
  margin-bottom: 1.5rem !important;
}

/* Estilos para os botões de ação (editar/excluir) para combinar com Estoque */
.btn-group-sm .btn {
  padding: .25rem .5rem;
  font-size: .875rem;
  line-height: 1.5;
  border-radius: .2rem;
}

.btn-outline-warning {
  --bs-btn-color: var(--bs-warning);
  --bs-btn-border-color: var(--bs-warning);
  --bs-btn-hover-color: var(--bs-black);
  --bs-btn-hover-bg: var(--bs-warning);
  --bs-btn-hover-border-color: var(--bs-warning);
  --bs-btn-active-color: var(--bs-black);
  --bs-btn-active-bg: var(--bs-warning);
  --bs-btn-active-border-color: var(--bs-warning);
  --bs-btn-disabled-color: var(--bs-warning);
  --bs-btn-disabled-border-color: var(--bs-warning);
}

.btn-outline-danger {
  --bs-btn-color: var(--bs-danger);
  --bs-btn-border-color: var(--bs-danger);
  --bs-btn-hover-color: var(--bs-white);
  --bs-btn-hover-bg: var(--bs-danger);
  --bs-btn-hover-border-color: var(--bs-danger);
  --bs-btn-active-color: var(--bs-white);
  --bs-btn-active-bg: var(--bs-danger);
  --bs-btn-active-border-color: var(--bs-danger);
  --bs-btn-disabled-color: var(--bs-danger);
  --bs-btn-disabled-border-color: var(--bs-danger);
}

/* Compacta visualmente a paginação */
.pagination .page-link {
  min-width: 34px;
  text-align: center;
  padding: .25rem .5rem;
}
</style>
