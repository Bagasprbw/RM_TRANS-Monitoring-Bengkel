import { monitoringApi } from '../api/monitoringApi'
import Swal from 'sweetalert2'

// State
const state = {
  monitoringList: [],
  availableArmadaList: [],
  currentMonitoring: null,
  loading: false
}

// Getters
const getters = {
  monitoringList: (state) => state.monitoringList,
  availableArmadaList: (state) => state.availableArmadaList,
  currentMonitoring: (state) => state.currentMonitoring,
  loading: (state) => state.loading
}

// Mutations
const mutations = {
  SET_MONITORING_LIST(state, data) {
    state.monitoringList = data
  },
  SET_AVAILABLE_ARMADA_LIST(state, data) {
    state.availableArmadaList = data
  },
  SET_CURRENT_MONITORING(state, data) {
    state.currentMonitoring = data
  },
  ADD_MONITORING(state, data) {
    state.monitoringList.unshift(data)
  },
  REMOVE_MONITORING(state, id) {
    state.monitoringList = state.monitoringList.filter(item => item.id !== id)
  },
  REMOVE_AVAILABLE_ARMADA(state, armadaId) {
    state.availableArmadaList = state.availableArmadaList.filter(item => item.id !== armadaId)
  },
  SET_LOADING(state, status) {
    state.loading = status
  }
}

// Actions
const actions = {
  // Fetch all monitoring armada
  async fetchMonitoring({ commit }) {
    commit('SET_LOADING', true)
    try {
      const response = await monitoringApi.getAll()
      commit('SET_MONITORING_LIST', response.data.data || response.data)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching monitoring armada:', error)
      Swal.fire({
        icon: 'error',
        title: 'Gagal Memuat Data',
        text: error.response?.data?.message || 'Terjadi kesalahan saat memuat data monitoring kendaraan',
        confirmButtonColor: '#3E3D90'
      })
      return { success: false, error: error.response?.data?.message || error.message }
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Fetch detail monitoring armada
  async fetchMonitoringDetail({ commit }, id) {
    commit('SET_LOADING', true)
    try {
      const response = await monitoringApi.getById(id)
      commit('SET_CURRENT_MONITORING', response.data.data || response.data)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching monitoring detail:', error)
      return { success: false, error: error.message }
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Fetch available armada (not monitored yet)
  async fetchAvailableArmada({ commit }) {
    try {
      const response = await monitoringApi.getAvailable()
      commit('SET_AVAILABLE_ARMADA_LIST', response.data.data || response.data)
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Error fetching available armada:', error)
      return { success: false, error: error.message }
    }
  },

  // Create new monitoring
  async createMonitoring({ commit }, payload) {
    commit('SET_LOADING', true)
    try {
      const response = await monitoringApi.create(payload)
      
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Kendaraan berhasil ditambahkan ke monitoring',
        confirmButtonColor: '#3E3D90',
        timer: 2000,
        showConfirmButton: false
      })
      
      commit('ADD_MONITORING', response.data.data)
      // also remove from available list
      commit('REMOVE_AVAILABLE_ARMADA', payload.armada_id)

      return { success: true, data: response.data.data }
    } catch (error) {
      console.error('Error creating monitoring:', error)
      
      let errorMessage = 'Gagal menambahkan kendaraan ke monitoring'
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors
        errorMessage = Object.values(errors).flat().join('\n')
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      }
      
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: errorMessage,
        confirmButtonColor: '#3E3D90'
      })
      return { success: false, error: errorMessage }
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Delete monitoring
  async deleteMonitoring({ commit }, id) {
    const result = await Swal.fire({
      title: 'Konfirmasi Hapus',
      text: 'Apakah Anda yakin ingin menghapus kendaraan ini dari monitoring?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    })
    
    if (!result.isConfirmed) return { success: false }
    
    commit('SET_LOADING', true)
    try {
      await monitoringApi.delete(id)
      
      Swal.fire({
        icon: 'success',
        title: 'Terhapus!',
        text: 'Data monitoring berhasil dihapus',
        confirmButtonColor: '#3E3D90',
        timer: 2000,
        showConfirmButton: false
      })
      
      commit('REMOVE_MONITORING', id)
      // Fetch available armadas again or maybe just force refreshing to be sure.
      
      return { success: true }
    } catch (error) {
      console.error('Error deleting monitoring:', error)
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: error.response?.data?.message || 'Gagal menghapus monitoring',
        confirmButtonColor: '#3E3D90'
      })
      return { success: false, error: error.response?.data?.message || error.message }
    } finally {
      commit('SET_LOADING', false)
    }
  },
  // Update monitoring status
  async updateStatus({ commit }, { id, data }) {
    try {
      const response = await monitoringApi.update(id, data)
      commit('UPDATE_MONITORING', response.data.data || response.data)
      return { success: true }
    } catch (error) {
      console.error('Error updating monitoring status:', error)
      return { success: false, error: error.message }
    }
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations: {
    ...mutations,
    UPDATE_MONITORING(state, updatedData) {
      const index = state.monitoringList.findIndex(item => item.id === updatedData.id)
      if (index !== -1) {
        state.monitoringList.splice(index, 1, updatedData)
      }
    }
  },
  actions
}
