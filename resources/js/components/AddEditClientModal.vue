<template>
  <div class="modal fade" :id="modalId" tabindex="-1" :aria-labelledby="`${modalId}Label`" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" :id="`${modalId}Label`">{{ isEditing ? 'Editar Cliente' : 'Adicionar Novo Cliente' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form @submit.prevent="saveClient">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="clientName" class="form-label">Nome do Cliente</label>
                <input type="text" class="form-control" id="clientName" v-model="localClient.name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="orderValue" class="form-label">Valor do Pedido</label>
                <input type="number" step="0.01" class="form-control" id="orderValue" v-model="localClient.order_value" required :readonly="isFromStock">
              </div>
            </div>
            <div class="mb-3">
              <label for="orderDescription" class="form-label">Descrição do Pedido</label>
              <textarea class="form-control" id="orderDescription" rows="3" v-model="localClient.order_description" :readonly="isFromStock"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="wholesaleValue" class="form-label">Valor Atacado</label>
                <input type="number" step="0.01" class="form-control" id="wholesaleValue" v-model="localClient.wholesale_value" required :readonly="isFromStock">
              </div>
              <div class="col-md-6 mb-3">
                <label for="orderDate" class="form-label">Data do Pedido</label>
                <input type="date" class="form-control" id="orderDate" v-model="localClient.order_date" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="clientCity" class="form-label">Cidade</label>
              <input type="text" class="form-control" id="clientCity" v-model="localClient.city">
            </div>
            <div class="mb-3">
              <label for="amountPaid" class="form-label">Valor Pago</label>
              <input type="number" step="0.01" class="form-control" id="amountPaid" v-model="localClient.amount_paid" min="0">
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="delivered" v-model="localClient.delivered">
              <label class="form-check-label" for="delivered">Entregue</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">{{ isEditing ? 'Salvar Alterações' : 'Adicionar Cliente' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, reactive, onMounted } from 'vue';
import axios from 'axios';
import * as bootstrap from 'bootstrap'; // Importa Bootstrap JS para controlar o modal

// Define as propriedades que o componente pode receber
const props = defineProps({
  modalId: { // ID único para o modal (ex: 'addClientModal', 'editClientModal')
    type: String,
    required: true,
  },
  clientData: { // Dados iniciais do cliente para pré-preenchimento (opcional para adicionar, necessário para editar)
    type: Object,
    default: () => ({
      name: '',
      order_description: '',
      order_value: 0.00,
      wholesale_value: 0.00,
      amount_paid: 0.00,
      order_date: new Date().toISOString().slice(0, 10), // Data atual por padrão
      city: '',
      delivered: false,
    }),
  },
  isEditing: { // Indica se o modal está em modo de edição
    type: Boolean,
    default: false,
  },
  isFromStock: { // Indica se os dados vieram do estoque (para campos readonly)
    type: Boolean,
    default: false,
  },
});

// Define os eventos que o componente pode emitir
const emit = defineEmits(['client-saved', 'client-deleted']);

// Cria uma cópia local dos dados do cliente para edição/adição no formulário
// Isso evita modificar as props diretamente
const localClient = reactive({});
// Uma instância do modal do Bootstrap
let modalInstance = null;

// Observa mudanças em clientData e atualiza localClient
// Isso é crucial para o pré-preenchimento ao abrir o modal, especialmente vindo do estoque
watch(() => props.clientData, (newVal) => {
  if (newVal) {
    // Usa Object.assign para copiar as propriedades sem perder a reatividade do reactive()
    Object.assign(localClient, {
      id: newVal.id || null, // Se for edição, terá um ID
      name: newVal.name || '',
      order_description: newVal.order_description || '',
      order_value: parseFloat(newVal.order_value || 0).toFixed(2),
      wholesale_value: parseFloat(newVal.wholesale_value || 0).toFixed(2),
      amount_paid: parseFloat(newVal.amount_paid || 0).toFixed(2),
      order_date: newVal.order_date || new Date().toISOString().slice(0, 10),
      city: newVal.city || '',
      delivered: newVal.delivered || false,
    });
  } else {
    // Reseta para valores padrão se clientData for nulo (para adicionar novo)
    resetForm();
  }
}, { immediate: true, deep: true }); // 'immediate' para rodar na montagem, 'deep' para objetos aninhados

// Função para resetar o formulário
const resetForm = () => {
    Object.assign(localClient, {
        id: null,
        name: '',
        order_description: '',
        order_value: 0.00,
        wholesale_value: 0.00,
        amount_paid: 0.00,
        order_date: new Date().toISOString().slice(0, 10),
        city: '',
        delivered: false,
    });
};

// Função para salvar o cliente (adicionar ou editar)
const saveClient = () => {
  const payload = {
    ...localClient,
    paid: parseFloat(localClient.amount_paid) >= parseFloat(localClient.order_value), // Lógica para 'paid'
  };

  const request = props.isEditing
    ? axios.put(`/api/customers/${localClient.id}`, payload) // Requisição PUT para editar
    : axios.post('/api/customers', payload); // Requisição POST para adicionar

  request
    .then(response => {
      console.log('Cliente salvo:', response.data);
      emit('client-saved', response.data); // Emite evento para o componente pai
      modalInstance.hide(); // Fecha o modal
      resetForm(); // Reseta o formulário
    })
    .catch(error => {
      console.error('Erro ao salvar cliente:', error);
      alert('Erro ao salvar cliente: ' + (error.response?.data?.message || 'Verifique os dados.'));
    });
};

// Função para mostrar o modal
const showModal = (clientToEdit = null) => {
    if (clientToEdit) {
        Object.assign(localClient, { // Preenche com dados para edição
            id: clientToEdit.id,
            name: clientToEdit.name,
            order_description: clientToEdit.order_description,
            order_value: parseFloat(clientToEdit.order_value).toFixed(2),
            wholesale_value: parseFloat(clientToEdit.wholesale_value).toFixed(2),
            amount_paid: parseFloat(clientToEdit.amount_paid).toFixed(2),
            order_date: clientToEdit.order_date,
            city: clientToEdit.city,
            delivered: clientToEdit.delivered,
        });
        // A lógica de isEditing já é tratada pelas props, mas podemos explicitamente setar aqui se necessário.
    } else {
        resetForm(); // Reseta para adicionar novo
    }
    modalInstance.show();
};

// Exponha o método showModal para que o componente pai possa chamá-lo via template ref
defineExpose({
  showModal,
});

onMounted(() => {
  modalInstance = new bootstrap.Modal(document.getElementById(props.modalId));
  // Opcional: Escutar o evento 'hidden.bs.modal' para resetar o formulário
  // se o usuário fechar o modal sem salvar.
  document.getElementById(props.modalId).addEventListener('hidden.bs.modal', resetForm);
});
</script>

<style scoped>
/* Adicione estilos específicos se necessário */
</style>