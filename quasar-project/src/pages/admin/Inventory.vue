<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Inventory</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Item"
        @click="openItemDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search items..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-3">
        <q-select
          v-model="filter.branch"
          :options="branches"
          dense
          outlined
          label="Filter by Branch"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
      <div class="col-12 col-md-3">
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
      <div class="col-12 col-md-3">
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
      :rows="items"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Stock Level Column -->
      <template v-slot:body-cell-stock_level="props">
        <q-td :props="props">
          <q-chip
            :color="getStockLevelColor(props.row.stock_level)"
            text-color="white"
            dense
          >
            {{ props.row.stock_level }}
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
            @click="openItemDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="add_circle"
            @click="openStockDialog(props.row)"
          >
            <q-tooltip>Adjust Stock</q-tooltip>
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

    <!-- Item Dialog -->
    <q-dialog v-model="itemDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingItem.id ? 'Edit' : 'Add' }} Item</div>
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
              v-model="editingItem.sku"
              label="SKU"
              :rules="[val => !!val || 'SKU is required']"
              outlined
            />

            <q-input
              v-model="editingItem.description"
              label="Description"
              type="textarea"
              outlined
            />

            <q-input
              v-model.number="editingItem.unit_price"
              label="Unit Price"
              type="number"
              :rules="[
                val => !!val || 'Price is required',
                val => val > 0 || 'Price must be greater than 0'
              ]"
              outlined
            />

            <q-input
              v-model.number="editingItem.cost_price"
              label="Cost Price"
              type="number"
              :rules="[
                val => !!val || 'Cost price is required',
                val => val > 0 || 'Cost price must be greater than 0'
              ]"
              outlined
            />

            <q-input
              v-model.number="editingItem.quantity"
              label="Stock Level"
              type="number"
              :rules="[
                val => val >= 0 || 'Stock level cannot be negative'
              ]"
              outlined
            />

            <q-input
              v-model.number="editingItem.reorder_level"
              label="Reorder Level"
              type="number"
              :rules="[
                val => val >= 0 || 'Reorder level cannot be negative'
              ]"
              outlined
            />

            <q-select
              v-model="editingItem.warehouse_id"
              :options="warehouses"
              label="Warehouse"
              outlined
              emit-value
              map-options
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

            <q-select
              v-model="editingItem.branch_id"
              :options="branches"
              label="Branch"
              :rules="[val => !!val || 'Branch is required']"
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

    <!-- Stock Adjustment Dialog -->
    <q-dialog v-model="stockDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Adjust Stock</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onStockSubmit" class="q-gutter-md">
            <div class="text-subtitle2">Current Stock: {{ selectedItem?.stock_level }}</div>

            <q-input
              v-model.number="stockAdjustment.quantity"
              label="Adjustment Quantity"
              type="number"
              :rules="[val => !!val || 'Quantity is required']"
              outlined
            />

            <q-select
              v-model="stockAdjustment.change_type"
              :options="adjustmentTypes"
              label="Adjustment Type"
              :rules="[val => !!val || 'Type is required']"
              outlined
              emit-value
              map-options
            />

            <q-input
              v-model="stockAdjustment.reason"
              label="Reason"
              type="textarea"
              :rules="[val => !!val || 'Reason is required']"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onStockSubmit" />
        </q-card-actions>
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
  name: 'InventoryPage',

  setup () {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const items = ref([])
    const branches = ref([])
    const categories = ref([])
    const warehouses = ref([])
    const itemDialog = ref(false)
    const stockDialog = ref(false)
    const deleteDialog = ref(false)
    const selectedItem = ref(null)

    const editingItem = ref({
      name: '',
      sku: '',
      description: '',
      unit_price: 0,
      cost_price: 0,
      quantity: 0,
      reorder_level: 0,
      warehouse_id: null,
      category_id: null,
      branch_id: null,
      business_id: null
    })

    const stockAdjustment = ref({
      quantity: 0,
      change_type: 'addition',
      reason: ''
    })

    const columns = [
      { name: 'name', label: 'Name', field: 'name', sortable: true },
      { name: 'sku', label: 'SKU', field: 'sku', sortable: true },
      { name: 'category', label: 'Category', field: row => row.category?.name, sortable: true },
      { name: 'branch', label: 'Branch', field: row => row.branch?.name, sortable: true },
      { 
        name: 'unit_price', 
        label: 'Unit Price', 
        field: 'unit_price', 
        sortable: true, 
        format: val => `$${Number(val).toFixed(2)}` 
      },
      { 
        name: 'cost_price', 
        label: 'Cost Price', 
        field: 'cost_price', 
        sortable: true, 
        format: val => `$${Number(val).toFixed(2)}` 
      },
      { 
        name: 'quantity', 
        label: 'Stock Level', 
        field: 'quantity', 
        sortable: true,
        format: val => Number(val).toFixed(2)
      },
      { 
        name: 'reorder_level', 
        label: 'Reorder Level', 
        field: 'reorder_level', 
        sortable: true,
        format: val => Number(val).toFixed(2)
      },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
    ]

    const stockOptions = [
      { label: 'All', value: null },
      { label: 'In Stock', value: 'in_stock' },
      { label: 'Low Stock', value: 'low_stock' },
      { label: 'Out of Stock', value: 'out_of_stock' }
    ]

    const adjustmentTypes = [
      { label: 'Add Stock', value: 'addition' },
      { label: 'Remove Stock', value: 'reduction' },
      { label: 'Adjust Stock', value: 'adjustment' }
    ]

    const filter = ref({
      search: '',
      branch: route.query.branch_id || null,
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

    const fetchItems = async () => {
      loading.value = true
      try {
              const response = await api.get(`/business/${route.params.businessId}/inventory`, {
        params: {
          ...filter.value,
          page: pagination.value.page,
          per_page: pagination.value.rowsPerPage,
          sort_by: pagination.value.sortBy,
          descending: pagination.value.descending
        }
      })
      items.value = response.data.data
      pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Error fetching items:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to fetch items',
          icon: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const fetchBranches = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/branches`)
        branches.value = response.data.map(branch => ({
          label: branch.name,
          value: branch.id
        }))
      } catch (error) {
        console.error('Error fetching branches:', error)
      }
    }
    const fetchWarehouses = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/warehouses`)
        warehouses.value = response.data.map(warehouse => ({
          label: warehouse.name,
          value: warehouse.id
        }))

        console.log(warehouses.value)
      } catch (error) {
        console.error('Error fetching warehouses:', error)}
      }

    const fetchCategories = async () => {
      try {
        const response = await api.get(`/business/${route.params.businessId}/categories`)
        categories.value = response.data.data.map(category => ({
          label: category.name,
          value: category.id
        }))
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    }

    const openItemDialog = (item = null) => {
      if (item) {
        editingItem.value = { ...item }
      } else {
        editingItem.value = {
          name: '',
          sku: '',
          description: '',
          unit_price: 0,
          cost_price: 0,
          quantity: 0,
          reorder_level: 0,
          category_id: null,
          warehouse_id: null,
          branch_id: filter.value.branch,
          business_id: route.params.businessId
        }
      }
      itemDialog.value = true
    }

    const openStockDialog = (item) => {
      selectedItem.value = item
      stockAdjustment.value = {
        quantity: 0,
        change_type: 'addition',
        reason: ''
      }
      stockDialog.value = true
    }

    const onSubmit = async () => {
      try {
        if (editingItem.value.id) {
          await api.put(`/admin/inventory/${editingItem.value.id}`, editingItem.value)
        } else {
          await api.post('/admin/inventory', editingItem.value)
        }
        itemDialog.value = false
        fetchItems()
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
        await api.post(`/admin/inventory/${selectedItem.value.id}/add-stock`, stockAdjustment.value)
        stockDialog.value = false
        fetchItems()
        $q.notify({
          color: 'positive',
          message: 'Stock adjusted successfully',
          icon: 'check'
        })
      } catch (error) {
        console.error('Error adjusting stock:', error)
        $q.notify({
          color: 'negative',
          message: 'Failed to adjust stock',
          icon: 'error'
        })
      }
    }

    const confirmDelete = (item) => {
      selectedItem.value = item
      deleteDialog.value = true
    }

    const onDelete = async () => {
      try {
        await api.delete(`/admin/inventory/${selectedItem.value.id}`)
        fetchItems()
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
      fetchItems()
    }

    const onSearch = () => {
      pagination.value.page = 1
      fetchItems()
    }

    const getStockLevelColor = (level) => {
      if (level <= 0) return 'negative'
      if (level <= selectedItem.value?.reorder_level) return 'warning'
      return 'positive'
    }

    onMounted(() => {
      fetchItems()
      fetchBranches()
      fetchCategories()
      fetchWarehouses()

    })

    return {
      loading,
      items,
      branches,
      categories,
      warehouses,
      columns,
      stockOptions,
      adjustmentTypes,
      filter,
      pagination,
      itemDialog,
      stockDialog,
      deleteDialog,
      editingItem,
      selectedItem,
      stockAdjustment,
      openItemDialog,
      openStockDialog,
      onSubmit,
      onStockSubmit,
      confirmDelete,
      onDelete,
      onRequest,
      onSearch,
      getStockLevelColor
    }
  }
}
</script>