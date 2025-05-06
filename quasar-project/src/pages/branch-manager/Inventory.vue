<template>
  <q-page padding>
    <div class="row q-mb-md items-center justify-between">
      <div class="text-h5">Inventory Management</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Item"
        @click="openAddDialog"
      />
    </div>

    <div class="row q-mb-md">
      <q-input
        v-model="filter"
        dense
        outlined
        placeholder="Search items..."
        class="col-12 col-md-4"
        @update:model-value="loadData"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>
    </div>

    <q-table
      :rows="inventoryItems"
      :columns="columns"
      :loading="loading"
      :pagination="pagination"
      row-key="id"
      @request="onRequest"
    >
      <template v-slot:body-cell-quantity="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.quantity <= props.row.reorder_level ? 'negative' : 'positive'"
            text-color="white"
            dense
          >
            {{ props.row.quantity }}
          </q-chip>
        </q-td>
      </template>

      <template v-slot:body-cell-unit_price="props">
        <q-td :props="props">
          ${{ parseFloat(props.row.unit_price).toFixed(2) }}
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="edit"
            size="sm"
            @click="openEditDialog(props.row)"
          >
            <q-tooltip>Edit Item</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="negative"
            icon="delete"
            size="sm"
            @click="handleDelete(props.row.id)"
          >
            <q-tooltip>Delete Item</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ isEditing ? 'Edit Item' : 'Add New Item' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="form.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="form.description"
              label="Description"
              type="textarea"
              outlined
            />

            <q-input
              v-model="form.sku"
              label="SKU"
              :rules="[val => !!val || 'SKU is required']"
              outlined
            />

            <q-input
              v-model="form.barcode"
              label="Barcode"
              outlined
            />

            <q-input
              v-model.number="form.quantity"
              label="Quantity"
              type="number"
              :rules="[val => val >= 0 || 'Quantity cannot be negative']"
              outlined
            />

            <q-input
              v-model.number="form.unit_price"
              label="Unit Price"
              type="number"
              :rules="[val => val >= 0 || 'Price must be greater than 0']"
              outlined
            >
              <template v-slot:prepend>$</template>
            </q-input>

            <q-input
              v-model.number="form.cost_price"
              label="Cost Price"
              type="number"
              :rules="[val => val >= 0 || 'Cost must be greater than 0']"
              outlined
            >
              <template v-slot:prepend>$</template>
            </q-input>

            <q-input
              v-model.number="form.reorder_level"
              label="Reorder Level"
              type="number"
              :rules="[val => val >= 0 || 'Reorder level cannot be negative']"
              outlined
            />

            <q-select
              v-model="form.category_id"
              :options="categories"
              label="Category"
              outlined
              emit-value
              map-options
            />

            <q-checkbox
              v-model="form.is_non_refundable"
              label="Non-refundable"
            />

            <q-checkbox
              v-model="form.requires_condition_check"
              label="Requires condition check"
            />

            <q-input
              v-if="form.requires_condition_check"
              v-model="form.condition_notes"
              label="Condition Notes"
              type="textarea"
              outlined
            />

            <q-input
              v-model.number="form.refund_restriction_amount"
              label="Refund Restriction Amount"
              type="number"
              outlined
            >
              <template v-slot:prepend>$</template>
            </q-input>

            <q-input
              v-model.number="form.refund_restriction_days"
              label="Refund Restriction Days"
              type="number"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Save" color="primary" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useBranchManagerStore } from 'stores/branchManager'
import { api } from 'src/boot/axios'

