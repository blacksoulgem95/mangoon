import { create } from 'zustand'
import axios from 'axios'

interface Manga {
  id: number
  title: string
  synopsis: string
  cover_image: string | null
  is_mature: boolean
  rating: number
  views: number
  created_at: string
}

interface Chapter {
  id: number
  manga_id: number
  chapter_number: string
  title: string
  page_count: number
  created_at: string
}

interface MangaState {
  manga: Manga | null
  chapters: Chapter[]
  isLoading: boolean
  fetchManga: (id: number) => Promise<void>
  fetchChapters: (mangaId: number) => Promise<void>
  fetchMangaList: (page?: number) => Promise<Manga[]>
}

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useMangaStore = create<MangaState>((set, get) => ({
  manga: null,
  chapters: [],
  isLoading: false,

  fetchManga: async (id: number) => {
    set({ isLoading: true })
    try {
      const response = await axios.get(`${API_URL}/v1/manga/${id}`)
      set({ manga: response.data, isLoading: false })
    } catch (error) {
      set({ isLoading: false })
      throw error
    }
  },

  fetchChapters: async (mangaId: number) => {
    set({ isLoading: true })
    try {
      const response = await axios.get(`${API_URL}/v1/manga/${mangaId}`)
      set({ chapters: response.data.chapters || [], isLoading: false })
    } catch (error) {
      set({ isLoading: false })
      throw error
    }
  },

  fetchMangaList: async (page = 1) => {
    try {
      const response = await axios.get(`${API_URL}/v1/manga`, {
        params: { page }
      })
      return response.data
    } catch (error) {
      throw error
    }
  },
}))
