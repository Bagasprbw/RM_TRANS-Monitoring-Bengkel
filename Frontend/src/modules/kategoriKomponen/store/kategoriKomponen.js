import { kategoriKomponenApi } from '../api/kategoriKomponenApi'
import Swal from 'sweetalert2'

const state = {
  kategoriList: [],
  loading: false
}

const getters = {
  kategoriList: (state) => state.kategoriList,
  loading: (state) => state.loading
}

const mutations = {
  SET_KATEGORI_LIST(state, data) {
    state.kategoriList = data
  },
  ADD_KATEGORI(state, data) {
    state.kategoriList.push(data)
  },
  SET_LOADING(state, status) {
    state.loading = status
  }
}

const actions = {
  async fetchKategori({ commit }) {
    commit('SET_LOADING', true)
    try {
      const response = await kategoriKomponenApi.getAll()
      commit('SET_KATEGORI_LIST', response.data.data || response.data)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Error fetching kategori komponen:', error)
      return { success: false, error: error.message }
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async createKategori({ commit }, payload) {
    commit('SET_LOADING', true)
    try {
      const response = await kategoriKomponenApi.create(payload)
      
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Kategori komponen berhasil ditambahkan',
        confirmButtonColor: '#3E3D90',
        timer: 2000,
        showConfirmButton: false
      })
      
      commit('ADD_KATEGORI', response.data.data || response.data)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Error creating kategori:', error)
      
      let errorMessage = 'Gagal menambahkan kategori komponen'
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

  async deleteKategori({ commit, dispatch }, id) {
    commit('SET_LOADING', true)
    try {
      await kategoriKomponenApi.delete(id)
      Swal.fire('Terhapus', 'Kategori komponen berhasil dihapus', 'success')
      dispatch('fetchKategori')
      return { success: true }
    } catch (error) {
      let msg = error.response?.data?.message || 'Gagal menghapus kategori.';
      if (msg.includes('SQLSTATE') || msg.includes('Integrity constraint violation')) {
         msg = 'Gagal menghapus kategori. Kategori ini sedang terpakai dan masih berelasi dengan komponen kendaraan lain.';
      }
      Swal.fire('Tidak Dapat Dihapus', msg, 'error')
      return { success: false, error: msg }
    } finally {
      commit('SET_LOADING', false)
    }
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
