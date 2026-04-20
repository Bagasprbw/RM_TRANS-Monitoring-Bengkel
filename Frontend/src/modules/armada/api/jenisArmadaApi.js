import axios from '@/core/axios'

export const jenisArmadaApi = {
  // GET all jenis_armada
  getAll() {
    return axios.get('/jenis_armada')
  },

  // GET detail jenis_armada by id
  getById(id) {
    return axios.get(`/jenis_armada/${id}`)
  },

  // POST create jenis_armada
  create(data) {
    return axios.post('/jenis_armada', data)
  },

  // DELETE jenis_armada
  delete(id) {
    return axios.delete(`/jenis_armada/${id}`)
  }
}
