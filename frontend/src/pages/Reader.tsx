import { useParams, useNavigate } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { ChevronLeft, ChevronRight } from 'lucide-react'

export default function Reader() {
  const { mangaId, chapterId } = useParams()
  const navigate = useNavigate()
  const [currentPage, setCurrentPage] = useState(1)
  const [pageCount, setPageCount] = useState(0)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const fetchPages = async () => {
      try {
        const response = await fetch(
          `http://localhost:8000/api/v1/chapters/${chapterId}/pages`
        )
        const data = await response.json()
        setPageCount(data.page_count || 0)
      } catch (error) {
        setError('Failed to load chapter pages')
      } finally {
        setLoading(false)
      }
    }

    if (chapterId) {
      fetchPages()
    }
  }, [chapterId])

  const getPageUrl = (page: number) => {
    return `http://localhost:8000/api/v1/chapters/${chapterId}/page/${page}`
  }

  const handlePrev = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1)
    }
  }

  const handleNext = () => {
    if (currentPage < pageCount) {
      setCurrentPage(currentPage + 1)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <p className="text-red-400">{error}</p>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-neutral-950">
      <div className="max-w-4xl mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <button
            onClick={() => navigate(`/manga/${mangaId}`)}
            className="flex items-center space-x-2 text-neutral-400 hover:text-emerald-600 transition-colors"
          >
            <ChevronLeft className="h-5 w-5" />
            <span>Back to Manga</span>
          </button>
          <span className="text-neutral-400">
            Page {currentPage} of {pageCount}
          </span>
        </div>

        <div className="bg-neutral-900 rounded-lg overflow-hidden">
          {currentPage <= pageCount ? (
            <img
              src={getPageUrl(currentPage)}
              alt={`Page ${currentPage}`}
              className="w-full h-auto"
              loading="lazy"
            />
          ) : (
            <div className="py-12 text-center text-neutral-400">
              End of chapter
            </div>
          )}
        </div>

        <div className="flex items-center justify-between mt-6">
          <button
            onClick={handlePrev}
            disabled={currentPage <= 1}
            className="flex items-center space-x-2 px-4 py-2 bg-neutral-900 border border-neutral-800 rounded hover:border-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronLeft className="h-5 w-5" />
            <span>Previous</span>
          </button>

          <div className="flex space-x-2">
            {Math.max(1, currentPage - 2)}
            {currentPage > 2 && <span className="text-neutral-500">...</span>}
            <span className="px-3 py-1 bg-emerald-600 text-white rounded">
              {currentPage}
            </span>
            {currentPage < pageCount - 1 && <span className="text-neutral-500">...</span>}
            {Math.min(pageCount, currentPage + 2)}
          </div>

          <button
            onClick={handleNext}
            disabled={currentPage >= pageCount}
            className="flex items-center space-x-2 px-4 py-2 bg-neutral-900 border border-neutral-800 rounded hover:border-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span>Next</span>
            <ChevronRight className="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  )
}
