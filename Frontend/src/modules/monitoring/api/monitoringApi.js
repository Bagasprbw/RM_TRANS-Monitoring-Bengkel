import axios from '@/core/axios'

export const monitoringApi = {
  // GET all monitoring armada
  getAll() {
    return axios.get('/monitoring_armada_aktif')
  },

  // GET detail armada by id
  getById(id) {
    return axios.get(`/monitoring_armada_aktif/${id}`)
  },

  // GET available armada (not currently monitored)
  getAvailable() {
    return axios.get('/monitoring_armada_aktif/available')
  },

  // POST create new monitoring armada
  create(data) {
    return axios.post('/monitoring_armada_aktif', data)
  },

  // PUT update status monitoring armada
  update(id, data) {
    return axios.put(`/monitoring_armada_aktif/${id}`, data)
  },

  // DELETE monitoring armada
  delete(id) {
    return axios.delete(`/monitoring_armada_aktif/${id}`)
  }
}
