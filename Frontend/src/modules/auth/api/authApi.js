import axios from '@/core/axios'

export const loginApi = (data) => axios.post('/login', data)
export const logoutApi = () => axios.post('/logout')
export const updateProfileApi = (data) => axios.post('/update-profile', data)