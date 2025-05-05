import { createApp } from 'vue'
import { Quasar, Notify, Loading, Dialog } from 'quasar'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Import Quasar css
import '@quasar/extras/roboto-font/roboto-font.css'
import '@quasar/extras/material-icons/material-icons.css'
import 'quasar/dist/quasar.css'

// Import app css
import './css/app.scss'

const app = createApp(App)

app.use(Quasar, {
  plugins: {
    Notify,
    Loading,
    Dialog
  },
  config: {
    brand: {
      primary: '#1a237e',
      secondary: '#0d47a1',
      accent: '#82B1FF',
      dark: '#1d1d1d',
      positive: '#21BA45',
      negative: '#C10015',
      info: '#31CCEC',
      warning: '#F2C037'
    }
  }
})

app.use(createPinia())
app.use(router)

app.mount('#app') 