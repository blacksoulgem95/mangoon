import { Link } from 'react-router-dom'
import { useMangaStore } from '../stores/mangaStore'
import { useEffect, useState } from 'react'
import { BookOpen } from 'lucide-react'

export default function Home() {
  const [mangaList, setMangaList] = useState<any[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchManga = async () => {
      try {
        const response = await fetch('http://localhost:8000/api/v1/manga')
        const data = await response.json()
        setMangaList(data.data || [])
      } catch (error) {
        console.error('Failed to fetch manga:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchManga()
  }, [])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>
    )
  }

  return (
    <div>
      <div className="text-center mb-12">
        <BookOpen className="h-16 w-16 text-emerald-600 mx-auto mb-4" />
        <h1 className="text-4xl font-bold text-emerald-600 mb-2">Welcome to Mangoon</h1>
        <p className="text-neutral-400">Your manga and comic management platform</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {mangaList.length > 0 ? (
          mangaList.map((manga) => (
            <Link
              key={manga.id}
              to={`/manga/${manga.id}`}
              className="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden hover:border-emerald-600 transition-colors"
            >
              <div className="aspect-[2/3] bg-neutral-800 flex items-center justify-center">
                {manga.cover_image ? (
                  <img src={manga.cover_image} alt={manga.title} className="w-full h-full object-cover" />
                ) : (
                  <BookOpen className="h-12 w-12 text-neutral-600" />
                )}
              </div>
              <div className="p-4">
                <h3 className="font-semibold text-neutral-100 truncate">{manga.title}</h3>
                {manga.is_mature && (
                  <span className="text-xs bg-red-900 text-red-300 px-2 py-1 rounded mt-2 inline-block">
                    Mature
                  </span>
                )}
              </div>
            </Link>
          ))
        ) : (
          <div className="col-span-full text-center py-12 text-neutral-500">
            No manga found. Start by adding some manga in the admin panel.
          </div>
        )}
      </div>
    </div>
  )
}
