import { boot } from 'quasar/wrappers'
import axios from 'axios'

// Helper to detect Cordova
function isCordova() {
  return typeof window !== 'undefined' && window.cordova;
}

// Dynamic API base URL
const API_BASE = isCordova()
  ? 'http://localhost:8000/api' // <-- Change to your production API or LAN IP for dev
  : process.env.API_BASE_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Secure token getter for Cordova or web
async function getToken() {
  if (isCordova() && window.SecureStorage) {
    return new Promise((resolve) => {
      const ss = new window.SecureStorage(
        () => {
          ss.get(
            (value) => resolve(value),
            () => resolve(null),
            'token'
          )
        },
        () => resolve(null),
        'POSAPP'
      )
    })
  } else {
    return localStorage.getItem('token')
  }
}

// Request interceptor for token
api.interceptors.request.use(async (config) => {
  const token = await getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, (error) => Promise.reject(error))

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
