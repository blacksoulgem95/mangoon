import { useParams, Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { BookOpen, ChevronRight } from 'lucide-react'

export default function MangaDetail() {
  const { id } = useParams()
  const [manga, setManga] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchManga = async () => {
      try {
        const response = await fetch(`http://localhost:8000/api/v1/manga/${id}`)
        const data = await response.json()
        setManga(data)
      } catch (error) {
        console.error('Failed to fetch manga:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchManga()
  }, [id])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>
    )
  }

  if (!manga) {
    return (
      <div className="text-center py-12">
        <p className="text-neutral-400">Manga not found</p>
      </div>
    )
  }

  return (
    <div>
      <div className="flex items-center space-x-2 text-sm text-neutral-400 mb-6">
        <Link to="/" className="hover:text-emerald-600">Home</Link>
        <ChevronRight className="h-4 w-4" />
        <span className="text-neutral-200">{manga.title}</span>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div className="md:col-span-1">
          <div className="bg-neutral-800 aspect-[2/3] rounded-lg overflow-hidden">
            {manga.cover_image ? (
              <img src={manga.cover_image} alt={manga.title} className="w-full h-full object-cover" />
            ) : (
              <div className="flex items-center justify-center h-full">
                <BookOpen className="h-16 w-16 text-neutral-600" />
              </div>
            )}
          </div>
        </div>

        <div className="md:col-span-2">
          <h1 className="text-4xl font-bold text-neutral-100 mb-4">{manga.title}</h1>
          
          {manga.is_mature && (
            <span className="text-sm bg-red-900 text-red-300 px-3 py-1 rounded mb-4 inline-block">
              Mature Content
            </span>
          )}

          <div className="flex items-center space-x-6 mb-6 text-sm text-neutral-400">
            <span>Rating: {manga.rating || 'N/A'}</span>
            <span>Views: {manga.views || 0}</span>
          </div>

          <div className="mb-8">
            <h2 className="text-xl font-semibold text-neutral-200 mb-3">Synopsis</h2>
            <p className="text-neutral-400 leading-relaxed">
              {manga.synopsis || 'No synopsis available'}
            </p>
          </div>

          <div>
            <h2 className="text-xl font-semibold text-neutral-200 mb-4">Chapters</h2>
            <div className="space-y-2">
              {manga.chapters && manga.chapters.length > 0 ? (
                manga.chapters.map((chapter: any) => (
                  <Link
                    key={chapter.id}
                    to={`/read/${manga.id}/${chapter.id}`}
                    className="block bg-neutral-900 border border-neutral-800 rounded-lg p-4 hover:border-emerald-600 transition-colors"
                  >
                    <div className="flex items-center justify-between">
                      <span className="font-medium text-neutral-200">
                        Chapter {chapter.chapter_number}
                        {chapter.title && ` - ${chapter.title}`}
                      </span>
                      <span className="text-sm text-neutral-500">
                        {chapter.page_count} pages
                      </span>
                    </div>
                  </Link>
                ))
              ) : (
                <p className="text-neutral-500">No chapters available</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
