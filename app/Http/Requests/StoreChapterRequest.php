<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChapterRequest extends FormRequest
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
        $chapterId = $this->route('chapter')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // Basic Information
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('chapters', 'slug')->ignore($chapterId),
            ],
            'chapter_number' => 'required|string|max:50',
            'title' => 'nullable|string|max:500',
            'volume_number' => 'nullable|integer|min:1|max:999',

            // Content
            'notes' => 'nullable|string|max:5000',
            'release_date' => 'nullable|date',

            // CBZ File - required on create, optional on update
            'cbz_file' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:zip,cbz',
                'max:512000', // 500MB max
            ],

            // Organization
            'sort_order' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',

            // Flags
            'is_active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }

        if ($this->has('is_premium')) {
            $this->merge([
                'is_premium' => $this->boolean('is_premium'),
            ]);
        }

        // Set default sort_order if not provided
        if (!$this->has('sort_order') && $this->has('chapter_number')) {
            // Extract numeric value from chapter_number for sort_order
            preg_match('/(\d+)/', $this->input('chapter_number'), $matches);
            if (!empty($matches[1])) {
                $this->merge([
                    'sort_order' => (int) $matches[1],
                ]);
            }
        }
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'chapter_number' => 'chapter number',
            'volume_number' => 'volume number',
            'release_date' => 'release date',
            'cbz_file' => 'CBZ file',
            'sort_order' => 'sort order',
            'is_active' => 'active status',
            'is_premium' => 'premium status',
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
            'slug.unique' => 'This slug is already in use by another chapter.',
            'chapter_number.required' => 'The chapter number is required.',
            'cbz_file.required' => 'A CBZ file is required when creating a new chapter.',
            'cbz_file.mimes' => 'The file must be a valid CBZ or ZIP archive.',
            'cbz_file.max' => 'The CBZ file must not be larger than 500MB.',
            'volume_number.min' => 'The volume number must be at least 1.',
            'volume_number.max' => 'The volume number must not exceed 999.',
            'sort_order.min' => 'The sort order must not be negative.',
        ];
    }
}
