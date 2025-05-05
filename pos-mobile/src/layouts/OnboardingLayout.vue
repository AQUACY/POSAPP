<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <div class="onboarding-progress">
        <q-linear-progress
          :value="progress"
          size="4px"
          color="primary"
          class="q-mb-md"
        />
        <div class="row justify-between text-caption text-grey-4">
          <span>Step {{ currentStep }} of {{ totalSteps }}</span>
          <span>{{ stepTitle }}</span>
        </div>
      </div>

      <router-view v-slot="{ Component }">
        <transition
          appear
          enter-active-class="animated slideInRight"
          leave-active-class="animated slideOutLeft"
        >
          <component :is="Component" @update:step="updateStep" />
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref, computed } from 'vue'
import { useRoute } from 'vue-router'

export default defineComponent({
  name: 'OnboardingLayout',
  setup() {
    const route = useRoute()
    const currentStep = ref(1)
    const totalSteps = ref(4)

    const stepTitles = {
      register: 'Create Account',
      'verify-otp': 'Verify Email',
      'setup-business': 'Business Setup',
      'setup-branch': 'Branch Setup'
    }

    const stepTitle = computed(() => {
      const step = route.name
      return stepTitles[step] || ''
    })

    const progress = computed(() => {
      return currentStep.value / totalSteps.value
    })

    const updateStep = (step) => {
      currentStep.value = step
    }

    return {
      currentStep,
      totalSteps,
      stepTitle,
      progress,
      updateStep
    }
  }
})
</script>

<style lang="scss" scoped>
.onboarding-progress {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
  z-index: 1000;
}
</style> 