@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Book Copies</h1>
            <p class="text-gray-600">{{ $book->title }} by {{ $book->author ?? 'Unknown Author' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.library.books.copies.create', $book->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Copies
            </a>
            <a href="{{ route('admin.library.books') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Back to Books
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Copies</div>
            <div class="text-2xl font-bold text-gray-800">{{ $book->copies->count() }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-4">
            <div class="text-sm text-green-600">Available</div>
            <div class="text-2xl font-bold text-green-800">{{ $book->copies->where('status', 'available')->count() }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow p-4">
            <div class="text-sm text-yellow-600">Borrowed</div>
            <div class="text-2xl font-bold text-yellow-800">{{ $book->copies->where('status', 'borrowed')->count() }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-4">
            <div class="text-sm text-red-600">Lost/Damaged</div>
            <div class="text-2xl font-bold text-red-800">{{ $book->copies->whereIn('status', ['lost', 'damaged'])->count() }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Copy #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ISBN</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Borrowed By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($book->copies as $copy)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $copy->copy_number }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $copy->isbn }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $copy->condition_badge }}">
                                {{ ucfirst($copy->condition) }}
                            </span>
                            @if($copy->condition_notes)
                            <span class="text-xs text-gray-500 block mt-1">{{ Str::limit($copy->condition_notes, 30) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $copy->status_badge }}">
                                {{ ucfirst($copy->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($copy->activeBorrow)
                                {{ $copy->activeBorrow->borrower_name ?? 'Unknown' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            {{ $copy->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            No copies added yet. 
                            <a href="{{ route('admin.library.books.copies.create', $book->id) }}" class="text-blue-600 hover:underline">Add copies now</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
