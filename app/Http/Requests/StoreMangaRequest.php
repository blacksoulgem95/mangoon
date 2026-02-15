<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMangaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mangaId = $this->route('manga')?->id;

        return [
            // Basic Information
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('mangas', 'slug')->ignore($mangaId),
            ],
            'source_id' => 'nullable|exists:sources,id',
            'author' => 'nullable|string|max:255',
            'illustrator' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',

            // Publication Information
            'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'publication_date' => 'nullable|date',
            'original_language' => 'nullable|string|max:10',

            // Status and Type
            'status' => [
                'nullable',
                'string',
                Rule::in(['ongoing', 'completed', 'hiatus', 'cancelled', 'upcoming']),
            ],
            'type' => [
                'nullable',
                'string',
                Rule::in(['manga', 'manhwa', 'manhua', 'webtoon', 'novel', 'one-shot', 'doujinshi']),
            ],

            // Images
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:10240',
            'banner_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:10240',

            // Numbers
            'total_chapters' => 'nullable|integer|min:0',
            'total_volumes' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:10',

            // Metadata
            'metadata' => 'nullable|array',

            // Flags
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_mature' => 'nullable|boolean',

            // Relationships
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'libraries' => 'nullable|array',
            'libraries.*' => 'exists:libraries,id',

            // Translations
            'translations' => 'nullable|array',
            'translations.*.language_code' => 'required_with:translations|string|max:10|exists:languages,code',
            'translations.*.title' => 'required_with:translations|string|max:500',
            'translations.*.synopsis' => 'nullable|string|max:1000',
            'translations.*.description' => 'nullable|string',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'source_id' => 'source',
            'publication_year' => 'publication year',
            'publication_date' => 'publication date',
            'original_language' => 'original language',
            'cover_image' => 'cover image',
            'banner_image' => 'banner image',
            'total_chapters' => 'total chapters',
            'total_volumes' => 'total volumes',
            'is_active' => 'active status',
            'is_featured' => 'featured status',
            'is_mature' => 'mature content flag',
            'translations.*.language_code' => 'translation language',
            'translations.*.title' => 'translation title',
            'translations.*.synopsis' => 'translation synopsis',
            'translations.*.description' => 'translation description',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug must only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already in use by another manga.',
            'status.in' => 'The selected status is invalid. Must be: ongoing, completed, hiatus, cancelled, or upcoming.',
            'type.in' => 'The selected type is invalid. Must be: manga, manhwa, manhua, webtoon, novel, one-shot, or doujinshi.',
            'cover_image.max' => 'The cover image must not be larger than 10MB.',
            'banner_image.max' => 'The banner image must not be larger than 10MB.',
            'rating.max' => 'The rating must not be greater than 10.',
            'rating.min' => 'The rating must not be less than 0.',
            'translations.*.title.required_with' => 'Each translation must have a title.',
            'translations.*.language_code.required_with' => 'Each translation must have a language.',
            'translations.*.language_code.exists' => 'The selected translation language does not exist.',
        ];
    }
}
