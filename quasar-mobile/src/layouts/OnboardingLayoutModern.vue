<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <div class="row full-height">
        <!-- Left Side - Branding & Progress -->
        <div class="col-12 col-md-5 left-panel">
          <div class="animated-background"></div>
          <div class="left-panel-content">
            <div class="branding">
              <q-img
                src="~assets/quasar-logo-vertical.svg"
                style="height: 40%; max-width: 160px"
                class="q-mb-lg logo-animation"
              />
              <div class="text-h4 text-weight-bold text-white q-mb-sm fade-in">
                Welcome to POS System
              </div>
              <div class="text-subtitle1 text-grey-3 fade-in-delay">
                Let's get your business set up in minutes
              </div>
            </div>

            <div class="progress-section q-mt-xl">
              <div class="text-h6 text-white q-mb-lg fade-in">Setup Progress</div>
              <div class="progress-steps">
                <div
                  v-for="(step, index) in steps"
                  :key="index"
                  class="progress-step"
                  :class="{
                    'step-completed': currentStep > index + 1,
                    'step-active': currentStep === index + 1,
                    'fade-in': true
                  }"
                  :style="{ animationDelay: `${index * 0.1}s` }"
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
                    class="check-animation"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Content -->
        <div class="col-12 col-md-7 right-panel">
          <div class="right-panel-content">
            <div class="step-container">
              <slot></slot>
            </div>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'OnboardingLayoutModern',
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
  position: relative;
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  @media (max-width: 1023px) {
    display: none;
  }

  .animated-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, #FF6B6B, #4ECDC4, #45B7D1, #96C93D);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
    z-index: 0;
  }

  .left-panel-content {
    position: relative;
    z-index: 1;
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
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);

        &.step-completed {
          background: rgba(255, 255, 255, 0.2);
          transform: translateX(10px);
        }

        &.step-active {
          background: rgba(255, 255, 255, 0.3);
          transform: scale(1.02);
          box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .step-number {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.2);
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          margin-right: 16px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          transition: all 0.3s ease;
        }

        .step-info {
          flex: 1;

          .step-title {
            color: white;
            font-weight: 500;
            margin-bottom: 4px;
          }

          .step-description {
            color: rgba(255, 255, 255, 0.8);
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
  background: #f8f9fa;

  .right-panel-content {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
  }

  .step-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    padding: 32px;
    min-height: 400px;
    display: flex;
    flex-direction: column;
  }
}

// Animations
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.2s forwards;
  opacity: 0;
}

.logo-animation {
  animation: slideDown 0.5s ease-out forwards;
}

.check-animation {
  animation: scaleIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

// Mobile Responsiveness
@media (max-width: 599px) {
  .right-panel {
    padding: 20px;

    .step-container {
      padding: 20px;
      min-height: 300px;
    }
  }
}
</style> 