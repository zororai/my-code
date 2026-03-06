@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('admin.library.books') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $book->title }}</h1>
                    <p class="text-gray-600">Book Details</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.library.books.copies', $book->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Manage Copies
                </a>
                <a href="{{ route('admin.library.books.edit', $book->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Book
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Book Cover & Basic Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-96 bg-gray-200 flex items-center justify-center">
                        @if($book->image)
                            <img src="{{ asset($book->image) }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                        @else
                            <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="px-3 py-1 text-sm rounded-full {{ $book->condition === 'excellent' ? 'bg-green-100 text-green-800' : ($book->condition === 'good' ? 'bg-blue-100 text-blue-800' : ($book->condition === 'fair' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($book->condition) }}
                            </span>
                            <span class="px-3 py-1 text-sm rounded-full {{ $book->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($book->status) }}
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Copies:</span>
                                <span class="font-semibold">{{ $book->quantity }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Available:</span>
                                <span class="font-semibold text-green-600">{{ $book->available_quantity }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Borrowed:</span>
                                <span class="font-semibold text-red-600">{{ $book->quantity - $book->available_quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Information Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Book Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Title</label>
                            <p class="text-gray-900 font-semibold">{{ $book->title }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Book Number</label>
                            <p class="text-gray-900 font-mono">{{ $book->book_number }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Author</label>
                            <p class="text-gray-900">{{ $book->author ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">ISBN</label>
                            <p class="text-gray-900 font-mono">{{ $book->isbn ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Category</label>
                            <p class="text-gray-900">{{ $book->category ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Added On</label>
                            <p class="text-gray-900">{{ $book->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @if($book->condition_notes)
                        <div class="mt-4">
                            <label class="text-sm font-medium text-gray-600">Condition Notes</label>
                            <p class="text-gray-900 mt-1">{{ $book->condition_notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Book Copies -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Book Copies ({{ $book->copies->count() }})</h2>
                        <a href="{{ route('admin.library.books.copies.create', $book->id) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            + Add Copies
                        </a>
                    </div>
                    @if($book->copies->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Copy #</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ISBN</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($book->copies as $copy)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $copy->copy_number }}</td>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $copy->isbn }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $copy->condition === 'excellent' ? 'bg-green-100 text-green-800' : ($copy->condition === 'good' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($copy->condition) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $copy->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($copy->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <p>No copies added yet</p>
                            <a href="{{ route('admin.library.books.copies.create', $book->id) }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                                Add copies with ISBNs
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Recent Borrowing History -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Recent Borrowing History</h2>
                        <a href="{{ route('admin.library.books.history', $book->id) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            View All
                        </a>
                    </div>
                    @if($borrowHistory->count() > 0)
                        <div class="space-y-3">
                            @foreach($borrowHistory as $record)
                                <div class="border-l-4 {{ $record->status === 'borrowed' ? 'border-yellow-500' : 'border-green-500' }} pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                @if($record->borrower_type === 'student')
                                                    {{ $record->student->user->name ?? 'Unknown' }}
                                                    <span class="text-xs text-gray-500">(Student)</span>
                                                @else
                                                    {{ $record->teacher->user->name ?? 'Unknown' }}
                                                    <span class="text-xs text-gray-500">(Teacher)</span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Issued: {{ $record->issue_date->format('M d, Y') }}
                                                @if($record->return_date)
                                                    • Returned: {{ $record->return_date->format('M d, Y') }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $record->status === 'borrowed' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>No borrowing history yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
