<template>
  <div>
    <!-- Open Shift Dialog -->
    <q-dialog v-model="showOpenShiftDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Open Shift</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onOpenShift" class="q-gutter-md">
            <q-input
              v-model.number="openShiftForm.opening_amount"
              label="Opening Amount"
              type="number"
              :rules="[val => val > 0 || 'Opening amount must be greater than 0']"
              outlined
            >
              <template v-slot:prepend>$</template>
            </q-input>

            <q-input
              v-model="openShiftForm.notes"
              label="Notes"
              type="textarea"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Open Shift" color="primary" @click="onOpenShift" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Close Shift Dialog -->
    <q-dialog v-model="showCloseShiftDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Close Shift</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onCloseShift" class="q-gutter-md">
            <q-input
              v-model.number="closeShiftForm.closing_amount"
              label="Closing Amount"
              type="number"
              :rules="[val => val > 0 || 'Closing amount must be greater than 0']"
              outlined
            >
              <template v-slot:prepend>$</template>
            </q-input>

            <q-input
              v-model="closeShiftForm.notes"
              label="Notes"
              type="textarea"
              outlined
            />

            <div v-if="currentShift" class="q-mt-md">
              <div class="text-subtitle2">Shift Summary</div>
              <div>Opening Amount: ${{ currentShift.opening_amount }}</div>
              <div>Expected Amount: ${{ currentShift.expected_amount }}</div>
              <div>Difference: ${{ currentShift.difference }}</div>
            </div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Close Shift" color="primary" @click="onCloseShift" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Shift Status -->
    <div v-if="currentShift" class="q-pa-md">
      <q-banner class="bg-positive text-white">
        <template v-slot:avatar>
          <q-icon name="check_circle" />
        </template>
        Shift is open
        <template v-slot:action>
          <q-btn flat color="white" label="Close Shift" @click="showCloseShiftDialog = true" />
        </template>
      </q-banner>
    </div>
    <div v-else class="q-pa-md">
      <q-banner class="bg-negative text-white">
        <template v-slot:avatar>
          <q-icon name="warning" />
        </template>
        No open shift
        <template v-slot:action>
          <q-btn flat color="white" label="Open Shift" @click="showOpenShiftDialog = true" />
        </template>
      </q-banner>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'src/boot/axios'

export default {
  name: 'ShiftManager',
  emits: ['shift-status-changed'],

  setup(props, { emit }) {
    const $q = useQuasar()
    const route = useRoute()
    const currentShift = ref(null)
    const showOpenShiftDialog = ref(false)
    const showCloseShiftDialog = ref(false)

    const openShiftForm = ref({
      opening_amount: 0,
      notes: ''
    })

    const closeShiftForm = ref({
      closing_amount: 0,
      notes: ''
    })

    const fetchCurrentShift = async () => {
      try {
        const { businessId, branchId } = route.params
        const response = await api.get(`/branch/${businessId}/${branchId}/shifts/current`)
        currentShift.value = response.data.data
        emit('shift-status-changed', !!currentShift.value)
      } catch (error) {
        console.error('Failed to fetch current shift:', error)
      }
    }

    const onOpenShift = async () => {
      try {
        const { businessId, branchId } = route.params
        await api.post(`/branch/${businessId}/${branchId}/shifts/open`, openShiftForm.value)
        showOpenShiftDialog.value = false
        await fetchCurrentShift()
        $q.notify({
          type: 'positive',
          message: 'Shift opened successfully'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to open shift'
        })
      }
    }

    const onCloseShift = async () => {
      try {
        const { businessId, branchId } = route.params
        await api.post(`/branch/${businessId}/${branchId}/shifts/close`, closeShiftForm.value)
        showCloseShiftDialog.value = false
        await fetchCurrentShift()
        $q.notify({
          type: 'positive',
          message: 'Shift closed successfully'
        })
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to close shift'
        })
      }
    }

    onMounted(() => {
      fetchCurrentShift()
    })

    return {
      currentShift,
      showOpenShiftDialog,
      showCloseShiftDialog,
      openShiftForm,
      closeShiftForm,
      onOpenShift,
      onCloseShift
    }
  }
}
</script> 