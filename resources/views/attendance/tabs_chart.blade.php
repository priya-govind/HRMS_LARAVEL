@extends('layouts.app')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<ul class="nav nav-tabs" id="teamTypeTabs">
    @foreach($teamTypes as $index => $type)
        <li class="nav-item">
            <a class="nav-link team-tab {{ $index === 0 ? 'active' : '' }}" data-id="{{ $type->id }}"
               style="background: {{ $type->team_color }}; font-weight: bold; color:black;">
                {{ $type->team_typ_name }}
            </a>
        </li>
    @endforeach
</ul>

<canvas id="AttendanceChart" width="400" height="200" class="mt-3"></canvas>

<script>
   $(document).ready(function () {
    const attendanceData = @json($attendance);
    const teamCounts = @json($teamCounts);
    const dates = @json($dates);
    const ctx = document.getElementById('AttendanceChart').getContext('2d');
    let chart;

    function renderChart(teamId) {
        const data = attendanceData[teamId] || [];
        const total = teamCounts[teamId] || 0;

        const present = dates.map(date => {
            const entry = data.find(d => d.date === date);
            return entry ? entry.present_count : 0;
        });

        const absent = present.map(p => total - p);

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'bar', // 🔄 Changed from 'line' to 'bar'
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Present',
                        data: present,
                        backgroundColor: 'rgba(0,128,0,0.6)',
                        borderColor: 'green',
                        borderWidth: 1
                    },
                    {
                        label: 'Absent',
                        data: absent,
                        backgroundColor: 'rgba(255,0,0,0.6)',
                        borderColor: 'red',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Attendance (Last 7 Days)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Tab click handler
    $('.team-tab').on('click', function (e) {
        e.preventDefault();
        $('.team-tab').removeClass('active');
        $(this).addClass('active');

        const teamId = $(this).data('id');
        renderChart(teamId);
    });

    // ✅ Initial render using first tab's data-id
    const firstTabId = $('.team-tab').first().data('id');
    renderChart(firstTabId);
});
</script>
@endsection