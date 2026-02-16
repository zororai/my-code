@include('dashboard.partials.stats-cards')

<!-- Pass/Fail Results by Gender Chart -->
<div class="w-full block mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Student Results by Gender</h3>
        <p class="text-sm text-gray-600 mb-6">Pass/Fail distribution for male and female students (Pass = 50% and above)</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bar Chart -->
            <div class="bg-gray-50 rounded-lg p-4">
                <canvas id="genderResultsChart" height="300"></canvas>
            </div>
            <!-- Statistics Summary -->
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-600">Male Students</p>
                                <p class="text-2xl font-bold text-blue-900">{{ ($genderStats['malePass'] ?? 0) + ($genderStats['maleFail'] ?? 0) }} Results</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex space-x-4">
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-700">Pass: <strong>{{ $genderStats['malePass'] ?? 0 }}</strong></span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-700">Fail: <strong>{{ $genderStats['maleFail'] ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="bg-pink-50 rounded-lg p-4 border border-pink-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-pink-600">Female Students</p>
                                <p class="text-2xl font-bold text-pink-900">{{ ($genderStats['femalePass'] ?? 0) + ($genderStats['femaleFail'] ?? 0) }} Results</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex space-x-4">
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-700">Pass: <strong>{{ $genderStats['femalePass'] ?? 0 }}</strong></span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                            <span class="text-sm text-gray-700">Fail: <strong>{{ $genderStats['femaleFail'] ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>
                <!-- Pass Rate Comparison -->
                <div class="bg-gray-100 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Pass Rate Comparison</p>
                    @php
                        $maleTotal = ($genderStats['malePass'] ?? 0) + ($genderStats['maleFail'] ?? 0);
                        $femaleTotal = ($genderStats['femalePass'] ?? 0) + ($genderStats['femaleFail'] ?? 0);
                        $malePassRate = $maleTotal > 0 ? round(($genderStats['malePass'] ?? 0) / $maleTotal * 100, 1) : 0;
                        $femalePassRate = $femaleTotal > 0 ? round(($genderStats['femalePass'] ?? 0) / $femaleTotal * 100, 1) : 0;
                    @endphp
                    <div class="space-y-2">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-600 font-medium">Male</span>
                                <span class="font-bold">{{ $malePassRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $malePassRate }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-pink-600 font-medium">Female</span>
                                <span class="font-bold">{{ $femalePassRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-pink-500 h-3 rounded-full" style="width: {{ $femalePassRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Classroom Population Chart -->
<div class="w-full block mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="mb-4 lg:mb-0">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Classroom Population</h3>
                <p class="text-sm text-gray-600">Number of students enrolled in each class</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <select id="populationGenderFilter" class="block w-full sm:w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Genders</option>
                    <option value="male">Male Only</option>
                    <option value="female">Female Only</option>
                </select>
            </div>
        </div>
        <div class="bg-gray-50 rounded-lg p-4" style="height: 400px;">
            <canvas id="classroomPopulationChart"></canvas>
        </div>
        <div id="populationSummaryStats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-blue-600">Total Classes</p>
                <p id="statTotalClasses" class="text-2xl font-bold text-blue-900">{{ $classroomPopulation->count() }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-green-600">Total Students</p>
                <p id="statTotalStudents" class="text-2xl font-bold text-green-900">{{ $classroomPopulation->sum('count') }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-purple-600">Largest Class</p>
                <p id="statLargestClass" class="text-2xl font-bold text-purple-900">{{ $classroomPopulation->max('count') ?? 0 }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <p class="text-sm font-medium text-orange-600">Average Size</p>
                <p id="statAverageSize" class="text-2xl font-bold text-orange-900">{{ $classroomPopulation->count() > 0 ? round($classroomPopulation->avg('count'), 1) : 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Performance Chart with Filters -->
@if(isset($assessmentStats))
<div class="w-full block mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="mb-4 lg:mb-0">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Assessment Performance by Type</h3>
                <p class="text-sm text-gray-600">Filter by class and subject to view specific performance</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                <select id="assessClassFilter" class="block w-full sm:w-36 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
                <select id="assessSubjectFilter" class="block w-full sm:w-36 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Subjects</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
                <select id="assessYearFilter" class="block w-full sm:w-28 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Years</option>
                    @php
                        $assessCurrentYear = date('Y');
                        for($y = $assessCurrentYear; $y >= $assessCurrentYear - 5; $y--) {
                            $selected = ($y == $currentYear) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                    @endphp
                </select>
                <select id="assessTermFilter" class="block w-full sm:w-28 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Terms</option>
                    <option value="first" {{ $currentPeriod == 'first' ? 'selected' : '' }}>Term 1</option>
                    <option value="second" {{ $currentPeriod == 'second' ? 'selected' : '' }}>Term 2</option>
                    <option value="third" {{ $currentPeriod == 'third' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-lg p-4" style="height: 350px;">
                <canvas id="assessmentPerformanceChart"></canvas>
            </div>
            <div>
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Assessment Summary</h5>
                <table class="w-full text-sm">
                    <thead>
                            <th class="text-left py-2 px-3">Type</th>
                            <th class="text-center py-2 px-3">Given</th>
                            <th class="text-center py-2 px-3">Avg Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessmentStats as $stat)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 px-3 text-gray-700">{{ $stat['type'] }}</td>
                            <td class="text-center py-2 px-3">{{ $stat['given'] }}</td>
                            <td class="text-center py-2 px-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $stat['performance'] >= 50 ? 'bg-green-100 text-green-700' : ($stat['performance'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                    {{ $stat['performance'] > 0 ? $stat['performance'] . '%' : '--' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Assessment Performance by Type by Gender -->
@if(isset($assessmentStatsByGender))
<div class="w-full block mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="mb-4 lg:mb-0">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Assessment Performance by Type & Gender</h3>
                <p class="text-sm text-gray-600">Compare how male and female students perform across different assessment types</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                <select id="genderClassFilter" class="block w-full sm:w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
                <select id="genderSubjectFilter" class="block w-full sm:w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
                <select id="genderYearFilter" class="block w-full sm:w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Years</option>
                    @php
                        $genderCurrentYearVal = date('Y');
                        for($y = $genderCurrentYearVal; $y >= $genderCurrentYearVal - 5; $y--) {
                            $selected = ($y == $currentYear) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                    @endphp
                </select>
                <select id="genderTermFilter" class="block w-full sm:w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Terms</option>
                    <option value="first" {{ $currentPeriod == 'first' ? 'selected' : '' }}>Term 1</option>
                    <option value="second" {{ $currentPeriod == 'second' ? 'selected' : '' }}>Term 2</option>
                    <option value="third" {{ $currentPeriod == 'third' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart -->
            <div class="bg-gray-50 rounded-lg p-4" style="height: 400px;">
                <canvas id="assessmentByGenderChart"></canvas>
            </div>
            <!-- Summary Table & Stats -->
            <div>
                <h5 class="text-sm font-semibold text-gray-700 mb-3">Performance Comparison</h5>
                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="bg-gray-100">
                                <th class="text-left py-2 px-3 rounded-l-lg">Type</th>
                                <th class="text-center py-2 px-3">
                                    <span class="inline-flex items-center">
                                        <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>Male
                                    </span>
                                </th>
                                <th class="text-center py-2 px-3">
                                    <span class="inline-flex items-center">
                                        <span class="w-3 h-3 bg-pink-500 rounded-full mr-2"></span>Female
                                    </span>
                                </th>
                                <th class="text-center py-2 px-3 rounded-r-lg">Better</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maleBetterCount = 0;
                                $femaleBetterCount = 0;
                                $tieCount = 0;
                            @endphp
                            @foreach($assessmentStatsByGender as $stat)
                            @php
                                $diff = $stat['male_performance'] - $stat['female_performance'];
                                if ($stat['male_performance'] > 0 || $stat['female_performance'] > 0) {
                                    if ($diff > 0) $maleBetterCount++;
                                    elseif ($diff < 0) $femaleBetterCount++;
                                    else $tieCount++;
                                }
                            @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-700 font-medium">{{ $stat['type'] }}</td>
                                <td class="text-center py-2 px-3">
                                    @if($stat['male_performance'] > 0)
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $stat['male_performance'] >= 50 ? 'bg-blue-100 text-blue-700' : 'bg-blue-50 text-blue-600' }}">
                                        {{ $stat['male_performance'] }}%
                                    </span>
                                    @else
                                    <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="text-center py-2 px-3">
                                    @if($stat['female_performance'] > 0)
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $stat['female_performance'] >= 50 ? 'bg-pink-100 text-pink-700' : 'bg-pink-50 text-pink-600' }}">
                                        {{ $stat['female_performance'] }}%
                                    </span>
                                    @else
                                    <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="text-center py-2 px-3">
                                    @if($stat['male_performance'] > 0 || $stat['female_performance'] > 0)
                                        @if($diff > 0)
                                        <span class="text-blue-600 font-semibold text-xs">♂ +{{ abs(round($diff, 1)) }}%</span>
                                        @elseif($diff < 0)
                                        <span class="text-pink-600 font-semibold text-xs">♀ +{{ abs(round($diff, 1)) }}%</span>
                                        @else
                                        <span class="text-gray-500 text-xs">Tie</span>
                                        @endif
                                    @else
                                    <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Overall Summary -->
                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-200">
                        <p class="text-xs font-medium text-blue-600">Male Leads</p>
                        <p class="text-xl font-bold text-blue-900">{{ $maleBetterCount }}</p>
                        <p class="text-xs text-blue-500">assessment types</p>
                    </div>
                    <div class="bg-pink-50 rounded-lg p-3 text-center border border-pink-200">
                        <p class="text-xs font-medium text-pink-600">Female Leads</p>
                        <p class="text-xl font-bold text-pink-900">{{ $femaleBetterCount }}</p>
                        <p class="text-xs text-pink-500">assessment types</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                        <p class="text-xs font-medium text-gray-600">Tied</p>
                        <p class="text-xl font-bold text-gray-900">{{ $tieCount }}</p>
                        <p class="text-xs text-gray-500">assessment types</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Subject Performance Chart with Pagination -->
@if(isset($subjectPerformanceData) && count($subjectPerformanceData) > 0)
<div class="w-full block mt-8" x-data="subjectPerformance()">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="mb-4 lg:mb-0">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Subject-Wise Performance</h3>
                <p class="text-sm text-gray-600">Average performance for each subject across all assessments</p>
                <p class="text-xs text-gray-500 mt-1">Showing <span x-text="visibleSubjects.length"></span> of {{ count($subjectPerformanceData) }} subjects</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input x-model="searchTerm" @input="filterSubjects()" type="text" placeholder="Search subjects..." class="block w-full sm:w-48 pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <select x-model="sortBy" @change="sortSubjects()" class="block w-full sm:w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="name">Sort by Name</option>
                    <option value="performance">Sort by Performance</option>
                    <option value="assessments">Sort by Assessments</option>
                </select>
                <select x-model="itemsPerPage" @change="updatePagination()" class="block w-full sm:w-28 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
        
        <!-- Chart for visible subjects -->
        <div x-show="visibleSubjects.length > 0" class="bg-gray-50 rounded-lg p-4 mb-6" style="height: 400px;">
            <canvas id="subjectPerformanceChart"></canvas>
        </div>
        
        <!-- No results message -->
        <div x-show="visibleSubjects.length === 0" class="text-center py-12">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500">No subjects found matching your search</p>
        </div>
        
        <!-- Subject Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
            <template x-for="subject in paginatedSubjects" :key="subject.subject">
                <div class="bg-gray-50 rounded-lg p-3 text-center border transition-all hover:shadow-md" :class="{
                    'border-green-200 hover:border-green-300': subject.performance >= 50,
                    'border-red-200 hover:border-red-300': subject.performance > 0 && subject.performance < 50,
                    'border-gray-200 hover:border-gray-300': subject.performance === 0
                }">
                    <p class="text-xs font-medium text-gray-600 truncate" :title="subject.subject" x-text="subject.subject.length > 12 ? subject.subject.substring(0, 12) + '...' : subject.subject"></p>
                    <p class="text-lg font-bold" :class="{
                        'text-green-600': subject.performance >= 50,
                        'text-red-600': subject.performance > 0 && subject.performance < 50,
                        'text-gray-400': subject.performance === 0
                    }" x-text="subject.performance > 0 ? subject.performance + '%' : '--'"></p>
                    <p class="text-xs text-gray-500" x-text="subject.assessments + ' assessments'"></p>
                </div>
            </template>
        </div>
        
        <!-- Pagination Controls -->
        <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
                Showing <span x-text="((currentPage - 1) * itemsPerPage) + 1"></span> to 
                <span x-text="Math.min(currentPage * itemsPerPage, visibleSubjects.length)"></span> of 
                <span x-text="visibleSubjects.length"></span> subjects
            </div>
            <div class="flex items-center space-x-2">
                <button @click="currentPage > 1 && changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Previous
                </button>
                <template x-for="page in pageNumbers" :key="page">
                    <button @click="changePage(page)" :class="{
                        'bg-blue-600 text-white border-blue-600': page === currentPage,
                        'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': page !== currentPage
                    }" class="px-3 py-2 text-sm border rounded-lg transition-colors" x-text="page"></button>
                </template>
                <button @click="currentPage < totalPages && changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Expandable Subject Cards with Class Filter -->
@if(isset($subjectAssessmentMatrix) && count($subjectAssessmentMatrix) > 0)
<div class="w-full block mt-8" x-data="{ selectedClass: 'all', showAll: false }">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Subject Performance Cards</h3>
                <p class="text-sm text-gray-600">Click on a subject to expand and view detailed assessment breakdown</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Class</label>
                <select x-model="selectedClass" class="block w-full sm:w-48 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($subjectAssessmentMatrix as $index => $subjectData)
            <div x-data="{ expanded: false }" 
                 x-show="(showAll || {{ $index }} < 6) && (selectedClass === 'all' || {{ json_encode($subjectData['class_ids'] ?? []) }}.includes(parseInt(selectedClass)))"
                 class="border rounded-xl overflow-hidden transition-all duration-300 {{ $subjectData['overall_performance'] >= 50 ? 'border-green-200' : ($subjectData['overall_performance'] > 0 ? 'border-red-200' : 'border-gray-200') }}">
                <div @click="expanded = !expanded" class="cursor-pointer p-4 {{ $subjectData['overall_performance'] >= 50 ? 'bg-green-50 hover:bg-green-100' : ($subjectData['overall_performance'] > 0 ? 'bg-red-50 hover:bg-red-100' : 'bg-gray-50 hover:bg-gray-100') }} transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $subjectData['overall_performance'] >= 50 ? 'bg-green-500' : ($subjectData['overall_performance'] > 0 ? 'bg-red-500' : 'bg-gray-400') }}">
                                <span class="text-white font-bold text-lg">{{ substr($subjectData['subject'], 0, 2) }}</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ $subjectData['subject'] }}</h4>
                                <p class="text-xs text-gray-500">Click to expand</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold {{ $subjectData['overall_performance'] >= 50 ? 'text-green-600' : ($subjectData['overall_performance'] > 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ $subjectData['overall_performance'] > 0 ? $subjectData['overall_performance'] . '%' : '--' }}
                            </p>
                            <p class="text-xs text-gray-500">Overall</p>
                        </div>
                    </div>
                    <div class="flex justify-center mt-2">
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div x-show="expanded" x-collapse class="bg-white border-t p-4">
                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Assessment Type Breakdown</h5>
                    <div class="space-y-2">
                        @foreach($assessmentTypes as $type)
                        @php $typeData = $subjectData['types'][$type] ?? ['given' => 0, 'performance' => 0]; @endphp
                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">{{ $type }}</span>
                                @if($typeData['given'] > 0)
                                <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">{{ $typeData['given'] }}</span>
                                @endif
                            </div>
                            <div>
                                @if($typeData['given'] > 0)
                                <div class="flex items-center space-x-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $typeData['performance'] >= 50 ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($typeData['performance'], 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $typeData['performance'] >= 50 ? 'text-green-600' : 'text-red-600' }}">{{ $typeData['performance'] }}%</span>
                                </div>
                                @else
                                <span class="text-sm text-gray-300">--</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if(count($subjectAssessmentMatrix) > 6)
        <div class="mt-6 text-center">
            <button @click="showAll = !showAll" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <span x-text="showAll ? 'Show Less' : 'Show More ({{ count($subjectAssessmentMatrix) - 6 }} more)'"></span>
                <svg class="w-4 h-4 ml-2 transition-transform duration-300" :class="{ 'rotate-180': showAll }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        @endif
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ... rest of the script remains the same ...
        const ctx = document.getElementById('genderResultsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Male', 'Female'],
                datasets: [
                    {
                        label: 'Pass',
                        data: [{{ $genderStats['malePass'] ?? 0 }}, {{ $genderStats['femalePass'] ?? 0 }}],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Fail',
                        data: [{{ $genderStats['maleFail'] ?? 0 }}, {{ $genderStats['femaleFail'] ?? 0 }}],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Pass vs Fail by Gender'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        // Classroom Population Chart with Male/Female breakdown (Line Graph)
        const classCtx = document.getElementById('classroomPopulationChart').getContext('2d');
        new Chart(classCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($classroomPopulation->pluck('name')) !!},
                datasets: [
                    {
                        label: 'Male',
                        data: {!! json_encode($classroomPopulation->pluck('male')) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    },
                    {
                        label: 'Female',
                        data: {!! json_encode($classroomPopulation->pluck('female')) !!},
                        backgroundColor: 'rgba(236, 72, 153, 0.2)',
                        borderColor: 'rgba(236, 72, 153, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(236, 72, 153, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Male vs Female Students per Class'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Assessment Performance Chart
        @if(isset($assessmentStats))
            let assessmentChart = null;
            const assessmentCtx = document.getElementById('assessmentPerformanceChart');
            const chartColors = [
                'rgba(59, 130, 246, 0.7)',
                'rgba(16, 185, 129, 0.7)',
                'rgba(245, 158, 11, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(139, 92, 246, 0.7)',
                'rgba(236, 72, 153, 0.7)',
                'rgba(20, 184, 166, 0.7)',
                'rgba(249, 115, 22, 0.7)',
                'rgba(99, 102, 241, 0.7)',
                'rgba(34, 197, 94, 0.7)'
            ];
            
            if (assessmentCtx) {
                assessmentChart = new Chart(assessmentCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(collect($assessmentStats)->pluck('type')) !!},
                        datasets: [{
                            label: 'Performance %',
                            data: {!! json_encode(collect($assessmentStats)->pluck('performance')) !!},
                            backgroundColor: chartColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: { display: true, text: 'Performance (%)' }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'School-Wide Assessment Performance'
                            }
                        }
                    }
                });
            }
            
            // Filter functionality for Assessment Performance
            const classFilter = document.getElementById('assessClassFilter');
            const subjectFilter = document.getElementById('assessSubjectFilter');
            const yearFilter = document.getElementById('assessYearFilter');
            const termFilter = document.getElementById('assessTermFilter');
            const summaryTable = document.querySelector('#assessmentPerformanceChart').closest('.grid').querySelector('tbody');
            
            function updateAssessmentStats() {
                const classId = classFilter.value;
                const subjectId = subjectFilter.value;
                const year = yearFilter.value;
                const term = termFilter.value;
                
                fetch(`/api/assessment-stats?class_id=${classId}&subject_id=${subjectId}&year=${year}&term=${term}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update chart
                            const performances = data.stats.map(s => s.performance);
                            assessmentChart.data.datasets[0].data = performances;
                            assessmentChart.update();
                            
                            // Update table
                            summaryTable.innerHTML = '';
                            data.stats.forEach(stat => {
                                const perfClass = stat.performance >= 50 ? 'bg-green-100 text-green-700' : (stat.performance > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500');
                                const perfText = stat.performance > 0 ? stat.performance + '%' : '--';
                                summaryTable.innerHTML += `
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-2 px-3 text-gray-700">${stat.type}</td>
                                        <td class="text-center py-2 px-3">${stat.given}</td>
                                        <td class="text-center py-2 px-3">
                                            <span class="px-2 py-1 rounded text-xs font-medium ${perfClass}">${perfText}</span>
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching assessment stats:', error));
            }
            
            if (classFilter && subjectFilter && yearFilter && termFilter) {
                classFilter.addEventListener('change', updateAssessmentStats);
                subjectFilter.addEventListener('change', updateAssessmentStats);
                yearFilter.addEventListener('change', updateAssessmentStats);
                termFilter.addEventListener('change', updateAssessmentStats);
            }
        @endif

        // Assessment Performance by Gender Chart
        @if(isset($assessmentStatsByGender))
            let genderChart = null;
            const genderCtx = document.getElementById('assessmentByGenderChart');
            const genderTable = document.querySelector('#assessmentByGenderChart').closest('.grid').querySelector('tbody');
            const genderSummaryCards = document.querySelector('#assessmentByGenderChart').closest('.grid').querySelectorAll('.grid-cols-3 > div');
            
            function createGenderChart(data) {
                const labels = data.map(s => s.type.length > 12 ? s.type.substring(0, 12) + '...' : s.type);
                const malePerf = data.map(s => s.male_performance);
                const femalePerf = data.map(s => s.female_performance);
                
                if (genderChart) {
                    genderChart.destroy();
                }
                
                genderChart = new Chart(genderCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Male',
                                data: malePerf,
                                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Female',
                                data: femalePerf,
                                backgroundColor: 'rgba(236, 72, 153, 0.7)',
                                borderColor: 'rgba(236, 72, 153, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 100,
                                title: { display: true, text: 'Performance (%)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: 'Assessment Performance: Male vs Female' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            function updateGenderTable(data) {
                let maleBetter = 0, femaleBetter = 0, tied = 0;
                let tableHtml = '';
                
                data.forEach(stat => {
                    const diff = stat.male_performance - stat.female_performance;
                    if (stat.male_performance > 0 || stat.female_performance > 0) {
                        if (diff > 0) maleBetter++;
                        else if (diff < 0) femaleBetter++;
                        else tied++;
                    }
                    
                    const maleClass = stat.male_performance >= 50 ? 'bg-blue-100 text-blue-700' : 'bg-blue-50 text-blue-600';
                    const femaleClass = stat.female_performance >= 50 ? 'bg-pink-100 text-pink-700' : 'bg-pink-50 text-pink-600';
                    const maleText = stat.male_performance > 0 ? `<span class="px-2 py-1 rounded text-xs font-medium ${maleClass}">${stat.male_performance}%</span>` : '<span class="text-gray-400">--</span>';
                    const femaleText = stat.female_performance > 0 ? `<span class="px-2 py-1 rounded text-xs font-medium ${femaleClass}">${stat.female_performance}%</span>` : '<span class="text-gray-400">--</span>';
                    
                    let betterText = '<span class="text-gray-400">--</span>';
                    if (stat.male_performance > 0 || stat.female_performance > 0) {
                        if (diff > 0) betterText = `<span class="text-blue-600 font-semibold text-xs">♂ +${Math.abs(diff.toFixed(1))}%</span>`;
                        else if (diff < 0) betterText = `<span class="text-pink-600 font-semibold text-xs">♀ +${Math.abs(diff.toFixed(1))}%</span>`;
                        else betterText = '<span class="text-gray-500 text-xs">Tie</span>';
                    }
                    
                    tableHtml += `<tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-2 px-3 text-gray-700 font-medium">${stat.type}</td>
                        <td class="text-center py-2 px-3">${maleText}</td>
                        <td class="text-center py-2 px-3">${femaleText}</td>
                        <td class="text-center py-2 px-3">${betterText}</td>
                    </tr>`;
                });
                
                if (genderTable) genderTable.innerHTML = tableHtml;
                
                // Update summary cards
                if (genderSummaryCards && genderSummaryCards.length >= 3) {
                    genderSummaryCards[0].querySelector('.text-xl').textContent = maleBetter;
                    genderSummaryCards[1].querySelector('.text-xl').textContent = femaleBetter;
                    genderSummaryCards[2].querySelector('.text-xl').textContent = tied;
                }
            }
            
            function fetchGenderStats() {
                const classId = document.getElementById('genderClassFilter').value;
                const subjectId = document.getElementById('genderSubjectFilter').value;
                const year = document.getElementById('genderYearFilter').value;
                const term = document.getElementById('genderTermFilter').value;
                
                fetch(`/api/assessment-stats-by-gender?class_id=${classId}&subject_id=${subjectId}&year=${year}&term=${term}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            createGenderChart(data.stats);
                            updateGenderTable(data.stats);
                        }
                    })
                    .catch(error => console.error('Error fetching gender stats:', error));
            }
            
            // Initialize chart with initial data
            if (genderCtx) {
                const initialGenderData = {!! json_encode($assessmentStatsByGender) !!};
                createGenderChart(initialGenderData);
                
                // Add filter event listeners
                document.getElementById('genderClassFilter').addEventListener('change', fetchGenderStats);
                document.getElementById('genderSubjectFilter').addEventListener('change', fetchGenderStats);
                document.getElementById('genderYearFilter').addEventListener('change', fetchGenderStats);
                document.getElementById('genderTermFilter').addEventListener('change', fetchGenderStats);
            }
        @endif

        // Subject Performance Chart with Pagination Support
        @if(isset($subjectPerformanceData) && count($subjectPerformanceData) > 0)
            let subjectChart = null;
            const subjectCtx = document.getElementById('subjectPerformanceChart');
            
            function createSubjectChart(subjects) {
                if (subjectChart) {
                    subjectChart.destroy();
                }
                
                if (subjectCtx && subjects.length > 0) {
                    const labels = subjects.map(s => s.subject.length > 15 ? s.subject.substring(0, 15) + '...' : s.subject);
                    const data = subjects.map(s => s.performance);
                    const backgroundColors = subjects.map(s => 
                        s.performance >= 50 ? 'rgba(34, 197, 94, 0.7)' : 
                        (s.performance > 0 ? 'rgba(239, 68, 68, 0.7)' : 'rgba(156, 163, 175, 0.7)')
                    );
                    const borderColors = subjects.map(s => 
                        s.performance >= 50 ? 'rgba(34, 197, 94, 1)' : 
                        (s.performance > 0 ? 'rgba(239, 68, 68, 1)' : 'rgba(156, 163, 175, 1)')
                    );
                    
                    const axisConfig = subjects.length > 10 ? 'y' : 'x';
                    const scalesConfig = {};
                    scalesConfig[subjects.length > 10 ? 'x' : 'y'] = {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Performance (%)' }
                    };
                    
                    subjectChart = new Chart(subjectCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Performance %',
                                data: data,
                                backgroundColor: backgroundColors,
                                borderColor: borderColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: subjects.length > 10 ? 'y' : 'x',
                            scales: scalesConfig,
                            plugins: {
                                legend: { display: false },
                                title: {
                                    display: true,
                                    text: 'Subject Performance (' + subjects.length + ' subjects)'
                                }
                            }
                        }
                    });
                }
            }
            
            // Alpine.js component for subject performance
            window.subjectPerformance = function() {
                return {
                    allSubjects: @json($subjectPerformanceData),
                    visibleSubjects: @json($subjectPerformanceData),
                    paginatedSubjects: [],
                    searchTerm: '',
                    sortBy: 'name',
                    itemsPerPage: 20,
                    currentPage: 1,
                    totalPages: 1,
                    pageNumbers: [],
                    
                    init() {
                        this.updatePagination();
                    },
                    
                    filterSubjects() {
                        this.visibleSubjects = this.allSubjects.filter(subject => 
                            subject.subject.toLowerCase().includes(this.searchTerm.toLowerCase())
                        );
                        this.sortSubjects();
                        this.currentPage = 1;
                        this.updatePagination();
                    },
                    
                    sortSubjects() {
                        this.visibleSubjects.sort((a, b) => {
                            switch(this.sortBy) {
                                case 'performance':
                                    return b.performance - a.performance;
                                case 'assessments':
                                    return b.assessments - a.assessments;
                                default:
                                    return a.subject.localeCompare(b.subject);
                            }
                        });
                        this.updatePagination();
                    },
                    
                    updatePagination() {
                        this.totalPages = Math.ceil(this.visibleSubjects.length / this.itemsPerPage);
                        this.currentPage = Math.min(this.currentPage, this.totalPages || 1);
                        
                        const start = (this.currentPage - 1) * this.itemsPerPage;
                        const end = start + parseInt(this.itemsPerPage);
                        this.paginatedSubjects = this.visibleSubjects.slice(start, end);
                        
                        this.updatePageNumbers();
                        this.updateChart();
                    },
                    
                    updatePageNumbers() {
                        const delta = 2;
                        const range = [];
                        const rangeWithDots = [];
                        
                        for (let i = Math.max(2, this.currentPage - delta); 
                             i <= Math.min(this.totalPages - 1, this.currentPage + delta); 
                             i++) {
                            range.push(i);
                        }
                        
                        if (this.currentPage - delta > 2) {
                            rangeWithDots.push(1, '...');
                        } else {
                            rangeWithDots.push(1);
                        }
                        
                        rangeWithDots.push(...range);
                        
                        if (this.currentPage + delta < this.totalPages - 1) {
                            rangeWithDots.push('...', this.totalPages);
                        } else if (this.totalPages > 1) {
                            rangeWithDots.push(this.totalPages);
                        }
                        
                        this.pageNumbers = rangeWithDots.filter((item, index, array) => array.indexOf(item) === index);
                    },
                    
                    changePage(page) {
                        if (page !== '...' && page >= 1 && page <= this.totalPages) {
                            this.currentPage = page;
                            this.updatePagination();
                        }
                    },
                    
                    updateChart() {
                        const self = this;
                        setTimeout(() => {
                            createSubjectChart(self.paginatedSubjects);
                        }, 100);
                    }
                };
            };
        @endif
    });
</script>