import { Routes, Route } from 'react-router-dom'
import { useAuthStore } from './stores/authStore'
import { useEffect } from 'react'
import Layout from './components/Layout'
import Home from './pages/Home'
import MangaDetail from './pages/MangaDetail'
import Reader from './pages/Reader'
import Login from './pages/Login'
import AdminDashboard from './pages/admin/Dashboard'
import MangaList from './pages/admin/MangaList'
import UserList from './pages/admin/UserList'
import LibraryList from './pages/admin/LibraryList'

function App() {
  const token = useAuthStore((state) => state.token)
  const checkAuth = useAuthStore((state) => state.checkAuth)

  useEffect(() => {
    if (token) {
      checkAuth()
    }
  }, [token, checkAuth])

  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route element={<Layout />}>
        <Route path="/" element={<Home />} />
        <Route path="/manga/:id" element={<MangaDetail />} />
        <Route path="/read/:mangaId/:chapterId" element={<Reader />} />
        <Route path="/admin" element={<AdminDashboard />} />
        <Route path="/admin/manga" element={<MangaList />} />
        <Route path="/admin/users" element={<UserList />} />
        <Route path="/admin/libraries" element={<LibraryList />} />
      </Route>
    </Routes>
  )
}

export default App
