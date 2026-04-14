jQuery(document).ready(function($){

var calendarEl = document.getElementById('training-calendar');

var calendar = new FullCalendar.Calendar(calendarEl, {

initialView: 'dayGridMonth',

/* HEADER CONTROLS */
headerToolbar: {
left: 'prev,next today',
center: 'title',
right: 'dayGridMonth,timeGridWeek,timeGridDay'
},

buttonText:{
today:'Today',
month:'Month',
week:'Week',
day:'Day'
},

height:"auto",

/* FETCH EVENTS */
events: function(fetchInfo, successCallback){

    $.ajax({
        url: training_ajax.ajaxurl,
        type:"POST",
        data:{
            action:"get_training_sessions"
        },
        success:function(data){

            var enhancedEvents = [];

            var currentView = calendar.view.type;

            data.forEach(function(event){

                // ORIGINAL event (timed)
                enhancedEvents.push(event);

                // ADD duplicate ONLY for Week/Day views
                if(currentView === 'timeGridWeek' || currentView === 'timeGridDay'){

                    enhancedEvents.push({
                        title: event.title,
                        start: event.start.split("T")[0], // only date
                        allDay: true,
                        display: 'block',
                        extendedProps: event.extendedProps
                    });

                }

            });

            successCallback(enhancedEvents);

        }
    });

},

/* REFRESH EVENTS WHEN VIEW CHANGES */
datesSet: function(){
    calendar.refetchEvents();
},

/* CUSTOM EVENT DESIGN */
eventContent:function(arg){

    var title = arg.event.title || '';
    var time  = arg.event.extendedProps.time || '';
    var location = arg.event.extendedProps.location || '';

    var html = `
    <div class="training-event">

        <div class="event-time">
            ${time}
        </div>

        <div class="event-title">
            ${title}
        </div>

        ${location ? `<div class="event-location">📍 ${location}</div>` : ''}

    </div>
    `;

    return { html: html };

},

/* CLICK EVENT */
eventClick:function(info){

    info.jsEvent.preventDefault();

    // ignore all-day duplicate clicks
    if(info.event.allDay){
        return;
    }

    if(info.event.url){
        window.location.href = info.event.url;
    }

}

});

calendar.render();

});