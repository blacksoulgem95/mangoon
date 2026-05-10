import { Link } from 'react-router-dom'
import { BookOpen, Users, Library, Settings } from 'lucide-react'

export default function AdminDashboard() {
  const stats = [
    { label: 'Total Manga', value: '0', icon: BookOpen, color: 'text-emerald-600' },
    { label: 'Total Chapters', value: '0', icon: Library, color: 'text-blue-600' },
    { label: 'Total Users', value: '0', icon: Users, color: 'text-purple-600' },
  ]

  return (
    <div>
      <h1 className="text-3xl font-bold text-emerald-600 mb-8">Admin Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {stats.map((stat) => (
          <div key={stat.label} className="bg-neutral-900 border border-neutral-800 rounded-lg p-6">
            <stat.icon className={`h-8 w-8 ${stat.color} mb-2`} />
            <p className="text-3xl font-bold text-neutral-100">{stat.value}</p>
            <p className="text-neutral-400">{stat.label}</p>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Link
          to="/admin/manga"
          className="bg-neutral-900 border border-neutral-800 rounded-lg p-6 hover:border-emerald-600 transition-colors"
        >
          <BookOpen className="h-8 w-8 text-emerald-600 mb-4" />
          <h3 className="text-xl font-semibold text-neutral-100 mb-2">Manga Management</h3>
          <p className="text-neutral-400">Create, edit, and manage manga entries</p>
        </Link>

        <Link
          to="/admin/libraries"
          className="bg-neutral-900 border border-neutral-800 rounded-lg p-6 hover:border-emerald-600 transition-colors"
        >
          <Library className="h-8 w-8 text-blue-600 mb-4" />
          <h3 className="text-xl font-semibold text-neutral-100 mb-2">Libraries</h3>
          <p className="text-neutral-400">Manage collections and user permissions</p>
        </Link>

        <Link
          to="/admin/users"
          className="bg-neutral-900 border border-neutral-800 rounded-lg p-6 hover:border-emerald-600 transition-colors"
        >
          <Users className="h-8 w-8 text-purple-600 mb-4" />
          <h3 className="text-xl font-semibold text-neutral-100 mb-2">User Management</h3>
          <p className="text-neutral-400">Manage users and their roles</p>
        </Link>

        <Link
          to="/admin/settings"
          className="bg-neutral-900 border border-neutral-800 rounded-lg p-6 hover:border-emerald-600 transition-colors"
        >
          <Settings className="h-8 w-8 text-orange-600 mb-4" />
          <h3 className="text-xl font-semibold text-neutral-100 mb-2">Settings</h3>
          <p className="text-neutral-400">Configure OAuth and system settings</p>
        </Link>
      </div>
    </div>
  )
}
