@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Received Exchange Requests</h1>
        <p class="text-gray-600 mt-2">View and manage incoming currency exchange requests from users</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($requests->count() > 0)
        <div class="grid grid-cols-1 gap-6">
            @foreach($requests as $request)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($request->status === 'accepted') bg-blue-100 text-blue-800
                                        @elseif($request->status === 'user_payment_confirmed') bg-purple-100 text-purple-800
                                        @elseif($request->status === 'completed') bg-green-100 text-green-800
                                        @elseif($request->status === 'rejected') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                    <span class="ml-3 text-sm text-gray-500">{{ $request->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-gray-500">Ref: {{ $request->transaction_reference }}</p>
                            </div>
                        </div>

                        <!-- Currency Combination -->
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Currency Combination</h3>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <span class="text-lg font-bold text-blue-600">{{ $request->source_currency }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">User wants to sell</p>
                                        <p class="text-lg font-bold text-gray-900">{{ number_format($request->source_amount, 2) }} {{ $request->source_currency }}</p>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                <div class="flex items-center space-x-2">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                        <span class="text-lg font-bold text-green-600">{{ $request->destination_currency }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">User wants to buy</p>
                                        <p class="text-lg font-bold text-gray-900">{{ $request->destination_currency }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Requested Amount -->
                        <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Requested Amount to be Exchanged</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($request->source_amount, 2) }} {{ $request->source_currency }}</p>
                        </div>

                        <!-- User Information -->
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Requested By</h3>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-sm font-semibold text-gray-700">{{ substr($request->user->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $request->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $request->user->email }}</p>
                                </div>
                            </div>
                        </div>

                        @if($request->notes)
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Notes</h3>
                                <p class="text-sm text-gray-600">{{ $request->notes }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex gap-3 mt-6">
                            @if($request->status === 'pending')
                                <form action="{{ route('exchange-requests.accept', $request->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Accept Offer
                                    </button>
                                </form>
                                <button onclick="openRejectModal({{ $request->id }})" class="flex-1 px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Reject Offer
                                </button>
                            @elseif($request->status === 'accepted')
                                <a href="{{ route('exchange-requests.confirmation', $request->id) }}" class="flex-1 px-6 py-3 bg-blue-600 text-white text-center font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    View Details
                                </a>
                            @elseif($request->status === 'user_payment_confirmed')
                                <a href="{{ route('exchange-requests.confirmation', $request->id) }}" class="flex-1 px-6 py-3 bg-purple-600 text-white text-center font-semibold rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                    Complete Exchange
                                </a>
                            @else
                                <a href="{{ route('exchange-requests.show', $request->id) }}" class="flex-1 px-6 py-3 bg-gray-600 text-white text-center font-semibold rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    View Details
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $requests->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Exchange Requests</h3>
            <p class="text-gray-500">There are currently no exchange requests to display.</p>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Exchange Request</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Rejection (Optional)</label>
                    <textarea name="rejection_reason" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Provide a reason for rejecting this request..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">
                        Reject
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="flex-1 px-4 py-2 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(requestId) {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectForm').action = '/exchange-requests/' + requestId + '/reject';
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
