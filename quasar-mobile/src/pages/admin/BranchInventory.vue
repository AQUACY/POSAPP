<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Branch Inventory</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Item"
        @click="openInventoryDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search inventory..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.category"
          :options="categories"
          dense
          outlined
          label="Filter by Category"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filter.stock"
          :options="stockOptions"
          dense
          outlined
          label="Stock Status"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
    </div>

    <!-- Inventory Table -->
    <q-table
      :rows="inventory"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Stock Status Column -->
      <template v-slot:body-cell-stock_status="props">
        <q-td :props="props">
          <q-chip
            :color="getStockStatusColor(props.row)"
            text-color="white"
            dense
          >
            {{ getStockStatus(props.row) }}
          </q-chip>
        </q-td>
      </template>

      <!-- Actions Column -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="edit"
            @click="openInventoryDialog(props.row)"
          >
            <q-tooltip>Edit Item</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="add_shopping_cart"
            @click="openStockDialog(props.row, 'addition')"
          >
            <q-tooltip>Add Stock</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="warning"
            icon="edit_note"
            @click="openStockDialog(props.row, 'adjustment')"
          >
            <q-tooltip>Adjust Stock</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="secondary"
            icon="history"
            @click="openStockHistoryDialog(props.row)"
          >
            <q-tooltip>Stock History</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="negative"
            icon="delete"
            @click="confirmDelete(props.row)"
          >
            <q-tooltip>Delete</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Inventory Dialog -->
    <q-dialog v-model="inventoryDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingItem.id ? 'Edit' : 'Add' }} Inventory Item</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingItem.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingItem.description"
              label="Description"
              type="textarea"
              outlined
            />

            <q-input
              v-model="editingItem.sku"
              label="SKU"
              :rules="[val => !!val || 'SKU is required']"
              outlined
            />

            <q-input
              v-model="editingItem.barcode"
              label="Barcode"
              outlined
            />

            <q-input
              v-model.number="editingItem.quantity"
              label="Quantity"
              type="number"
              :rules="[val => val >= 0 || 'Quantity must be positive']"
              outlined
            />

            <q-input
              v-model.number="editingItem.unit_price"
              label="Unit Price"
              type="number"
              :rules="[val => val >= 0 || 'Price must be positive']"
              outlined
            />

            <q-input
              v-model.number="editingItem.cost_price"
              label="Cost Price"
              type="number"
              :rules="[val => val >= 0 || 'Cost must be positive']"
              outlined
            />

            <q-input
              v-model.number="editingItem.reorder_level"
              label="Reorder Level"
              type="number"
              :rules="[val => val >= 0 || 'Reorder level must be positive']"
              outlined
            />

            <q-select
              v-model="editingItem.category_id"
              :options="categories"
              label="Category"
              :rules="[val => !!val || 'Category is required']"
              outlined
              emit-value
              map-options
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Stock Dialog -->
    <q-dialog v-model="stockDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">{{ stockData.change_type === 'addition' ? 'Add Stock' : 'Adjust Stock' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onStockSubmit" class="q-gutter-md">
            <q-input
              v-model.number="stockData.quantity"
              :label="stockData.change_type === 'addition' ? 'Quantity to Add' : 'New Total Quantity'"
              type="number"
              :rules="[
                val => val > 0 || 'Quantity must be positive',
                val => stockData.change_type === 'addition' || val >= 0 || 'Total quantity cannot be negative'
              ]"
              outlined
            />
            <q-input
              v-model="stockData.reason"
              :label="stockData.change_type === 'addition' ? 'Reason for Addition' : 'Reason for Adjustment'"
              type="textarea"
              :rules="[val => !!val || 'Reason is required']"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat :label="stockData.change_type === 'addition' ? 'Add' : 'Adjust'" @click="onStockSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Stock History Dialog -->
    <q-dialog v-model="stockHistoryDialog" persistent>
      <q-card style="min-width: 700px">
        <q-card-section class="row items-center">
          <div class="text-h6">Stock History</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-table
            :rows="stockHistory"
            :columns="stockHistoryColumns"
            row-key="id"
            :loading="historyLoading"
            :pagination-label="(firstRow, endRow, totalRows) => `${firstRow}-${endRow} of ${totalRows}`"
          >
            <template v-slot:body-cell-change_type="props">
              <q-td :props="props">
                <q-chip
                  :color="getChangeTypeColor(props.row.change_type)"
                  text-color="white"
                  dense
                >
                  {{ props.row.change_type }}
                </q-chip>
              </q-td>
            </template>
            <template v-slot:body-cell-created_at="props">
              <q-td :props="props">
                {{ new Date(props.row.created_at).toLocaleString() }}
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this item?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="onDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'

export default {
  name: 'BranchInventoryPage',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const inventory = ref([])
    const categories = ref([])
    const inventoryDialog = ref(false)
    const stockDialog = ref(false)
    const deleteDialog = ref(false)
    const stockHistoryDialog = ref(false)
    const stockHistory = ref([])
    const historyLoading = ref(false)
    const editingItem = ref({
      name: '',
      description: '',
      sku: '',
      barcode: '',
      quantity: 0,
      unit_price: 0,
      cost_price: 0,
      reorder_level: 0,
      category_id: null,
      business_id: null,
      branch_id: null
    })
    const stockData = ref({
      quantity: 0,
      reason: '',
      change_type: 'addition'
    })
    const itemToDelete = ref(null)

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'sku', label: 'SKU', field: 'sku', sortable: true },
      { name: 'barcode', label: 'Barcode', field: 'barcode' },
      { name: 'quantity', label: 'Quantity', field: 'quantity', sortable: true },
      { name: 'unit_price', label: 'Unit Price', field: 'unit_price', sortable: true },
      { name: 'cost_price', label: 'Cost Price', field: 'cost_price', sortable: true },
      { name: 'stock_status', label: 'Stock Status', field: 'stock_status' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const stockOptions = [
      { label: 'All', value: null },
      { label: 'In Stock', value: 'in_stock' },
      { label: 'Low Stock', value: 'low_stock' },
      { label: 'Out of Stock', value: 'out_of_stock' }
    ]

    const filter = ref({
      search: '',
      category: null,
      stock: null
    })

    const pagination = ref({
      sortBy: 'name',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const stockHistoryColumns = [
      { name: 'created_at', align: 'left', label: 'Date', field: 'created_at', sortable: true },
      { name: 'change_type', align: 'left', label: 'Type', field: 'change_type', sortable: true },
      { name: 'quantity', align: 'right', label: 'Quantity', field: 'quantity', sortable: true },
      { name: 'reason', align: 'left', label: 'Reason', field: 'reason' },
      { name: 'user', align: 'left', label: 'Updated By', field: row => row.user?.name }
    ]

    const fetchInventory = async () => {
      loading.value = true
      try {
        const response = await api.get(`/business/${route.params.businessId}/branch/${route.params.branchId}/inventory`, {
          params: {
            ...filter.value,
            page: pagination.value.page,
            per_page: pagination.value.rowsPerPage,
            sort_by: pagination.value.sortBy,
            descending: pagination.value.descending
          }
        })
        inventory.value = response.data
        pagination.value.rowsNumber = response.data.length
      } catch (error) {
        console.error('Error fetching inventory:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch inventory',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchCategories = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/categories`)
        categories.value = response.data.map(category => ({
          label: category.name,
          value: category.id
        }))
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    }

    const openInventoryDialog = (item = null) => {
      if (item) {
        editingItem.value = { ...item }
      } else {
        editingItem.value = {
          name: '',
          description: '',
          sku: '',
          barcode: '',
          quantity: 0,
          unit_price: 0,
          cost_price: 0,
          reorder_level: 0,
          category_id: null,
          business_id: route.params.businessId,
          branch_id: route.params.branchId
        }
      }
      inventoryDialog.value = true
    }

    const openStockDialog = (item, type) => {
      editingItem.value = item
      stockData.value = {
        quantity: type === 'adjustment' ? item.quantity : 0,
        reason: '',
        change_type: type
      }
      stockDialog.value = true
    }

    const openStockHistoryDialog = async (item) => {
      historyLoading.value = true
      try {
        const response = await api.get(`/inventory/${item.id}/stock-history`)
        stockHistory.value = response.data
        stockHistoryDialog.value = true
      } catch (error) {
        console.error('Error fetching stock history:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch stock history',
          icon: 'error'
        })
      } finally {
        historyLoading.value = false
      }
    }

    const onSubmit = async () => {
      try {
        if (editingItem.value.id) {
          await api.put(`/admin/inventory/${editingItem.value.id}`, editingItem.value)
        } else {
          await api.post('/inventory', editingItem.value)
        }
        inventoryDialog.value = false
        fetchInventory()
        $q.notify({
          color: 'positive',
          message: `Item ${editingItem.value.id ? 'updated' : 'created'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error saving item:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to save item',
          icon: 'error'
        })
      }
    }

    const onStockSubmit = async () => {
      try {
        await api.post(`/inventory/${editingItem.value.id}/stock`, stockData.value)
        stockDialog.value = false
        fetchInventory()
        $q.notify({
          color: 'positive',
          message: `Stock ${stockData.value.change_type === 'addition' ? 'added' : 'adjusted'} successfully`,
          icon: 'check'
        })
      } catch (error) {
        console.error('Error updating stock:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to update stock',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (item) => {
      itemToDelete.value = item
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/inventory/${itemToDelete.value.id}`)
        fetchInventory()
        $q.notify({
          color: 'positive',
          message: 'Item deleted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error deleting item:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to delete item',
          icon: 'error'
        })
      }
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      fetchInventory()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchInventory()
    }

    const getStockStatus = (item) => {
      if (item.quantity <= 0) return 'Out of Stock'
      if (item.quantity <= item.reorder_level) return 'Low Stock'
      return 'In Stock'
    }

    const getStockStatusColor = (item) => {
      if (item.quantity <= 0) return 'negative'
      if (item.quantity <= item.reorder_level) return 'warning'
      return 'positive'
    }

    const getChangeTypeColor = (type) => {
      switch (type) {
        case 'addition':
          return 'positive'
        case 'reduction':
          return 'negative'
        case 'adjustment':
          return 'warning'
        default:
          return 'grey'
      }
    }

    onMounted(() => {
      fetchInventory()
      fetchCategories()
    })

    return {
      loading,
      inventory,
      categories,
      columns,
      stockOptions,
      filter,
      pagination,
      inventoryDialog,
      stockDialog,
      deleteDialog,
      stockHistoryDialog,
      stockHistory,
      historyLoading,
      stockHistoryColumns,
      editingItem,
      stockData,
      openInventoryDialog,
      openStockDialog,
      openStockHistoryDialog,
      onSubmit,
      onStockSubmit,
      confirmDelete,
      onDelete,
      onRequest,
      onSearch,
      getStockStatus,
      getStockStatusColor,
      getChangeTypeColor
    }
  }
}
</script> 