import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

// Create a Calendar wrapper that auto-includes plugins
class FullCalendarWrapper {
    static Calendar = class extends Calendar {
        constructor(el, options) {
            super(el, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                ...options
            });
        }
    };
}

window.FullCalendar = FullCalendarWrapper;