<template>
  <q-page class="q-pa-md">
    <div class="text-h5 q-mb-md">Shift Management</div>

    <!-- Filters -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-4">
        <q-input
          v-model="filters.search"
          dense
          outlined
          label="Search"
          clearable
          @update:model-value="loadShifts"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
      <div class="col-12 col-md-4">
        <q-select
          v-model="filters.status"
          :options="statusOptions"
          dense
          outlined
          label="Status"
          clearable
          @update:model-value="loadShifts"
        />
      </div>
      <div class="col-12 col-md-4">
        <q-input
          v-model="filters.dateRange"
          dense
          outlined
          label="Date Range"
          clearable
          @update:model-value="loadShifts"
        >
          <template v-slot:append>
            <q-icon name="event" class="cursor-pointer">
              <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                <q-date
                  v-model="filters.dateRange"
                  range
                  mask="YYYY-MM-DD"
                  @update:model-value="loadShifts"
                />
              </q-popup-proxy>
            </q-icon>
          </template>
        </q-input>
      </div>
    </div>

    <!-- Shifts Table -->
    <q-table
      :rows="shifts"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :pagination.sync="pagination"
      @request="onRequest"
      binary-state-sort
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.status === 'open' ? 'positive' : 'grey'"
            text-color="white"
            dense
          >
            {{ props.row.status }}
          </q-chip>
        </q-td>
      </template>

      <!-- Amounts Column -->
      <template v-slot:body-cell-amounts="props">
        <q-td :props="props">
          <div>Opening: ${{ props.row.opening_amount }}</div>
          <div v-if="props.row.status === 'closed'">
            <div>Closing: ${{ props.row.closing_amount }}</div>
            <div>Expected: ${{ props.row.expected_amount }}</div>
            <div>Difference: ${{ props.row.difference }}</div>
          </div>
        </q-td>
      </template>

      <!-- Actions Column -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn
            flat
            round
            color="primary"
            icon="visibility"
            @click="viewShiftDetails(props.row)"
          >
            <q-tooltip>View Details</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Shift Details Dialog -->
    <q-dialog v-model="showShiftDetails" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Shift Details</div>
        </q-card-section>

        <q-card-section v-if="selectedShift">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <div class="text-subtitle2">Cashier</div>
              <div>{{ selectedShift.user?.name }}</div>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Status</div>
              <q-chip
                :color="selectedShift.status === 'open' ? 'positive' : 'grey'"
                text-color="white"
              >
                {{ selectedShift.status }}
              </q-chip>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Opening Amount</div>
              <div>${{ selectedShift.opening_amount }}</div>
            </div>
            <div class="col-12" v-if="selectedShift.status === 'closed'">
              <div class="text-subtitle2">Closing Amount</div>
              <div>${{ selectedShift.closing_amount }}</div>
            </div>
            <div class="col-12" v-if="selectedShift.status === 'closed'">
              <div class="text-subtitle2">Expected Amount</div>
              <div>${{ selectedShift.expected_amount }}</div>
            </div>
            <div class="col-12" v-if="selectedShift.status === 'closed'">
              <div class="text-subtitle2">Difference</div>
              <div>${{ selectedShift.difference }}</div>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Notes</div>
              <div>{{ selectedShift.notes || 'No notes' }}</div>
            </div>
            <div class="col-12">
              <div class="text-subtitle2">Opened At</div>
              <div>{{ formatDate(selectedShift.opened_at) }}</div>
            </div>
            <div class="col-12" v-if="selectedShift.status === 'closed'">
              <div class="text-subtitle2">Closed At</div>
              <div>{{ formatDate(selectedShift.closed_at) }}</div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'
import { date } from 'quasar'

export default {
  name: 'ShiftManagement',

  setup() {
    const $q = useQuasar()
    const route = useRoute()
    const loading = ref(false)
    const shifts = ref([])
    const showShiftDetails = ref(false)
    const selectedShift = ref(null)

    const filters = ref({
      search: '',
      status: null,
      dateRange: null
    })

    const statusOptions = [
      { label: 'Open', value: 'open' },
      { label: 'Closed', value: 'closed' }
    ]

    const columns = [
      {
        name: 'id',
        label: 'ID',
        field: 'id',
        sortable: true
      },
      {
        name: 'user',
        label: 'Cashier',
        field: row => row.user?.name,
        sortable: true
      },
      {
        name: 'status',
        label: 'Status',
        field: 'status',
        sortable: true
      },
      {
        name: 'amounts',
        label: 'Amounts',
        field: 'amounts'
      },
      {
        name: 'opened_at',
        label: 'Opened At',
        field: 'opened_at',
        sortable: true,
        format: val => formatDate(val)
      },
      {
        name: 'closed_at',
        label: 'Closed At',
        field: 'closed_at',
        sortable: true,
        format: val => val ? formatDate(val) : '-'
      },
      {
        name: 'actions',
        label: 'Actions',
        field: 'actions',
        align: 'center'
      }
    ]

    const pagination = ref({
      sortBy: 'opened_at',
      descending: true,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const loadShifts = async () => {
      try {
        loading.value = true
        const { businessId, branchId } = route.params
        
        const params = {
          page: pagination.value.page,
          per_page: pagination.value.rowsPerPage,
          sort_by: pagination.value.sortBy,
          sort_desc: pagination.value.descending,
          search: filters.value.search,
          status: filters.value.status,
          date_from: filters.value.dateRange?.from,
          date_to: filters.value.dateRange?.to
        }

        const response = await api.get(`/branch/${businessId}/1/shifts/history`, { params })
        shifts.value = response.data.data
        pagination.value.rowsNumber = response.data.total
      } catch (error) {
        console.error('Failed to load shifts:', error)
        $q.notify({
          type: 'negative',
          message: 'Failed to load shifts'
        })
      } finally {
        loading.value = false
      }
    }

    const onRequest = (props) => {
      pagination.value = props.pagination
      loadShifts()
    }

    const viewShiftDetails = (shift) => {
      selectedShift.value = shift
      showShiftDetails.value = true
    }

    const formatDate = (dateString) => {
      return date.formatDate(dateString, 'YYYY-MM-DD HH:mm:ss')
    }

    onMounted(() => {
      loadShifts()
    })

    return {
      loading,
      shifts,
      columns,
      pagination,
      filters,
      statusOptions,
      showShiftDetails,
      selectedShift,
      onRequest,
      viewShiftDetails,
      formatDate
    }
  }
}
</script> 