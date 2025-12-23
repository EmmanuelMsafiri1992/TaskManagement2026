import Axios from 'axios'

const axios = Axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
})

axios.interceptors.response.use(
  (response) => {
    // Check if response is HTML (indicating a redirect to login page)
    const contentType = response.headers['content-type']
    if (contentType && contentType.includes('text/html')) {
      console.warn('[Axios] Received HTML instead of JSON, redirecting to login')
      window.location.href = '/login'
      return Promise.reject(new Error('Session expired'))
    }
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      window.location.href = '/login'
    }
    // Handle cases where we get HTML instead of JSON (redirects)
    const contentType = error.response?.headers['content-type']
    if (contentType && contentType.includes('text/html')) {
      console.warn('[Axios] Received HTML error response, redirecting to login')
      window.location.href = '/login'
      return Promise.reject(new Error('Session expired'))
    }
    return Promise.reject(error)
  }
)

export { axios }
