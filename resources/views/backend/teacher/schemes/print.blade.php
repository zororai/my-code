<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $scheme->title }} - Scheme of Work</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .school-logo {
            max-width: 100px;
            max-height: 100px;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 10px 0;
        }

        .school-address {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 15px 0;
            text-transform: uppercase;
        }

        .scheme-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .info-item {
            display: flex;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #4b5563;
            min-width: 140px;
        }

        .info-value {
            color: #1f2937;
        }

        .topics-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        .topics-table th {
            background: #2563eb;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #1e40af;
        }

        .topics-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .topics-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .topic-number {
            font-weight: bold;
            color: #2563eb;
            text-align: center;
        }

        .topic-name {
            font-weight: 600;
            color: #1f2937;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }

        .signature-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin-top: 30px;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin-top: 50px;
            padding-top: 8px;
            font-weight: 600;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .print-button:hover {
            background: #1e40af;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">
        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Document
    </button>

    <!-- Header -->
    <div class="header">
        @if($schoolLogo)
            <img src="{{ asset('storage/' . $schoolLogo) }}" alt="School Logo" class="school-logo">
        @endif
        <div class="school-name">{{ $schoolName }}</div>
        @if($schoolAddress)
            <div class="school-address">{{ $schoolAddress }}</div>
        @endif
        <div class="document-title">Scheme of Work</div>
    </div>

    <!-- Scheme Information -->
    <div class="scheme-info">
        <h2 style="margin: 0 0 15px 0; color: #1e3a8a; font-size: 18px;">{{ $scheme->title }}</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Subject:</span>
                <span class="info-value">{{ $scheme->subject->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ $scheme->class->class_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Term:</span>
                <span class="info-value">{{ $scheme->term }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Academic Year:</span>
                <span class="info-value">{{ $scheme->academic_year }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Teacher:</span>
                <span class="info-value">{{ $scheme->teacher->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="status-badge status-{{ $scheme->status }}">{{ ucfirst($scheme->status) }}</span>
            </div>
            @if($scheme->start_date)
            <div class="info-item">
                <span class="info-label">Start Date:</span>
                <span class="info-value">{{ $scheme->start_date->format('d M Y') }}</span>
            </div>
            @endif
            @if($scheme->end_date)
            <div class="info-item">
                <span class="info-label">End Date:</span>
                <span class="info-value">{{ $scheme->end_date->format('d M Y') }}</span>
            </div>
            @endif
        </div>

        @if($scheme->description)
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #d1d5db;">
            <span class="info-label">Description:</span>
            <p style="margin: 5px 0 0 0; color: #4b5563;">{{ $scheme->description }}</p>
        </div>
        @endif
    </div>

    <!-- Topics Table -->
    <h3 style="color: #1e3a8a; margin-bottom: 10px;">Syllabus Topics Covered</h3>
    <table class="topics-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th style="width: 30%;">Topic Name</th>
                <th style="width: 60px;">Week</th>
                <th style="width: 70px;">Periods</th>
                <th style="width: 80px;">Expected %</th>
                <th>Teaching Methods</th>
                <th>Resources</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scheme->schemeTopics as $index => $schemeTopic)
            <tr>
                <td class="topic-number">{{ $index + 1 }}</td>
                <td class="topic-name">
                    {{ $schemeTopic->syllabusTopic->name ?? 'N/A' }}
                    @if($schemeTopic->syllabusTopic->topic_code)
                        <br><small style="color: #6b7280;">({{ $schemeTopic->syllabusTopic->topic_code }})</small>
                    @endif
                </td>
                <td style="text-align: center;">{{ $schemeTopic->week_number ?? '-' }}</td>
                <td style="text-align: center;">{{ $schemeTopic->planned_periods }}</td>
                <td style="text-align: center;">{{ $schemeTopic->expected_performance ? number_format($schemeTopic->expected_performance, 1) . '%' : '-' }}</td>
                <td>{{ $schemeTopic->teaching_methods ?: '-' }}</td>
                <td>{{ $schemeTopic->resources ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">No topics added to this scheme</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div style="margin-top: 25px; background: #f9fafb; padding: 15px; border-radius: 8px;">
        <h4 style="margin: 0 0 10px 0; color: #1e3a8a;">Summary</h4>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 12px;">
            <div>
                <strong>Total Topics:</strong> {{ $scheme->schemeTopics->count() }}
            </div>
            <div>
                <strong>Total Periods:</strong> {{ $scheme->schemeTopics->sum('planned_periods') }}
            </div>
            @if($scheme->expected_performance)
            <div>
                <strong>Target Performance:</strong> {{ number_format($scheme->expected_performance, 1) }}%
            </div>
            @endif
        </div>
    </div>

    <!-- Footer with Signatures -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line">Teacher's Signature</div>
                <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">{{ $scheme->teacher->user->name ?? '' }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">Head of Department</div>
                <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">Date: _______________</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px; font-size: 11px; color: #9ca3af;">
            Generated on {{ now()->format('d F Y, H:i') }}
        </div>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
