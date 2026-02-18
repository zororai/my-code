@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Term</h2>
            <p class="text-gray-600 mt-1">{{ ucfirst($resultStatus->result_period) }} Term {{ $resultStatus->year }}</p>
        </div>
        <a href="{{ route('results_status.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Terms
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('results_status.update', $resultStatus->id) }}" method="POST" id="termForm">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select name="year" id="year" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select year</option>
                        @for($y = 2024; $y <= 2030; $y++)
                            <option value="{{ $y }}" {{ (old('year', $resultStatus->year) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    @error('year')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="result_period" class="block text-sm font-medium text-gray-700 mb-2">Term Period</label>
                    <select name="result_period" id="result_period" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a Term</option>
                        <option value="first" {{ (old('result_period', $resultStatus->result_period) == 'first') ? 'selected' : '' }}>First Term</option>
                        <option value="second" {{ (old('result_period', $resultStatus->result_period) == 'second') ? 'selected' : '' }}>Second Term</option>
                        <option value="third" {{ (old('result_period', $resultStatus->result_period) == 'third') ? 'selected' : '' }}>Third Term</option>
                    </select>
                    @error('result_period')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        @if(isset($feeLevelGroups) && $feeLevelGroups->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Fee Structure by Level Group
                </span>
            </h3>
            
            <div class="border-b border-gray-200 mb-4">
                <nav class="flex flex-wrap gap-1">
                    @foreach($feeLevelGroups as $index => $group)
                    <button type="button" 
                        onclick="switchTab('{{ $group->id }}')" 
                        id="tab-{{ $group->id }}" 
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 {{ $index === 0 ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $group->name }}
                    </button>
                    @endforeach
                </nav>
            </div>

            @foreach($feeLevelGroups as $index => $group)
            <div id="panel-{{ $group->id }}" class="tab-panel {{ $index !== 0 ? 'hidden' : '' }}">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600 mb-2"><strong>Class Range:</strong> {{ $group->class_range }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-indigo-700 mb-3">ZIMSEC Curriculum</h4>
                        @foreach(['zimsec_day_existing' => 'Day (Existing)', 'zimsec_day_new' => 'Day (New)', 'zimsec_boarding_existing' => 'Boarding (Existing)', 'zimsec_boarding_new' => 'Boarding (New)'] as $key => $label)
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                            <div class="fee-container" id="fees-{{ $group->id }}-{{ $key }}">
                                @php $fees = $existingFeeStructures[$group->id][$key] ?? []; @endphp
                                @if(count($fees) > 0)
                                    @foreach($fees as $idx => $fee)
                                    <div class="fee-row flex gap-2 mb-2">
                                        <select name="fee_structures[{{ $group->id }}][{{ $key }}][{{ $idx }}][fee_type_id]" class="flex-1 border rounded px-2 py-1 text-sm">
                                            <option value="">Select Fee</option>
                                            @foreach($feeTypes as $type)
                                            <option value="{{ $type->id }}" {{ ($fee['fee_type_id'] == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="fee_structures[{{ $group->id }}][{{ $key }}][{{ $idx }}][amount]" value="{{ $fee['amount'] }}" step="0.01" min="0" placeholder="Amount" class="w-28 border rounded px-2 py-1 text-sm">
                                        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 px-2">x</button>
                                    </div>
                                    @endforeach
                                @else
                                <div class="fee-row flex gap-2 mb-2">
                                    <select name="fee_structures[{{ $group->id }}][{{ $key }}][0][fee_type_id]" class="flex-1 border rounded px-2 py-1 text-sm">
                                        <option value="">Select Fee</option>
                                        @foreach($feeTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="fee_structures[{{ $group->id }}][{{ $key }}][0][amount]" step="0.01" min="0" placeholder="Amount" class="w-28 border rounded px-2 py-1 text-sm">
                                    <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 px-2">x</button>
                                </div>
                                @endif
                            </div>
                            <button type="button" onclick="addFeeRow('{{ $group->id }}', '{{ $key }}')" class="text-xs text-blue-600 hover:text-blue-800">+ Add Fee</button>
                        </div>
                        @endforeach
                    </div>

                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-purple-700 mb-3">Cambridge Curriculum</h4>
                        @foreach(['cambridge_day_existing' => 'Day (Existing)', 'cambridge_day_new' => 'Day (New)', 'cambridge_boarding_existing' => 'Boarding (Existing)', 'cambridge_boarding_new' => 'Boarding (New)'] as $key => $label)
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                            <div class="fee-container" id="fees-{{ $group->id }}-{{ $key }}">
                                @php $fees = $existingFeeStructures[$group->id][$key] ?? []; @endphp
                                @if(count($fees) > 0)
                                    @foreach($fees as $idx => $fee)
                                    <div class="fee-row flex gap-2 mb-2">
                                        <select name="fee_structures[{{ $group->id }}][{{ $key }}][{{ $idx }}][fee_type_id]" class="flex-1 border rounded px-2 py-1 text-sm">
                                            <option value="">Select Fee</option>
                                            @foreach($feeTypes as $type)
                                            <option value="{{ $type->id }}" {{ ($fee['fee_type_id'] == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="fee_structures[{{ $group->id }}][{{ $key }}][{{ $idx }}][amount]" value="{{ $fee['amount'] }}" step="0.01" min="0" placeholder="Amount" class="w-28 border rounded px-2 py-1 text-sm">
                                        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 px-2">x</button>
                                    </div>
                                    @endforeach
                                @else
                                <div class="fee-row flex gap-2 mb-2">
                                    <select name="fee_structures[{{ $group->id }}][{{ $key }}][0][fee_type_id]" class="flex-1 border rounded px-2 py-1 text-sm">
                                        <option value="">Select Fee</option>
                                        @foreach($feeTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="fee_structures[{{ $group->id }}][{{ $key }}][0][amount]" step="0.01" min="0" placeholder="Amount" class="w-28 border rounded px-2 py-1 text-sm">
                                    <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 px-2">x</button>
                                </div>
                                @endif
                            </div>
                            <button type="button" onclick="addFeeRow('{{ $group->id }}', '{{ $key }}')" class="text-xs text-purple-600 hover:text-purple-800">+ Add Fee</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Attendance Settings
                </span>
            </h3>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Session Mode</label>
                <div class="flex flex-wrap gap-4">
                    @php $sessionMode = $attendanceSettings['session_mode'] ?? 'morning'; @endphp
                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer {{ $sessionMode == 'morning' ? 'border-amber-500 bg-amber-50' : 'border-gray-200' }}">
                        <input type="radio" name="session_mode" value="morning" {{ $sessionMode == 'morning' ? 'checked' : '' }} class="h-4 w-4 text-amber-600" onchange="toggleAfternoon()">
                        <span class="ml-2 font-medium">Morning Only</span>
                    </label>
                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer {{ $sessionMode == 'afternoon' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                        <input type="radio" name="session_mode" value="afternoon" {{ $sessionMode == 'afternoon' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600" onchange="toggleAfternoon()">
                        <span class="ml-2 font-medium">Afternoon Only</span>
                    </label>
                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer {{ $sessionMode == 'dual' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}">
                        <input type="radio" name="session_mode" value="dual" {{ $sessionMode == 'dual' ? 'checked' : '' }} class="h-4 w-4 text-purple-600" onchange="toggleAfternoon()">
                        <span class="ml-2 font-medium">Dual Session</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-In Time</label>
                    <input type="time" name="check_in_time" value="{{ $attendanceSettings['check_in_time'] ?? '07:30' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-Out Time</label>
                    <input type="time" name="check_out_time" value="{{ $attendanceSettings['check_out_time'] ?? '16:30' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Late Grace (minutes)</label>
                    <input type="number" name="late_grace_minutes" value="{{ $attendanceSettings['late_grace_minutes'] ?? 0 }}" min="0" max="60" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            <div id="afternoon_fields" class="{{ $sessionMode == 'dual' ? '' : 'hidden' }}">
                <h4 class="font-medium text-indigo-700 mb-3">Afternoon Session Times</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Afternoon Check-In</label>
                        <input type="time" name="afternoon_check_in_time" value="{{ $attendanceSettings['afternoon_check_in_time'] ?? '12:30' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Afternoon Check-Out</label>
                        <input type="time" name="afternoon_check_out_time" value="{{ $attendanceSettings['afternoon_check_out_time'] ?? '17:30' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('results_status.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
                Update Term
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function switchTab(groupId) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(t => {
        t.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
        t.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById('panel-' + groupId).classList.remove('hidden');
    const tab = document.getElementById('tab-' + groupId);
    tab.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
    tab.classList.remove('border-transparent', 'text-gray-500');
}

function toggleAfternoon() {
    const mode = document.querySelector('input[name="session_mode"]:checked').value;
    document.getElementById('afternoon_fields').classList.toggle('hidden', mode !== 'dual');
}

const feeCounters = {};
function addFeeRow(groupId, category) {
    const container = document.getElementById('fees-' + groupId + '-' + category);
    const key = groupId + '_' + category;
    if (!feeCounters[key]) feeCounters[key] = container.querySelectorAll('.fee-row').length;
    const idx = feeCounters[key]++;
    const html = `<div class="fee-row flex gap-2 mb-2">
        <select name="fee_structures[${groupId}][${category}][${idx}][fee_type_id]" class="flex-1 border rounded px-2 py-1 text-sm">
            <option value="">Select Fee</option>
            @foreach($feeTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
        </select>
        <input type="number" name="fee_structures[${groupId}][${category}][${idx}][amount]" step="0.01" min="0" placeholder="Amount" class="w-28 border rounded px-2 py-1 text-sm">
        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 px-2">x</button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function removeRow(btn) {
    const row = btn.closest('.fee-row');
    const container = row.parentElement;
    if (container.querySelectorAll('.fee-row').length > 1) {
        row.remove();
    }
}
</script>
@endpush