export default {
  name: 'InventoryPage',
  setup() {
    const $q = useQuasar()
    const route = useRoute()
    const store = useBranchManagerStore()
    const loading = ref(false)
    const filter = ref('')
    const showDialog = ref(false)
    const isEditing = ref(false)
    const currentItemId = ref(null)
    const categories = ref([])

    const pagination = ref({
      sortBy: 'name',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const form = ref({
      name: '',
      description: '',
      sku: '',
      barcode: '',
      quantity: 0,
      unit_price: 0,
      cost_price: 0,
      reorder_level: 0,
      category_id: null,
      is_non_refundable: false,
      requires_condition_check: false,
      condition_notes: null,
      refund_restriction_amount: null,
      refund_restriction_days: null
    })

    const columns = [
      {
        name: 'id',
        label: 'ID',
        field: 'id',
        sortable: true,
        align: 'left'
      },
      {
        name: 'name',
        label: 'Name',
        field: 'name',
        sortable: true,
        align: 'left'
      },
      {
        name: 'description',
        label: 'Description',
        field: 'description',
        align: 'left'
      },
      {
        name: 'sku',
        label: 'SKU',
        field: 'sku',
        sortable: true,
        align: 'left'
      },
      {
        name: 'quantity',
        label: 'Stock Level',
        field: 'quantity',
        sortable: true,
        align: 'center'
      },
      {
        name: 'unit_price',
        label: 'Price',
        field: 'unit_price',
        sortable: true,
        align: 'right'
      },
      {
        name: 'category',
        label: 'Category',
        field: row => row.category?.name,
        sortable: true,
        align: 'left'
      },
      {
        name: 'actions',
        label: 'Actions',
        field: 'actions',
        align: 'center'
      }
    ]

    const inventoryItems = computed(() => {
      console.log('this is the inventory data ', store.inventory)
      return store.inventory?.data?.data || []
    })

    const fetchCategories = async () => {
      try {
        const response = await api.get('/categories')
        categories.value = response.data.data.map(category => ({
          label: category.name,
          value: category.id,
          description: category.description
        }))
      } catch (error) {
        console.error('Failed to fetch categories:', error)
        $q.notify({
          type: 'negative',
          message: 'Failed to load categories'
        })
      }
    }

    const loadData = async () => {
      loading.value = true
      try {
        const { businessId, branchId } = route.params
        await store.fetchInventory(businessId, branchId, {
          filter: filter.value,
          page: pagination.value.page,
          rowsPerPage: pagination.value.rowsPerPage,
          sortBy: pagination.value.sortBy,
          descending: pagination.value.descending
        })
        pagination.value.rowsNumber = store.inventory?.data?.total || 0
      } catch {
        $q.notify({
          type: 'negative',
          message: 'Failed to load inventory items'
        })
      } finally {
        loading.value = false
      }
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      loadData()
    }

    const openAddDialog = () => {
      isEditing.value = false
      currentItemId.value = null
      form.value = {
        name: '',
        description: '',
        sku: '',
        barcode: '',
        quantity: 0,
        unit_price: 0,
        cost_price: 0,
        reorder_level: 0,
        category_id: null,
        is_non_refundable: false,
        requires_condition_check: false,
        condition_notes: null,
        refund_restriction_amount: null,
        refund_restriction_days: null
      }
      showDialog.value = true
    }

    const openEditDialog = (item) => {
      isEditing.value = true
      currentItemId.value = item.id
      form.value = {
        name: item.name,
        description: item.description,
        sku: item.sku,
        barcode: item.barcode,
        quantity: item.quantity,
        unit_price: parseFloat(item.unit_price),
        cost_price: parseFloat(item.cost_price),
        reorder_level: item.reorder_level,
        category_id: item.category_id,
        is_non_refundable: item.is_non_refundable,
        requires_condition_check: item.requires_condition_check,
        condition_notes: item.condition_notes,
        refund_restriction_amount: item.refund_restriction_amount,
        refund_restriction_days: item.refund_restriction_days
      }
      showDialog.value = true
    }

    const onSubmit = async () => {
      try {
        const { businessId, branchId } = route.params
        if (isEditing.value) {
          await store.updateInventory(businessId, branchId, currentItemId.value, form.value)
          $q.notify({
            type: 'positive',
            message: 'Item updated successfully'
          })
        } else {
          await store.createInventory(businessId, branchId, form.value)
          $q.notify({
            type: 'positive',
            message: 'Item created successfully'
          })
        }
        showDialog.value = false
        loadData()
      } catch {
        $q.notify({
          type: 'negative',
          message: isEditing.value ? 'Failed to update item' : 'Failed to create item'
        })
      }
    }

    const handleDelete = async (itemId) => {
      try {
        await $q.dialog({
          title: 'Confirm',
          message: 'Are you sure you want to delete this item?',
          cancel: true,
          persistent: true
        })

        const { businessId, branchId } = route.params
        await store.deleteInventoryItem(businessId, branchId, itemId)
        $q.notify({
          type: 'positive',
          message: 'Item deleted successfully'
        })
        loadData()
      } catch {
        $q.notify({
          type: 'negative',
          message: 'Failed to delete item'
        })
      }
    }

    onMounted(async () => {
      await Promise.all([
        loadData(),
        fetchCategories()
      ])
    })

    return {
      loading,
      filter,
      showDialog,
      isEditing,
      form,
      columns,
      categories,
      pagination,
      inventoryItems,
      onRequest,
      openAddDialog,
      openEditDialog,
      onSubmit,
      handleDelete,
      loadData
    }
  }
}
</script>
