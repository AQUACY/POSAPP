<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <div class="row full-height">
        <!-- Left Side - Branding & Progress -->
        <div class="col-12 col-md-5 left-panel">
          <div class="left-panel-content">
            <div class="branding">
              <q-img
                src="~assets/quasar-logo-vertical.svg"
                style="height: 80px; max-width: 160px"
                class="q-mb-lg"
              />
              <div class="text-h4 text-weight-bold text-white q-mb-sm">
                Welcome to POS System
              </div>
              <div class="text-subtitle1 text-grey-3">
                Let's get your business set up in minutes
              </div>
            </div>

            <div class="progress-section q-mt-xl">
              <div class="text-h6 text-white q-mb-lg">Setup Progress</div>
              <div class="progress-steps">
                <div
                  v-for="(step, index) in steps"
                  :key="index"
                  class="progress-step"
                  :class="{
                    'step-completed': currentStep > index + 1,
                    'step-active': currentStep === index + 1
                  }"
                >
                  <div class="step-number">{{ index + 1 }}</div>
                  <div class="step-info">
                    <div class="step-title">{{ step.title }}</div>
                    <div class="step-description">{{ step.description }}</div>
                  </div>
                  <q-icon
                    v-if="currentStep > index + 1"
                    name="check_circle"
                    color="positive"
                    size="24px"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Content -->
        <div class="col-12 col-md-7 right-panel">
          <div class="right-panel-content">
            <slot></slot>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'OnboardingLayoutDark',
  props: {
    currentStep: {
      type: Number,
      required: true
    }
  },
  setup() {
    const steps = [
      {
        title: 'Create Account',
        description: 'Register your account details'
      },
      {
        title: 'Verify Account',
        description: 'Confirm your phone number'
      },
      {
        title: 'Business Setup',
        description: 'Configure your business details'
      },
      {
        title: 'Branch Setup',
        description: 'Set up your first branch'
      },
      {
        title: 'Staff Setup',
        description: 'Create staff accounts'
      }
    ]

    return {
      steps
    }
  }
})
</script>

<style lang="scss" scoped>
.full-height {
  min-height: 100vh;
}

.left-panel {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;

  @media (max-width: 1023px) {
    display: none;
  }

  .left-panel-content {
    max-width: 400px;
    width: 100%;
  }

  .branding {
    text-align: center;
  }

  .progress-section {
    .progress-steps {
      .progress-step {
        display: flex;
        align-items: center;
        padding: 16px;
        margin-bottom: 16px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);

        &.step-completed {
          background: rgba(255, 255, 255, 0.1);
          border-color: rgba(255, 255, 255, 0.2);
        }

        &.step-active {
          background: rgba(255, 255, 255, 0.15);
          border-color: rgba(255, 255, 255, 0.3);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .step-number {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.1);
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          margin-right: 16px;
          border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .step-info {
          flex: 1;

          .step-title {
            color: white;
            font-weight: 500;
            margin-bottom: 4px;
          }

          .step-description {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9em;
          }
        }
      }
    }
  }
}

.right-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: #121212;

  .right-panel-content {
    max-width: 600px;
    width: 100%;
    color: white;

    :deep(.q-stepper) {
      background: transparent;
      color: white;
    }

    :deep(.q-stepper__title) {
      color: white;
    }

    :deep(.q-stepper__subtitle) {
      color: rgba(255, 255, 255, 0.7);
    }

    :deep(.q-field__label) {
      color: rgba(255, 255, 255, 0.7);
    }

    :deep(.q-field__control) {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.1);
    }

    :deep(.q-field__native) {
      color: white;
    }
  }
}

// Mobile Responsiveness
@media (max-width: 599px) {
  .right-panel {
    padding: 20px;
  }
}
</style> 