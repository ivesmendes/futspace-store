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
          <div class="col-md-5">
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
          <div class="col-md-4">
            <label class="form-label">Status Pagamento</label>
            <select
              v-model="filters.payment_status"
              @change="applyFilters"
              class="form-select"
            >
              <option :value="null">Todos</option>
              <option value="fully_paid">Pago Totalmente</option>
              <option value="partially_paid">Pago Parcialmente</option>
              <option value="not_paid">Não Pago</option>
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
  amount_paid: '', // NOVO: Propriedade 'amount_paid'
  order_date: '',
  city: '',
  // paid: false, // REMOVIDO
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
  payment_status: null, // NOVO: Mudei de 'paid' para 'payment_status'
  delivered: null,
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

function fetch(page = 1) {
  const params = {
    page: page,
    search: filters.value.search,
    payment_status: filters.value.payment_status, // NOVO: Usando payment_status
    delivered: filters.value.delivered,
  };

  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key];
    }
  });

  axios
    .get('/api/customers', { params: params })
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
  filters.value.payment_status = null; // Reinicia para null
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
    amount_paid: '', // Reinicia amount_paid
    order_date: '',
    city: '',
    // paid: false, // REMOVIDO
    delivered: false,
  };
}

function add() {
  const payload = {
    ...form.value,
    order_value: parseFloat(form.value.order_value) || 0,
    wholesale_value: parseFloat(form.value.wholesale_value) || 0,
    amount_paid: parseFloat(form.value.amount_paid) || 0, // Garante que é número, default 0
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
    amount_paid: String(c.amount_paid || 0), // Preenche amount_paid
    order_date: c.order_date || '',
    city: c.city || '',
    // paid: Boolean(c.paid), // REMOVIDO
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
    amount_paid: parseFloat(form.value.amount_paid) || 0, // Garante que é número, default 0
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
  fetch();
  clientFormModal = new Modal(document.getElementById('clientFormModal'));
});
</script>