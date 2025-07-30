<template>
  <div class="container py-5">
    <h1 class="mb-4">Estoque de Camisas</h1>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" @click="openAddItemModal">Adicionar Item ao Estoque</button>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">Carregando itens do estoque...</div>
        <div v-else-if="stockItems.length === 0" class="text-center py-5">
          Nenhum item no estoque. Adicione um!
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-striped mb-0">
            <thead>
              <tr>
                <th>Descrição</th>
                <th>Custo (Atacado)</th>
                <th>Valor de Venda</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in stockItems" :key="item.id">
                <td>{{ item.description }}</td>
                <td>R$ {{ parseFloat(item.wholesale_value).toFixed(2) }}</td>
                <td>R$ {{ parseFloat(item.suggested_sale_value).toFixed(2) }}</td>
                <td>
                  <button class="btn btn-sm btn-success me-2" @click="markAsSold(item)">
                    <i class="bi bi-tag-fill"></i> Vendida
                  </button>
                  <button class="btn btn-sm btn-danger" @click="deleteStockItem(item.id)">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addItemModalLabel">Adicionar Novo Item ao Estoque</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveStockItem">
            <div class="modal-body">
              <div class="mb-3">
                <label for="description" class="form-label">Descrição do Item</label>
                <input type="text" class="form-control" id="description" v-model="newItem.description" required>
              </div>
              <div class="mb-3">
                <label for="wholesale_value" class="form-label">Valor Atacado (Custo)</label>
                <input type="number" step="0.01" class="form-control" id="wholesale_value" v-model="newItem.wholesale_value" required>
              </div>
              <div class="mb-3">
                <label for="suggested_sale_value" class="form-label">Valor de Venda Sugerido</label>
                <input type="number" step="0.01" class="form-control" id="suggested_sale_value" v-model="newItem.suggested_sale_value" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Adicionar ao Estoque</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <AddEditClientModal
      modalId="addClientFromStockModal"
      :clientData="clientDataForModal"
      :isEditing="false"
      :isFromStock="true"
      @client-saved="handleClientSavedFromStock"
      ref="addClientFromStockModalRef"
    />

  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import * as bootstrap from 'bootstrap';
import AddEditClientModal from './AddEditClientModal.vue'; // Importe o novo componente do modal

const stockItems = ref([]);
const loading = ref(true);
const newItem = reactive({
  description: '',
  wholesale_value: 0.00,
  suggested_sale_value: 0.00,
});

// Objeto para passar dados ao modal de cliente, reativo para pré-preenchimento
const clientDataForModal = reactive({
    id_from_stock: null, // ID do item de estoque para ser removido após a venda
    name: '',
    order_description: '',
    order_value: 0.00,
    wholesale_value: 0.00,
    amount_paid: 0.00,
    order_date: new Date().toISOString().slice(0, 10),
    city: '',
    delivered: false,
});

let addItemModalInstance = null; // Instância do modal para adicionar item
const addClientFromStockModalRef = ref(null); // Referência para o componente do modal de cliente

// --- Funções para Itens de Estoque ---
const fetchStockItems = () => {
  loading.value = true;
  axios.get('/api/stock-items')
    .then(response => {
      stockItems.value = response.data;
    })
    .catch(error => {
      console.error('Erro ao buscar itens do estoque:', error);
      stockItems.value = [];
    })
    .finally(() => {
      loading.value = false;
    });
};

const openAddItemModal = () => {
  Object.assign(newItem, {
    description: '',
    wholesale_value: 0.00,
    suggested_sale_value: 0.00,
  });
  addItemModalInstance.show();
};

const saveStockItem = () => {
  axios.post('/api/stock-items', newItem)
    .then(response => {
      console.log('Item adicionado ao estoque:', response.data);
      fetchStockItems();
      addItemModalInstance.hide();
    })
    .catch(error => {
      console.error('Erro ao adicionar item ao estoque:', error);
      alert('Erro ao adicionar item: ' + (error.response?.data?.message || 'Verifique os dados.'));
    });
};

const deleteStockItem = (id) => {
  if (confirm('Tem certeza que deseja remover este item do estoque?')) {
    axios.delete(`/api/stock-items/${id}`)
      .then(() => {
        console.log('Item removido do estoque:', id);
        fetchStockItems();
      })
      .catch(error => {
        console.error('Erro ao remover item do estoque:', error);
        alert('Erro ao remover item.');
      });
  }
};

// --- Funções para Venda (Estoque -> Cliente) ---
const markAsSold = (item) => {
    // Preenche clientDataForModal com os dados do item do estoque
    Object.assign(clientDataForModal, {
        id_from_stock: item.id, // Guarda o ID do item de estoque para remover depois
        name: '', // Sempre vazio para nova venda
        order_description: item.description,
        order_value: parseFloat(item.suggested_sale_value).toFixed(2),
        wholesale_value: parseFloat(item.wholesale_value).toFixed(2),
        amount_paid: 0.00, // Inicia com 0
        order_date: new Date().toISOString().slice(0, 10),
        city: '',
        delivered: false,
    });
    // Usa a referência para o componente do modal para chamá-lo
    addClientFromStockModalRef.value.showModal();
};

const handleClientSavedFromStock = (newClientData) => {
    // Este método é chamado quando o cliente é salvo via o modal.
    // Agora precisamos remover o item original do estoque.
    if (clientDataForModal.id_from_stock) {
        axios.delete(`/api/stock-items/${clientDataForModal.id_from_stock}`)
            .then(() => {
                console.log('Item do estoque removido após venda e cliente adicionado.');
                fetchStockItems(); // Atualiza a lista de estoque
                alert('Item vendido e cliente adicionado com sucesso!');
                // Resetar clientDataForModal para o próximo uso
                Object.assign(clientDataForModal, {
                    id_from_stock: null, name: '', order_description: '', order_value: 0.00,
                    wholesale_value: 0.00, amount_paid: 0.00, order_date: new Date().toISOString().slice(0, 10),
                    city: '', delivered: false,
                });
            })
            .catch(error => {
                console.error('Erro ao remover item do estoque após venda:', error);
                alert('Cliente salvo, mas erro ao remover item do estoque. Remova manualmente, por favor.');
            });
    } else {
        alert('Cliente salvo, mas nenhum item de estoque associado para remover.');
    }
};


// --- Ciclo de Vida ---
onMounted(() => {
  fetchStockItems();
  // Inicializa o modal de adicionar item (este modal não é o reutilizável)
  addItemModalInstance = new bootstrap.Modal(document.getElementById('addItemModal'));
});
</script>

<style scoped>
.card {
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.table-responsive {
  border-radius: 0.5rem;
  overflow: hidden;
}
</style>