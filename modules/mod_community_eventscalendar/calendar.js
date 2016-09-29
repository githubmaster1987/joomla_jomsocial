(function ($) {

    var eCalendar = function (options, object) {
        // Initializing global variables
        var adDay = new Date().getDate();
        var adMonth = new Date().getMonth();
        var adYear = new Date().getFullYear();
        var dDay = adDay;
        var dMonth = adMonth;
        var dYear = adYear;
        var instance = object;

        var settings = $.extend({}, $.fn.eCalendar.defaults, options);

        function lpad(value, length, pad) {
            if (typeof pad == 'undefined') {
                pad = '0';
            }
            var p;
            for (var i = 0; i < length; i++) {
                p += pad;
            }
            return (p + value).slice(-length);
        }

        var mouseOver = function () {
            $(this).addClass('c-nav-btn-over');
        };
        var mouseLeave = function () {
            $(this).removeClass('c-nav-btn-over');
        };
        var mouseOverEvent = function () {
            $(this).addClass('joms-calendar__event-over');
            var d = $(this).attr('data-event-day');
            $('div.joms-calendar__event-item[data-event-day="' + d + '"]').addClass('joms-calendar__event-over');
        };
        var mouseLeaveEvent = function () {
            $(this).removeClass('joms-calendar__event-over')
            var d = $(this).attr('data-event-day');
            $('div.joms-calendar__event-item[data-event-day="' + d + '"]').removeClass('joms-calendar__event-over');
        };
        var mouseOverItem = function () {
            $(this).addClass('joms-calendar__event-over');
            var d = $(this).attr('data-event-day');
            $('div.joms-calendar__event[data-event-day="' + d + '"]').addClass('joms-calendar__event-over');
        };
        var mouseLeaveItem = function () {
            $(this).removeClass('joms-calendar__event-over')
            var d = $(this).attr('data-event-day');
            $('div.joms-calendar__event[data-event-day="' + d + '"]').removeClass('joms-calendar__event-over');
        };
        var nextMonth = function () {
            if (dMonth < 11) {
                dMonth++;
            } else {
                dMonth = 0;
                dYear++;
            }
            print();
        };
        var previousMonth = function () {
            if (dMonth > 0) {
                dMonth--;
            } else {
                dMonth = 11;
                dYear--;
            }
            print();
        };

        function loadEvents() {
            if (typeof settings.url != 'undefined' && settings.url != '') {
                $.ajax({url: settings.url,
                    async: false,
                    success: function (result) {
                        settings.events = result;
                    }
                });
            }
        }

        function print() {
            loadEvents();
            var dWeekDayOfMonthStart = new Date(dYear, dMonth, 1).getDay() - settings.firstDay;
            dWeekDayOfMonthStart = dWeekDayOfMonthStart < 0 ? 7 + dWeekDayOfMonthStart : dWeekDayOfMonthStart;
            var dLastDayOfMonth = new Date(dYear, dMonth + 1, 0).getDate();
            var dLastDayOfPreviousMonth = new Date(dYear, dMonth + 1, 0).getDate() - dWeekDayOfMonthStart + 1;

            var cBody = $('<div/>').addClass('joms-calendar__grid clearfix');
            var cEvents = $('<div/>').addClass('joms-event__grid');
            var cEventsBody = $('<div/>').addClass('joms-event__body');
            var cTitle = $('<div/>').hide().addClass('joms-calendar__event-title joms-calendar__pad').html(settings.eventTitle);
            cEvents.append(cTitle);
            cEvents.append(cEventsBody);
            var cNext = $('<div/>').addClass('joms-calendar--next joms-calendar__grid-title joms-calendar__pad');
            var cMonth = $('<div/>').addClass('joms-calendar--month joms-calendar__grid-title joms-calendar__pad');
            var cPrevious = $('<div/>').addClass('joms-calendar--prev joms-calendar__grid-title joms-calendar__pad');
            cPrevious.html(settings.textArrows.previous);
            cMonth.html(settings.months[dMonth] + ' ' + dYear);
            cNext.html(settings.textArrows.next);

            cPrevious.on('mouseover', mouseOver).on('mouseleave', mouseLeave).on('click', previousMonth);
            cNext.on('mouseover', mouseOver).on('mouseleave', mouseLeave).on('click', nextMonth);

            cBody.append(cPrevious);
            cBody.append(cMonth);
            cBody.append(cNext);
            for (var i = 0; i < settings.weekDays.length; i++) {
                var cWeekDay = $('<div/>').addClass('joms-calendar--week-day joms-calendar__pad');
                cWeekDay.html(settings.weekDays[i]);
                cBody.append(cWeekDay);
            }
            var day = 1;
            var dayOfNextMonth = 1;
            for (var i = 0; i < 42; i++) {
                var cDay = $('<div/>');
                if (i < dWeekDayOfMonthStart) {
                    cDay.addClass('joms-calendar--day-previous-month joms-calendar__pad');
                    cDay.html(dLastDayOfPreviousMonth++);
                } else if (day <= dLastDayOfMonth) {
                    cDay.addClass('joms-calendar--day joms-calendar__pad');
                    if (day == dDay && adMonth == dMonth && adYear == dYear) {
                        cDay.addClass('joms-calendar--today');
                    }
                    for (var j = 0; j < settings.events.length; j++) {
                        var d = settings.events[j].datetime;
                        if (d.getDate() == day && d.getMonth() == dMonth && d.getFullYear() == dYear) {
                            cDay.addClass('joms-calendar__event').attr('data-event-day', d.getDate());
                            cDay.on('mouseover', mouseOverEvent).on('mouseleave', mouseLeaveEvent);
                        }
                    }
                    cDay.html(day++);
                } else {
                    cDay.addClass('joms-calendar--day-next-month joms-calendar__pad');
                    cDay.html(dayOfNextMonth++);
                }
                cBody.append(cDay);
            }
            var eventList = $('<div/>').addClass('joms-calendar__event-list');
            for (var i = 0; i < settings.events.length; i++) {
                var d = settings.events[i].datetime;
                var ampm = settings.events[i].showAmpm;
                if (d.getMonth() == dMonth && d.getFullYear() == dYear) {
                    var item = $('<div/>').addClass('joms-calendar__event-item');
                    var title = $('<h5/>').addClass('title').html(settings.events[i].title);
                    var description = $('<p/>').addClass('description').html(settings.events[i].description);
                    var datetime = $('<small/>').html(settings.events[i].datelabel);
                    item.attr('data-event-day', d.getDate());
                    item.attr('onclick', 'window.location=\'' + settings.events[i].url + '\'');
                    item.css('cursor', 'pointer');
                    item.on('mouseover', mouseOverItem).on('mouseleave', mouseLeaveItem);
                    item.append(title).append(description).append(datetime);
                    eventList.append(item);
                    if (cTitle) {
                        cTitle.show();
                        cTitle = false;
                    }
                }
            }
            $(instance).addClass('joms-calendar');
            cEventsBody.append(eventList);
            $(instance).html(cBody).append(cEvents);
        }

        // adjust first day
        if ( settings.firstDay > 0 ) {
            var tmp = settings.weekDays.splice(0, settings.firstDay);
            settings.weekDays = settings.weekDays.concat(tmp);
        }

        return print();
    }

    $.fn.eCalendar = function (oInit) {
        return this.each(function () {
            return eCalendar(oInit, $(this));
        });
    };

    // plugin defaults
    $.fn.eCalendar.defaults = {
        firstDay: 0,
        weekDays: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
        months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        textArrows: {previous: '◀', next: '▶'},
        eventTitle: 'Eventos',
        url: '',
        events: [
            {title: 'Brasil x Croácia', description: 'Abertura da copa do mundo 2014', datetime: new Date(2014, 6, 12, 17)},
            {title: 'Brasil x México', description: 'Segundo jogo da seleção brasileira', datetime: new Date(2014, 6, 17, 16)},
            {title: 'Brasil x Camarões', description: 'Terceiro jogo da seleção brasileira', datetime: new Date(2014, 6, 23, 16)}
        ]
    };

}(jQuery));
