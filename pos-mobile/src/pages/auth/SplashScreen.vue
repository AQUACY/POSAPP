<template>
  <q-page class="splash-screen flex flex-center">
    <div class="splash-content text-center">
      <div class="logo-container">
        <q-img
          src="~assets/quasar-logo-vertical.svg"
          class="logo"
          :class="{ 'logo-animate': showLogo }"
          @load="onLogoLoad"
        />
      </div>
      <h1 class="app-title" :class="{ 'title-animate': showTitle }">
        POS System
      </h1>
      <p class="app-subtitle" :class="{ 'subtitle-animate': showSubtitle }">
        Modern Point of Sale Solution
      </p>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { useRouter } from 'vue-router'

export default defineComponent({
  name: 'SplashScreen',
  setup() {
    const router = useRouter()
    const showLogo = ref(false)
    const showTitle = ref(false)
    const showSubtitle = ref(false)

    const onLogoLoad = () => {
      showLogo.value = true
      setTimeout(() => {
        showTitle.value = true
        setTimeout(() => {
          showSubtitle.value = true
          setTimeout(() => {
            router.push('/auth/login')
          }, 1000)
        }, 500)
      }, 500)
    }

    return {
      showLogo,
      showTitle,
      showSubtitle,
      onLogoLoad
    }
  }
})
</script>

<style lang="scss" scoped>
.splash-screen {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
}

.splash-content {
  padding: 2rem;
}

.logo-container {
  width: 150px;
  height: 150px;
  margin: 0 auto;
  position: relative;
}

.logo {
  width: 100%;
  height: 100%;
  opacity: 0;
  transform: scale(0.5);
  transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.logo-animate {
  opacity: 1;
  transform: scale(1);
}

.app-title {
  color: white;
  font-size: 2.5rem;
  margin-top: 1rem;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.5s ease-out;
}

.title-animate {
  opacity: 1;
  transform: translateY(0);
}

.app-subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.2rem;
  margin-top: 0.5rem;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.5s ease-out;
}

.subtitle-animate {
  opacity: 1;
  transform: translateY(0);
}
</style> 