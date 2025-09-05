<template>
  <div class="container py-5">
    <h1 class="mb-4">Prejuízos</h1>

    <div class="row">
      <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <button class="btn btn-primary" @click="openForm(false)">Novo Prejuízo</button>
          <div class="d-flex gap-2">
            <select v-model="filters.month" class="form-select form-select-sm" style="width: 140px;">
              <option value="">Mês (Todos)</option>
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.name }}</option>
            </select>
            <select v-model="filters.year" class="form-select form-select-sm" style="width: 140px;">
              <option value="">Ano (Todos)</option>
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
            <button class="btn btn-outline-secondary btn-sm" @click="fetchLosses">Aplicar</button>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <div v-if="loading" class="text-center py-5">Carregando...</div>
            <div v-else-if="losses.length === 0" class="text-center py-5">Nenhum prejuízo cadastrado.</div>
            <div v-else class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th style="width: 40%;">Motivo</th>
                    <th style="width: 20%;">Valor</th>
                    <th style="width: 20%;">Data</th>
                    <th style="width: 20%;">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="l in losses" :key="l.id">
                    <td>{{ l.reason }}</td>
                    <td>R$ {{ parseFloat(l.amount).toFixed(2) }}</td>
                    <td>{{ formatDate(l.loss_date) }}</td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-warning" @click="openForm(true, l)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" @click="remove(l.id)">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <nav v-if="pagination.lastPage > 1" class="mt-3">
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
                  <button class="page-link" @click="goTo(pagination.currentPage - 1)">Anterior</button>
                </li>
                <li class="page-item" v-for="p in pagination.lastPage" :key="p" :class="{ active: p === pagination.currentPage }">
                  <button class="page-link" @click="goTo(p)">{{ p }}</button>
                </li>
                <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }">
                  <button class="page-link" @click="goTo(pagination.currentPage + 1)">Próximo</button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white">Resumo</div>
          <div class="card-body">
            <p class="mb-2">Total de Prejuízos (filtro):</p>
            <h3>R$ {{ totalLosses.toFixed(2) }}</h3>
            <small class="text-muted">Este valor impacta o lucro exibido no Dashboard.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="lossFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="isEditing ? update() : save()">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditing ? 'Editar Prejuízo' : 'Novo Prejuízo' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Motivo</label>
                <input v-model="form.reason" class="form-control" required placeholder="Ex.: Camisa danificada"/>
              </div>
              <div class="mb-3">
                <label class="form-label">Valor</label>
                <input v-model.number="form.amount" type="number" step="0.01" class="form-control" required placeholder="Ex.: 50,00"/>
              </div>
              <div class="mb-3">
                <label class="form-label">Data</label>
                <input v-model="form.loss_date" type="date" class="form-control"/>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
              <button class="btn btn-primary" type="submit">{{ isEditing ? 'Salvar' : 'Adicionar' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import * as bootstrap from 'bootstrap'

const losses = ref([])
const loading = ref(true)
const isEditing = ref(false)
const editId = ref(null)
let modalInstance = null

const form = ref({
  reason: '',
  amount: 0.00,
  loss_date: new Date().toISOString().slice(0,10),
})

const pagination = ref({
  currentPage: 1, lastPage: 1, total: 0
})

const filters = ref({ month: '', year: '' })

const months = [
  { value: 1, name: 'Janeiro' }, { value: 2, name: 'Fevereiro' }, { value: 3, name: 'Março' },
  { value: 4, name: 'Abril' }, { value: 5, name: 'Maio' }, { value: 6, name: 'Junho' },
  { value: 7, name: 'Julho' }, { value: 8, name: 'Agosto' }, { value: 9, name: 'Setembro' },
  { value: 10, name: 'Outubro' }, { value: 11, name: 'Novembro' }, { value: 12, name: 'Dezembro' },
]
const years = ref([])
const totalLosses = ref(0)

function generateYears() {
  const cur = new Date().getFullYear()
  for (let y = cur - 5; y <= cur + 1; y++) years.value.push(y)
  years.value.sort((a,b)=>b-a)
}

function formatDate(d) {
  if (!d) return '';
  // Se já vier no formato ISO (contém 'T'), use direto.
  // Se vier 'YYYY-MM-DD', complemente com 'T00:00:00'.
  const iso = typeof d === 'string'
    ? (d.includes('T') ? d : `${d}T00:00:00`)
    : d;

  const date = new Date(iso);
  if (isNaN(date)) return String(d); // fallback
  return date.toLocaleDateString('pt-BR');
}

function paramsWithFilters(extra={}) {
  const p = { ...extra }
  if (filters.value.month !== '') p.month = filters.value.month
  if (filters.value.year !== '')  p.year  = filters.value.year
  return p
}

function fetchLosses(page = 1) {
  loading.value = true
  axios.get('/api/losses', { params: paramsWithFilters({ page }) })
    .then(r => {
      const data = r.data
      if (Array.isArray(data)) {
        losses.value = data
        pagination.value = { currentPage: 1, lastPage: 1, total: data.length }
      } else {
        losses.value = data.data
        pagination.value.currentPage = data.current_page
        pagination.value.lastPage = data.last_page
        pagination.value.total = data.total
      }
    })
    .finally(()=> loading.value = false)

  axios.get('/api/losses-total', { params: paramsWithFilters() })
    .then(r => totalLosses.value = parseFloat(r.data.total_losses || 0))
    .catch(()=> totalLosses.value = 0)
}

function openForm(edit=false, l=null) {
  isEditing.value = edit
  if (edit && l) {
    editId.value = l.id
    form.value = {
      reason: l.reason,
      amount: Number(l.amount),
      loss_date: l.loss_date ?? new Date().toISOString().slice(0,10),
    }
  } else {
    editId.value = null
    form.value = { reason:'', amount:0.00, loss_date: new Date().toISOString().slice(0,10) }
  }
  modalInstance.show()
}

function save() {
  axios.post('/api/losses', form.value)
    .then(()=> {
      fetchLosses(pagination.value.currentPage)
      modalInstance.hide()
    })
}

function update() {
  axios.put(`/api/losses/${editId.value}`, form.value)
    .then(()=> {
      fetchLosses(pagination.value.currentPage)
      modalInstance.hide()
    })
}

function remove(id) {
  if (!confirm('Deseja excluir este prejuízo?')) return
  axios.delete(`/api/losses/${id}`)
    .then(()=> fetchLosses(pagination.value.currentPage))
}

function goTo(p) {
  if (p>=1 && p<=pagination.value.lastPage) fetchLosses(p)
}

onMounted(()=>{
  generateYears()
  fetchLosses()
  modalInstance = new bootstrap.Modal(document.getElementById('lossFormModal'))
})
</script>
