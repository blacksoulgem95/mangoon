import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuthStore } from '../stores/authStore'
import { BookOpen } from 'lucide-react'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const login = useAuthStore((state) => state.login)
  const navigate = useNavigate()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    try {
      await login(email, password)
      navigate('/admin')
    } catch (err: any) {
      setError(err.message || 'Login failed')
    } finally {
      setLoading(false)
    }
  }

  const handleOAuth = (provider: string) => {
    window.location.href = `http://localhost:8000/api/v1/auth/oauth/${provider}/redirect`
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-neutral-950 px-4">
      <div className="max-w-md w-full">
        <div className="text-center mb-8">
          <BookOpen className="h-16 w-16 text-emerald-600 mx-auto mb-4" />
          <h1 className="text-3xl font-bold text-emerald-600">Mangoon</h1>
          <p className="text-neutral-400 mt-2">Sign in to your account</p>
        </div>

        <div className="bg-neutral-900 border border-neutral-800 rounded-lg p-6">
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <div className="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded">
                {error}
              </div>
            )}

            <div>
              <label htmlFor="email" className="block text-sm font-medium text-neutral-300 mb-1">
                Email
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-4 py-2 bg-neutral-800 border border-neutral-700 rounded text-neutral-100 focus:outline-none focus:border-emerald-600"
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-neutral-300 mb-1">
                Password
              </label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full px-4 py-2 bg-neutral-800 border border-neutral-700 rounded text-neutral-100 focus:outline-none focus:border-emerald-600"
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded transition-colors disabled:opacity-50"
            >
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <div className="mt-6">
            <div className="relative">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-neutral-700"></div>
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="px-2 bg-neutral-900 text-neutral-400">Or continue with</span>
              </div>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-3">
              <button
                onClick={() => handleOAuth('github')}
                className="w-full bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-neutral-300 font-medium py-2 rounded transition-colors"
              >
                Sign in with GitHub
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
