@if(session('timetable_warnings'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong>⚠ Warnings</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('timetable_warnings') as $w)
        <li>{{ $w['message'] }}
            @if(!empty($w['suggestion']))
                <br><small class="text-muted">💡 {{ $w['suggestion'] }}</small>
            @endif
        </li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if(session('timetable_conflicts'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>🔴 Teacher Conflicts</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('timetable_conflicts') as $c)
        <li>
            <strong>{{ $c['teacher'] }}</strong> — 
            {{ $c['day'] }} at {{ $c['time'] }} — 
            {{ implode(', ', $c['classes']) }}
        </li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if(session('timetable_failures'))
<div class="alert alert-warning alert-dismissible fade show"
     style="border-left:4px solid orange;" role="alert">
    <strong>🟠 Unplaced Lessons</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('timetable_failures') as $f)
        <li>
            <strong>{{ $f['subject'] }}</strong>: {{ $f['reason'] }}
            @if(!empty($f['suggestion']))
                <br><small class="text-muted">💡 {{ $f['suggestion'] }}</small>
            @endif
        </li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif
