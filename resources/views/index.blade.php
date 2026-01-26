@extends('custom-fields::layout')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                    Custom Fields
                </h1>
                <p class="mt-2 text-sm text-gray-500 font-medium">Powering dynamic data across your application.</p>
            </div>
            <div class="mt-6 md:mt-0 flex items-center space-x-4">
                <a href="{{ route('custom-fields.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-1">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Field
                </a>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Total Fields -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 transition-all hover:shadow-md">
                <div class="p-4 bg-indigo-50 rounded-xl text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Total Fields</p>
                    <p class="text-2xl font-black text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>

            <!-- Active Models -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 transition-all hover:shadow-md">
                <div class="p-4 bg-blue-50 rounded-xl text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Active Models</p>
                    <p class="text-2xl font-black text-gray-900">{{ $stats['models'] }}</p>
                </div>
            </div>

            <!-- Field Types -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 transition-all hover:shadow-md">
                <div class="p-4 bg-purple-50 rounded-xl text-purple-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Field Types</p>
                    <p class="text-2xl font-black text-gray-900">{{ $stats['types'] }}</p>
                </div>
            </div>

            <!-- Required Fields -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-4 transition-all hover:shadow-md">
                <div class="p-4 bg-amber-50 rounded-xl text-amber-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Required</p>
                    <p class="text-2xl font-black text-gray-900">{{ $stats['required'] }}</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 backdrop-blur-sm bg-white/80">
            <form action="{{ route('custom-fields.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="hidden" name="trashed" value="{{ request('trashed') }}">

                <!-- Search -->
                <div class="col-span-1 md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Search Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Field name..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all">
                    </div>
                </div>

                <!-- Model Filter -->
                <div>
                    <label for="model" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Model</label>
                    <select name="model" id="model" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Type</label>
                    <select name="type" id="type" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Required Filter -->
                <div>
                    <label for="required" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Requirement</label>
                    <select name="required" id="required" class="block w-full py-2 px-3 border border-gray-200 bg-gray-50 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('required') === '1' ? 'selected' : '' }}>Required</option>
                        <option value="0" {{ request('required') === '0' ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="col-span-1 md:col-span-5 flex justify-end space-x-2 mt-2">
                    <a href="{{ route('custom-fields.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        Reset Filters
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        Apply Filters
                    </button>
                </div>
            </form>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-start space-x-4">
                <a href="{{ route('custom-fields.index', ['trashed' => '']) }}" class="text-xs font-medium px-3 py-1 rounded-full {{ !request('trashed') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:bg-gray-100' }}">
                    Active ({{ \Salah\LaravelCustomFields\Models\CustomField::count() }})
                </a>
                <a href="{{ route('custom-fields.index', ['trashed' => 'only']) }}" class="text-xs font-medium px-3 py-1 rounded-full {{ request('trashed') == 'only' ? 'bg-red-100 text-red-700' : 'text-gray-500 hover:bg-gray-100' }}">
                    Trashed ({{ \Salah\LaravelCustomFields\Models\CustomField::onlyTrashed()->count() }})
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-8 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Field Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Model & Type</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Is Required</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($customFields as $field)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">#{{ $field->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900 border-l-4 border-indigo-500 pl-3">{{ $field->name }}</div>
                            <div class="text-xs text-gray-500 mt-1 pl-3">{{ $field->placeholder ?: 'No placeholder' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-1">
                                {{ class_basename($field->model) }}
                            </span>
                            <br>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                                {{ ucfirst($field->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($field->required)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 shadow-sm border border-amber-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-2"></span>
                                YES
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-400 mr-2"></span>
                                NO
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                @if($field->trashed())
                                <form action="{{ route('custom-fields.restore', $field->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors">Restore</button>
                                </form>
                                <form action="{{ route('custom-fields.force-delete', $field->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Permanently delete?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold transition-colors">Destroy</button>
                                </form>
                                @else
                                <a href="{{ route('custom-fields.edit', $field->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('custom-fields.destroy', $field->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" onclick="return confirm('Move to trash?')">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium">No custom fields found</p>
                                <p class="text-sm">Try adjusting your filters or search query.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $customFields->links('custom-fields::pagination') }}
        </div>
    </div>
</div>

<style>
    /* Premium Font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    /* Custom Scrollbar for modern look */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: #c7d2fe;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a5b4fc;
    }

    /* Glassmorphism subtle effect */
    .backdrop-blur-sm {
        backdrop-filter: blur(4px);
    }
</style>
@endsection