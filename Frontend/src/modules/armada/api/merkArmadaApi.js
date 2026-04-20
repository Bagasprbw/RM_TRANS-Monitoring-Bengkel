import axios from '@/core/axios'

export const merkArmadaApi = {
  // GET all merk_armada
  getAll() {
    return axios.get('/merk_armada')
  },

  // POST create merk_armada
  create(data) {
    return axios.post('/merk_armada', data)
  },

  // DELETE merk_armada
  delete(id) {
    return axios.delete(`/merk_armada/${id}`)
  }
}
