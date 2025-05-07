<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h4">Categories</div>
      <q-btn
        color="primary"
        icon="add"
        label="Add Category"
        @click="openCategoryDialog()"
      />
    </div>

    <!-- Search and Filter -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-6">
        <q-input
          v-model="filter.search"
          dense
          outlined
          placeholder="Search categories..."
          @update:model-value="onSearch"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-6">
        <q-select
          v-model="filter.status"
          :options="statusOptions"
          dense
          outlined
          label="Status"
          emit-value
          map-options
          @update:model-value="onSearch"
        />
      </div>
    </div>

    <!-- Categories Table -->
    <q-table
      :rows="categories"
      :columns="columns"
      row-key="id"
      :loading="loading"
      v-model:pagination="pagination"
      @request="onRequest"
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.is_active ? 'positive' : 'negative'"
            text-color="white"
            dense
          >
            {{ props.row.is_active ? 'Active' : 'Inactive' }}
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
            @click="openCategoryDialog(props.row)"
          >
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="info"
            icon="inventory_2"
            @click="viewItems(props.row)"
          >
            <q-tooltip>View Items</q-tooltip>
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

    <!-- Category Dialog -->
    <q-dialog v-model="categoryDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ editingCategory.id ? 'Edit' : 'Add' }} Category</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmit" class="q-gutter-md">
            <q-input
              v-model="editingCategory.name"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
              outlined
            />

            <q-input
              v-model="editingCategory.description"
              label="Description"
              type="textarea"
              outlined
            />

            <q-toggle
              v-model="editingCategory.is_active"
              label="Active"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this category?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="onDelete" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup name="CategoriesPage">
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import { api } from 'boot/axios'

const $q = useQuasar()
const route = useRoute()
const router = useRouter()

const categories = ref([])
const loading = ref(false)
const categoryDialog = ref(false)
const editingCategory = ref({})
const categoryToDelete = ref(null)
const deleteDialog = ref(false)

const statusOptions = [
  { label: 'All', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

const columns = [
  {
    name: 'name',
    required: true,
    label: 'Name',
    align: 'left',
    field: row => row.name,
    sortable: true
  },
  {
    name: 'description',
    label: 'Description',
    align: 'left',
    field: row => row.description
  },
  {
    name: 'status',
    label: 'Status',
    align: 'left',
    field: row => row.is_active
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'right'
  }
]

const filter = ref({
  search: '',
  status: null
})

const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await api.get(`/business/${route.params.businessId}/categories`, {
      params: {
        ...filter.value,
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
        sort_by: pagination.value.sortBy,
        descending: pagination.value.descending
      }
    })
    categories.value = response.data.data
    pagination.value.rowsNumber = response.data.total
  } catch (error) {
    console.error('Error fetching categories:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to fetch categories',
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}

const openCategoryDialog = (category = null) => {
  if (category) {
    editingCategory.value = { ...category }
  } else {
    editingCategory.value = {
      name: '',
      description: '',
      is_active: true,
      business_id: route.params.businessId
    }
  }
  categoryDialog.value = true
}

const onSubmit = async () => {
  try {
    if (editingCategory.value.id) {
      await api.put(`/categories/${editingCategory.value.id}`, editingCategory.value)
    } else {
      await api.post('/categories', editingCategory.value)
    }
    categoryDialog.value = false
    fetchCategories()
    $q.notify({
      color: 'positive',
      message: `Category ${editingCategory.value.id ? 'updated' : 'created'} successfully`,
      icon: 'check'
    })
  } catch (error) {
    console.error('Error saving category:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to save category',
      icon: 'error'
    })
  }
}

const confirmDelete = (category) => {
  categoryToDelete.value = category
  deleteDialog.value = true
}

const onDelete = async () => {
  try {
    await api.delete(`/categories/${categoryToDelete.value.id}`)
    fetchCategories()
    $q.notify({
      color: 'positive',
      message: 'Category deleted successfully',
      icon: 'check'
    })
  } catch (error) {
    console.error('Error deleting category:', error)
    $q.notify({
      color: 'negative',
      message: 'Failed to delete category',
      icon: 'error'
    })
  }
}

const viewItems = (category) => {
  router.push(`/business/${route.params.businessId}/inventory?category_id=${category.id}`)
}

const onRequest = (props) => {
  pagination.value = props.pagination
  fetchCategories()
}

const onSearch = () => {
  pagination.value.page = 1
  fetchCategories()
}

onMounted(() => {
  fetchCategories()
})
</script>