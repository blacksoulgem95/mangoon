{{-- resources/views/admin/libraries/create.blade.php --}}
@extends('layouts.app')

@section('title', ' • Admin: Create New Library')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-future text-3xl font-semibold text-pip-green">Create New Library</h1>

        <a href="{{ route('admin.libraries.index') }}" class="btn btn-secondary flex items-center gap-2">
            <i class="gg-arrow-left"></i>
            <span class="terminal-text">Back to Libraries</span>
        </a>
    </div>

    <div class="panel p-6">
        <form action="{{ route('admin.libraries.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Library Name --}}
                <div>
                    <label for="name" class="block text-text-primary text-sm font-medium mb-2">Library Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slug (Optional, auto-generated if empty) --}}
                <div>
                    <label for="slug" class="block text-text-primary text-sm font-medium mb-2">Slug (Optional)</label>
                    <input type="text" name="slug" id="slug" class="form-input" value="{{ old('slug') }}">
                    @error('slug')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Icon (Optional, e.g., gg-folder) --}}
                <div>
                    <label for="icon" class="block text-text-primary text-sm font-medium mb-2">Icon (e.g., gg-folder)</label>
                    <input type="text" name="icon" id="icon" class="form-input" value="{{ old('icon') }}" placeholder="gg-folder">
                    @error('icon')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color (Hex Code) --}}
                <div>
                    <label for="color" class="block text-text-primary text-sm font-medium mb-2">Color (Hex, e.g., #RRGGBB)</label>
                    <input type="text" name="color" id="color" class="form-input" value="{{ old('color') }}" placeholder="#000000">
                    @error('color')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sort Order --}}
                <div>
                    <label for="sort_order" class="block text-text-primary text-sm font-medium mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-input" value="{{ old('sort_order', 0) }}">
                    @error('sort_order')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Is Active --}}
                <div class="md:col-span-2">
                    <label for="is_active" class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" class="form-checkbox" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="text-text-primary text-sm font-medium">Is Active</span>
                    </label>
                    @error('is_active')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Is Public --}}
                <div class="md:col-span-2">
                    <label for="is_public" class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_public" id="is_public" class="form-checkbox" {{ old('is_public', true) ? 'checked' : '' }}>
                        <span class="text-text-primary text-sm font-medium">Is Public</span>
                    </label>
                    @error('is_public')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-text-primary text-sm font-medium mb-2">Description (Optional)</label>
                    <textarea name="description" id="description" rows="4" class="form-textarea">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <i class="gg-add"></i>
                    <span class="terminal-text">Create Library</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
