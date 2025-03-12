document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Default view
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,multiMonthYear'
        },
        views: {
            multiMonthYear: {
                type: 'multiMonthYear',
                duration: { months: 6 } // Show 6 months at a time
            }
        },
        events: [
            {
                title: 'Sample Event',
                start: '2025-02-15'
            }
        ]
    });

    calendar.render();

    // Toggle between single month and multi-month view
    document.getElementById('toggleMonthView').addEventListener('click', function() {
        if (calendar.view.type === 'dayGridMonth') {
            calendar.changeView('multiMonthYear'); // Switch to multi-month view
        } else {
            calendar.changeView('dayGridMonth'); // Switch back to single month
        }
    });
});
