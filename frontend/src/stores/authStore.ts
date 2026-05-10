import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import axios from 'axios'

interface User {
  id: number
  name: string
  email: string
  is_root: boolean
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  isLoading: boolean
  login: (email: string, password: string) => Promise<void>
  loginWithOAuth: (provider: string, token: string) => Promise<void>
  logout: () => Promise<void>
  checkAuth: () => Promise<void>
  refresh: () => Promise<void>
}

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,

      login: async (email: string, password: string) => {
        set({ isLoading: true })
        try {
          const response = await axios.post(`${API_URL}/v1/auth/login`, {
            email,
            password,
          })
          const { user, token } = response.data
          
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          
          set({ 
            user, 
            token, 
            isAuthenticated: true,
            isLoading: false 
          })
        } catch (error: any) {
          set({ isLoading: false })
          throw error.response?.data?.error || 'Login failed'
        }
      },

      loginWithOAuth: async (provider: string, oauthToken: string) => {
        set({ isLoading: true })
        try {
          const response = await axios.get(`${API_URL}/v1/auth/oauth/${provider}/callback`)
          const { user, token } = response.data
          
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          
          set({ 
            user, 
            token, 
            isAuthenticated: true,
            isLoading: false 
          })
        } catch (error: any) {
          set({ isLoading: false })
          throw error.response?.data?.error || 'OAuth login failed'
        }
      },

      logout: async () => {
        try {
          await axios.post(`${API_URL}/v1/auth/logout`)
        } catch (error) {
          console.error('Logout error:', error)
        } finally {
          delete axios.defaults.headers.common['Authorization']
          set({ 
            user: null, 
            token: null, 
            isAuthenticated: false 
          })
        }
      },

      checkAuth: async () => {
        const token = get().token
        if (!token) {
          set({ isAuthenticated: false, user: null })
          return
        }

        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

        try {
          const response = await axios.get(`${API_URL}/v1/auth/me`)
          set({ 
            user: response.data.user, 
            isAuthenticated: true,
            isLoading: false 
          })
        } catch (error) {
          delete axios.defaults.headers.common['Authorization']
          set({ isAuthenticated: false, user: null, token: null })
        }
      },

      refresh: async () => {
        try {
          const response = await axios.post(`${API_URL}/v1/auth/refresh`)
          const { token } = response.data
          
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
          
          set({ token })
        } catch (error) {
          get().logout()
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ token: state.token }),
    }
  )
)
