import axios from 'axios'

const instance = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  }
})

// Request interceptor: attach token dari localStorage
instance.interceptors.request.use(config => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = 'Bearer ' + token
  }

  return config
})

// Response interceptor: handle 401 Unauthorized (token expired/invalid/dihapus dari server)
instance.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Bersihkan data autentikasi dari localStorage
      localStorage.removeItem('token')
      localStorage.removeItem('user')

      // Redirect ke halaman login jika belum di sana
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default instance