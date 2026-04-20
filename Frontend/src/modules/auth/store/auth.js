import axios from '@/core/axios'

const state = {
  token: localStorage.getItem('token') || '',
  user: JSON.parse(localStorage.getItem('user')) || null,
  error: null,
  loading: false
}

const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    localStorage.setItem('token', token)
  },
  SET_USER(state, user) {
    state.user = user
    localStorage.setItem('user', JSON.stringify(user))
  },
  SET_ERROR(state, error) {
    state.error = error
  },
  LOGOUT(state) {
    state.token = ''
    state.user = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },
  SET_LOADING(state, status) {
    state.loading = status
  }
}

const actions = {
  async login({ commit }, payload) {
    try {
      commit('SET_ERROR', null)
      commit('SET_LOADING', true)

      const res = await axios.post('/login', payload)

      const token = res.data.token

      commit('SET_TOKEN', token)
      commit('SET_USER', res.data.user)
      return true

    } catch (err) {
      commit('SET_ERROR', 'Login gagal')
      throw err
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async logout({ commit }) {
    try {
      await axios.post('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      commit('LOGOUT')
    }
  },

  async updateProfile({ commit }, payload) {
    try {
      commit('SET_LOADING', true)
      const res = await axios.post('/update-profile', payload)
      commit('SET_USER', res.data.user)
      return res.data
    } catch (err) {
      throw err
    } finally {
      commit('SET_LOADING', false)
    }
  }
}

const getters = {
  isAuthenticated: state => !!state.token
}

export default {
  namespaced: true, // 🔥 WAJIB
  state,
  mutations,
  actions,
  getters
}