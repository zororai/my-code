@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Class Formats</h1>
            <p class="text-gray-600 mt-1">Define the naming format and structure of classes in your school</p>
        </div>
        <div class="flex gap-3">
            <button onclick="showBulkModal()" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:from-green-700 hover:to-emerald-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Bulk Add Classes
            </button>
            <button onclick="showAddModal()" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Class Format
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Class Formats</strong> define how classes are named and ordered in your school. The numeric value determines the upgrade sequence.
                </p>
            </div>
        </div>
    </div>

    <!-- Class Formats Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Defined Class Formats</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numeric Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($classFormats as $format)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $format->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-bold text-sm">{{ substr($format->format_name, 0, 2) }}</span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $format->format_name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $format->numeric_value }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $format->display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($format->is_active)
                                <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="showEditModal({{ json_encode($format) }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form action="{{ route('admin.settings.class-formats.delete', $format->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this class format?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No class formats defined</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by adding a new class format.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Existing Classes Reference -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Existing Classes in System</h3>
            <p class="text-sm text-gray-500 mt-1">Reference: Classes currently defined in the system</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numeric Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($existingClasses as $class)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $class->class_name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $class->class_numeric }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $class->class_description ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                            No classes defined in the system yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Class Format Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideAddModal()"></div>
        
        <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" onclick="hideAddModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Add Class Format</h3>
                    <form action="{{ route('admin.settings.class-formats.store') }}" method="POST" class="mt-4">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Format Name</label>
                                <input type="text" name="format_name" placeholder="e.g., Grade 1, Form 1, Year 1" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Numeric Value</label>
                                <input type="number" name="numeric_value" min="0" placeholder="e.g., 1, 2, 3" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Used to determine upgrade sequence</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                                <input type="text" name="display_name" placeholder="e.g., First Grade, Form One" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" min="0" placeholder="e.g., 1, 2, 3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Add Format
                            </button>
                            <button type="button" onclick="hideAddModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Classes Modal -->
<div id="bulkModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideBulkModal()"></div>
        
        <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full sm:p-6">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" onclick="hideBulkModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-2">Bulk Add Classes</h3>
                <p class="text-sm text-gray-500 mb-4">Select grade levels and configure their class names</p>
                
                <form action="{{ route('admin.settings.class-formats.bulk-store') }}" method="POST" id="bulkForm">
                    @csrf
                    
                    <div class="grid grid-cols-12 gap-4">
                        <!-- Left Side: Grade Level List -->
                        <div class="col-span-3 border-r pr-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Select Grade Levels</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="ECD A" data-numeric="0">
                                    <span class="ml-2 text-sm text-gray-700">ECD A</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="ECD B" data-numeric="1">
                                    <span class="ml-2 text-sm text-gray-700">ECD B</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 1" data-numeric="2">
                                    <span class="ml-2 text-sm text-gray-700">Grade 1</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 2" data-numeric="3">
                                    <span class="ml-2 text-sm text-gray-700">Grade 2</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 3" data-numeric="4">
                                    <span class="ml-2 text-sm text-gray-700">Grade 3</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 4" data-numeric="5">
                                    <span class="ml-2 text-sm text-gray-700">Grade 4</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 5" data-numeric="6">
                                    <span class="ml-2 text-sm text-gray-700">Grade 5</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 6" data-numeric="7">
                                    <span class="ml-2 text-sm text-gray-700">Grade 6</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Grade 7" data-numeric="8">
                                    <span class="ml-2 text-sm text-gray-700">Grade 7</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 1" data-numeric="9">
                                    <span class="ml-2 text-sm text-gray-700">Form 1</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 2" data-numeric="10">
                                    <span class="ml-2 text-sm text-gray-700">Form 2</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 3" data-numeric="11">
                                    <span class="ml-2 text-sm text-gray-700">Form 3</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 4" data-numeric="12">
                                    <span class="ml-2 text-sm text-gray-700">Form 4</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 5" data-numeric="13">
                                    <span class="ml-2 text-sm text-gray-700">Form 5</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" class="grade-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" value="Form 6" data-numeric="14">
                                    <span class="ml-2 text-sm text-gray-700">Form 6</span>
                                </label>
                            </div>
                        </div>

                        <!-- Right Side: Configuration for Selected Grades -->
                        <div class="col-span-9">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Configure Selected Grades</h4>
                            <div id="gradeConfigContainer" class="space-y-4 max-h-96 overflow-y-auto">
                                <p class="text-sm text-gray-500 text-center py-8">Select grade levels from the left to configure</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                        <button type="button" onclick="hideBulkModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Cancel
                        </button>
                        <button type="submit" id="bulkSubmitBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Create All Classes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Class Format Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideEditModal()"></div>
        
        <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" onclick="hideEditModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-indigo-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Class Format</h3>
                    <form id="editForm" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Format Name</label>
                                <input type="text" name="format_name" id="edit_format_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Numeric Value</label>
                                <input type="number" name="numeric_value" id="edit_numeric_value" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                                <input type="text" name="display_name" id="edit_display_name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" id="edit_sort_order" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="edit_is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="edit_is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update Format
                            </button>
                            <button type="button" onclick="hideEditModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function hideAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

function showBulkModal() {
    document.getElementById('bulkModal').classList.remove('hidden');
}

function hideBulkModal() {
    document.getElementById('bulkModal').classList.add('hidden');
}

function showEditModal(format) {
    document.getElementById('editForm').action = '{{ url("admin/settings/class-formats") }}/' + format.id;
    document.getElementById('edit_format_name').value = format.format_name;
    document.getElementById('edit_numeric_value').value = format.numeric_value;
    document.getElementById('edit_display_name').value = format.display_name;
    document.getElementById('edit_sort_order').value = format.sort_order;
    document.getElementById('edit_is_active').checked = format.is_active;
    document.getElementById('editModal').classList.remove('hidden');
}

function hideEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Bulk modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const gradeCheckboxes = document.querySelectorAll('.grade-checkbox');
    const configContainer = document.getElementById('gradeConfigContainer');
    const bulkForm = document.getElementById('bulkForm');
    
    // Store templates data from server
    const templates = @json($formatTemplates);
    
    gradeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateGradeConfigs();
        });
    });
    
    // Form validation before submission
    bulkForm.addEventListener('submit', function(e) {
        const selectedGrades = Array.from(gradeCheckboxes).filter(cb => cb.checked);
        
        if (selectedGrades.length === 0) {
            e.preventDefault();
            alert('Please select at least one grade level from the left.');
            return false;
        }
        
        // Check each grade has at least one class name selected
        let hasError = false;
        selectedGrades.forEach(gradeCheckbox => {
            const gradeName = gradeCheckbox.value;
            const classNameCheckboxes = document.querySelectorAll(`input[name="grades[${gradeName}][class_names][]"]`);
            const hasClassNames = Array.from(classNameCheckboxes).some(cb => cb.checked);
            
            if (!hasClassNames) {
                hasError = true;
                alert(`Please select at least one class name for ${gradeName}`);
                return false;
            }
        });
        
        if (hasError) {
            e.preventDefault();
            return false;
        }
    });
    
    function updateGradeConfigs() {
        const selectedGrades = Array.from(gradeCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => ({
                name: cb.value,
                numeric: cb.dataset.numeric
            }));
        
        if (selectedGrades.length === 0) {
            configContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">Select grade levels from the left to configure</p>';
            return;
        }
        
        configContainer.innerHTML = selectedGrades.map(grade => `
            <div class="border rounded-lg p-4 bg-gray-50">
                <h5 class="font-semibold text-gray-800 mb-3">${grade.name}</h5>
                
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Numeric Value</label>
                        <input type="number" name="grades[${grade.name}][numeric_value]" value="${grade.numeric}" min="0" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Select Template</label>
                        <select class="template-selector w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" data-grade="${grade.name}">
                            <option value="">Choose a template...</option>
                            ${templates.map(t => `<option value="${t.id}" data-type="${t.type}" data-values="${t.values}">${t.name}</option>`).join('')}
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select a saved template or it will use default</p>
                    </div>
                </div>
                
                <div class="names-section-${grade.name}" data-grade-section="${grade.name}">
                    <label class="block text-xs font-medium text-gray-700 mb-2">Class Names</label>
                    <div class="grid grid-cols-3 gap-2 mb-2" data-checkbox-container="${grade.name}">
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="Blue" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Blue</span>
                        </label>
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="White" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">White</span>
                        </label>
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="Green" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Green</span>
                        </label>
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="Red" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Red</span>
                        </label>
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="Yellow" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Yellow</span>
                        </label>
                        <label class="flex items-center p-2 border rounded hover:bg-white text-xs">
                            <input type="checkbox" name="grades[${grade.name}][class_names][]" value="Orange" class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">Orange</span>
                        </label>
                    </div>
                    <input type="text" placeholder="Type custom name and press Enter" 
                        class="custom-class-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        data-grade="${grade.name}">
                </div>
                
                <div class="numeric-section-${grade.name} hidden">
                    <label class="block text-xs font-medium text-gray-700 mb-2">Number of Classes</label>
                    <input type="number" class="numeric-count w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                        min="1" max="20" value="3" placeholder="e.g., 3 for 1.1, 1.2, 1.3"
                        data-grade="${grade.name}">
                    <p class="text-xs text-gray-500 mt-1">This will create ${grade.name}.1, ${grade.name}.2, etc.</p>
                </div>
                
                <div class="custom-section-${grade.name} hidden">
                    <label class="block text-xs font-medium text-gray-700 mb-2">Enter Class Names</label>
                    <textarea class="custom-format-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                        rows="3" placeholder="Enter class names separated by commas, e.g., A, B, C, D or Alpha, Beta, Gamma"
                        data-grade="${grade.name}"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Separate each class name with a comma. Example: A, B, C, D</p>
                </div>
            </div>
        `).join('');
        
        // Attach event listeners after HTML is created
        attachTemplateListeners();
        attachCustomInputListeners();
    }
    
    function attachTemplateListeners() {
        // Add event listeners for template selection
        document.querySelectorAll('.template-selector').forEach(select => {
            select.addEventListener('change', function() {
                const gradeName = this.dataset.grade;
                const selectedOption = this.options[this.selectedIndex];
                const templateType = selectedOption.dataset.type;
                const templateValues = selectedOption.dataset.values;
                
                // Clear all existing selections for this grade
                document.querySelectorAll(`input[name="grades[${gradeName}][class_names][]"]`).forEach(el => {
                    if (el.type === 'checkbox') {
                        el.checked = false;
                    } else if (el.type === 'hidden' && (el.dataset.numeric === 'true' || el.dataset.custom === 'true')) {
                        el.remove();
                    }
                });
                
                // Remove any previously added custom checkboxes
                const namesSection = document.querySelector(`[data-grade-section="${gradeName}"]`);
                if (!namesSection) {
                    console.error('Names section not found for grade:', gradeName);
                    return;
                }
                const checkboxContainer = document.querySelector(`[data-checkbox-container="${gradeName}"]`);
                if (!checkboxContainer) {
                    console.error('Checkbox container not found for grade:', gradeName);
                    return;
                }
                checkboxContainer.querySelectorAll('[data-custom-checkbox="true"]').forEach(el => el.remove());
                
                if (!templateType || !templateValues) return;
                
                // Apply template based on type
                if (templateType === 'numeric') {
                    // Generate numeric classes as hidden inputs
                    const count = parseInt(templateValues);
                    for (let i = 1; i <= count; i++) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `grades[${gradeName}][class_names][]`;
                        input.value = `.${i}`;
                        input.setAttribute('data-numeric', 'true');
                        namesSection.appendChild(input);
                    }
                    
                    // Show visual feedback
                    const customInput = namesSection.querySelector('.custom-class-input');
                    customInput.value = `Will create: ${gradeName}.1, ${gradeName}.2, ${gradeName}.3...`;
                    customInput.disabled = true;
                    setTimeout(() => {
                        customInput.value = '';
                        customInput.disabled = false;
                    }, 2000);
                } else {
                    // For names and custom types
                    const classNames = templateValues.split(',').map(name => name.trim());
                    classNames.forEach(className => {
                        // Try to find matching checkbox
                        const checkbox = document.querySelector(`input[name="grades[${gradeName}][class_names][]"][value="${className}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        } else {
                            // Create visible checkbox for custom names
                            const label = document.createElement('label');
                            label.className = 'flex items-center p-2 border rounded hover:bg-white text-xs';
                            label.setAttribute('data-custom-checkbox', 'true');
                            label.innerHTML = `
                                <input type="checkbox" name="grades[${gradeName}][class_names][]" value=" ${className}" checked class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-gray-700">${className}</span>
                            `;
                            checkboxContainer.appendChild(label);
                        }
                    });
                }
            });
        });
        
    }
    
    function attachCustomInputListeners() {
        // Add event listeners for custom class inputs
        document.querySelectorAll('.custom-class-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const customName = this.value.trim();
                    const gradeName = this.dataset.grade;
                    if (customName) {
                        const container = this.previousElementSibling;
                        const label = document.createElement('label');
                        label.className = 'flex items-center p-2 border rounded hover:bg-white text-xs';
                        label.innerHTML = `
                            <input type="checkbox" name="grades[${gradeName}][class_names][]" value="${customName}" checked class="h-3 w-3 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">${customName}</span>
                        `;
                        container.appendChild(label);
                        this.value = '';
                    }
                }
            });
        });
    }
    
    function generateNumericClasses(gradeName) {
        // Remove existing numeric inputs for this grade
        document.querySelectorAll(`input[name="grades[${gradeName}][class_names][]"][data-numeric="true"]`).forEach(el => el.remove());
        
        const countInput = document.querySelector(`.numeric-count[data-grade="${gradeName}"]`);
        const count = parseInt(countInput.value) || 3;
        const numericSection = document.querySelector(`.numeric-section-${gradeName.replace(/\s+/g, '\\ ')}`);
        
        // Create hidden inputs for numeric classes
        for (let i = 1; i <= count; i++) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `grades[${gradeName}][class_names][]`;
            input.value = `.${i}`;
            input.setAttribute('data-numeric', 'true');
            numericSection.appendChild(input);
        }
    }
    
    function generateCustomClasses(gradeName, inputValue) {
        // Remove existing custom inputs for this grade
        document.querySelectorAll(`input[name="grades[${gradeName}][class_names][]"][data-custom="true"]`).forEach(el => el.remove());
        
        const customSection = document.querySelector(`.custom-section-${gradeName.replace(/\s+/g, '\\ ')}`);
        
        // Parse comma-separated values
        const classNames = inputValue.split(',')
            .map(name => name.trim())
            .filter(name => name.length > 0);
        
        // Create hidden inputs for each class name
        classNames.forEach(className => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `grades[${gradeName}][class_names][]`;
            input.value = ` ${className}`;
            input.setAttribute('data-custom', 'true');
            customSection.appendChild(input);
        });
    }
});
</script>
@endsection
