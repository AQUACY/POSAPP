<template>
  <q-layout view="hHh lpR fFf">
    <router-view v-slot="{ Component }">
      <component :is="Component" />
    </router-view>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'OnboardingLayout'
})
</script>

<style lang="scss" scoped>
.full-height {
  min-height: 100vh;
}

.left-panel {
  background: linear-gradient(135deg, #1976D2 0%, #2196F3 100%);
  padding: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    animation: shimmer 3s infinite;
  }

  @media (max-width: 1023px) {
    display: none;
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
        border-radius: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: left center;

        &.step-completed {
          background: rgba(255, 255, 255, 0.2);
          transform: translateX(10px);
        }

        &.step-active {
          background: rgba(255, 255, 255, 0.3);
          transform: scale(1.02);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .step-number {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.2);
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          margin-right: 16px;
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
            color: rgba(255, 255, 255, 0.7);
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
  background: #f5f5f5;

  .right-panel-content {
    max-width: 600px;
    width: 100%;
  }
}

// Animations
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
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
  }
}
</style> 