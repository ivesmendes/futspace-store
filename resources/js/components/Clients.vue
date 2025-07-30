<template>
  <div class="container py-5">
    <ThemeToggle />

    <h1 class="mb-4">Clientes</h1>

    <button @click="openFormModal(false)" class="btn btn-primary mb-4">
      Adicionar Novo Cliente
    </button>

    <div class="card mb-4">
      <div class="card-header">Filtros</div>
      <div class="card-body">
        <div class="row gy-3">
          <div class="col-md-6">
            <label for="search" class="form-label">Pesquisar</label>
            <input
              v-model.lazy="filters.search"
              @change="applyFilters"
              type="text"
              class="form-control"
              id="search"
              placeholder="Nome, descrição ou cidade"
            />
          </div>
          <div class="col-md-3">
            <label class="form-label">Status Pagamento</label>
            <select
              v-model="filters.paid"
              @change="applyFilters"
              class="form-select"
            >
              <option :value="null">Todos</option>
              <option value="1">Pago</option>
              <option value="0">Não Pago</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status Entrega</label>
            <select
              v-model="filters.delivered"
              @change="applyFilters"
              class="form-select"
            >
              <option :value="null">Todos</option>
              <option value="1">Entregue</option>
              <option value="0">Não Entregue</option>
            </select>
          </div>
          <div class="col-12 mt-3">
            <button @click="clearFilters" class="btn btn-outline-secondary btn-sm">
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>
    </div>
    <ul class="list-group">
      <li
        v-for="c in customers"
        :key="c.id"
        class="list-group-item d-flex justify-content-between align-items-start"
      >
        <div>
          <div class="fw-bold">{{ c.name }}</div>
          <div>{{ c.order_description }}</div>
          <div class="mt-1">
            R$ {{ parseFloat(c.order_value).toFixed(2) }} —
            Atacado: R$ {{ parseFloat(c.wholesale_value).toFixed(2) }}
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
              :class="c.paid ? 'bg-success' : 'bg-warning text-dark'"
            >
              {{ c.paid ? 'Pago' : 'Não Pago' }}
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
            ✏️
          </button>
          <button
            @click="remove(c.id)"
            class="btn btn-outline-danger"
            title="Excluir"
          >
            🗑️
          </button>
        </div>
      </li>
    </ul>

    <nav v-if="pagination.lastPage > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
          <button class="page-link" @click="goToPage(pagination.currentPage - 1)">
            Anterior
          </button>
        </li>
        <li
          v-for="page in pagination.lastPage"
          :key="page"
          class="page-item"
          :class="{ active: page === pagination.currentPage }"
        >
          <button class="page-link" @click="goToPage(page)">
            {{ page }}
          </button>
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
    <div class="mt-4">
      <a href="/" class="btn btn-outline-primary">&larr; Dashboard</a>
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
                    v-model="form.paid"
                    type="checkbox"
                    id="paid"
                    class="form-check-input"
                  />
                  <label for="paid" class="form-check-label">Pago</label>
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ThemeToggle from './ThemeToggle.vue';
import { Modal } from 'bootstrap';

const customers = ref([]);
const form = ref({
  name: '',
  order_description: '',
  order_value: '',
  wholesale_value: '',
  order_date: '',
  city: '',
  paid: false,
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

// NOVO: Estado para os filtros
const filters = ref({
  search: '',
  paid: null, // null para 'Todos', '1' para 'Pago', '0' para 'Não Pago'
  delivered: null, // null para 'Todos', '1' para 'Entregue', '0' para 'Não Entregue'
});

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

// Modificada a função fetch para incluir os parâmetros de filtro
function fetch(page = 1) {
  const params = {
    page: page,
    search: filters.value.search,
    paid: filters.value.paid,
    delivered: filters.value.delivered,
  };

  // Remove parâmetros vazios para não poluir a URL ou a requisição desnecessariamente
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key];
    }
  });

  axios
    .get('/api/customers', { params: params }) // Envia os filtros como query parameters
    .then((r) => {
      customers.value = r.data.data;
      pagination.value.currentPage = r.data.current_page;
      pagination.value.lastPage = r.data.last_page;
      pagination.value.total = r.data.total;
    })
    .catch(console.error);
}

// NOVO: Função para aplicar os filtros (reinicia a paginação para a primeira página)
function applyFilters() {
  fetch(1);
}

// NOVO: Função para limpar os filtros
function clearFilters() {
  filters.value.search = '';
  filters.value.paid = null;
  filters.value.delivered = null;
  fetch(1); // Re-busca os dados com filtros limpos
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
    order_date: '',
    city: '',
    paid: false,
    delivered: false,
  };
}

function add() {
  const payload = {
    ...form.value,
    order_value: parseFloat(form.value.order_value) || 0,
    wholesale_value: parseFloat(form.value.wholesale_value) || 0,
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
    order_date: c.order_date || '',
    city: c.city || '',
    paid: Boolean(c.paid),
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

onMounted(() => {
  fetch(); // Carrega a primeira página com os filtros iniciais (vazios)
  clientFormModal = new Modal(document.getElementById('clientFormModal'));
});
</script>