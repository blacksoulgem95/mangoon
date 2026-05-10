import { Outlet, Link, useNavigate } from 'react-router-dom'
import { useAuthStore } from '../stores/authStore'
import { BookOpen, User, Settings, LogOut, Menu } from 'lucide-react'
import { useState } from 'react'

export default function Layout() {
  const { user, isAuthenticated, logout } = useAuthStore()
  const navigate = useNavigate()
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <div className="min-h-screen bg-neutral-950 text-neutral-100">
      <nav className="border-b border-neutral-800 bg-neutral-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center">
              <Link to="/" className="flex items-center space-x-2">
                <BookOpen className="h-8 w-8 text-emerald-600" />
                <span className="text-xl font-bold text-emerald-600">MANGOON</span>
              </Link>
              
              <div className="hidden md:flex ml-10 space-x-4">
                <Link to="/" className="px-3 py-2 hover:text-emerald-500 transition-colors">
                  Home
                </Link>
                {isAuthenticated && (
                  <Link to="/admin" className="px-3 py-2 hover:text-emerald-500 transition-colors">
                    Admin
                  </Link>
                )}
              </div>
            </div>

            <div className="hidden md:flex items-center space-x-4">
              {isAuthenticated ? (
                <>
                  <span className="text-sm text-neutral-400">
                    {user?.name}
                    {user?.is_root && <span className="ml-2 text-xs bg-emerald-900 text-emerald-300 px-2 py-1 rounded">ROOT</span>}
                  </span>
                  <button
                    onClick={handleLogout}
                    className="flex items-center space-x-1 px-3 py-2 hover:bg-neutral-800 rounded transition-colors"
                  >
                    <LogOut className="h-4 w-4" />
                    <span>Logout</span>
                  </button>
                </>
              ) : (
                <Link
                  to="/login"
                  className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded transition-colors"
                >
                  Login
                </Link>
              )}
            </div>

            <button
              className="md:hidden"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            >
              <Menu className="h-6 w-6" />
            </button>
          </div>
        </div>

        {mobileMenuOpen && (
          <div className="md:hidden border-t border-neutral-800 bg-neutral-900">
            <div className="px-2 pt-2 pb-3 space-y-1">
              <Link
                to="/"
                className="block px-3 py-2 hover:bg-neutral-800 rounded"
                onClick={() => setMobileMenuOpen(false)}
              >
                Home
              </Link>
              {isAuthenticated && (
                <Link
                  to="/admin"
                  className="block px-3 py-2 hover:bg-neutral-800 rounded"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Admin
                </Link>
              )}
              {isAuthenticated ? (
                <button
                  onClick={() => {
                    handleLogout()
                    setMobileMenuOpen(false)
                  }}
                  className="block w-full text-left px-3 py-2 hover:bg-neutral-800 rounded"
                >
                  Logout
                </button>
              ) : (
                <Link
                  to="/login"
                  className="block px-3 py-2 hover:bg-neutral-800 rounded"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Login
                </Link>
              )}
            </div>
          </div>
        )}
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Outlet />
      </main>

      <footer className="border-t border-neutral-800 bg-neutral-900 mt-auto">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <p className="text-center text-sm text-neutral-500">
            Mangoon - Manga Management Platform
          </p>
        </div>
      </footer>
    </div>
  )
}
