<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>TOAST UI Calendar Example - Monthly View Basic</title>
    <link rel="stylesheet" href="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.css" />
    <link rel="stylesheet" href="assets/styles/reset.css" />
    <link rel="stylesheet" href="assets/styles/app.css" />
    <link rel="stylesheet" href="assets/styles/icons.css" />
    <style>
      .navbar {
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div class="app-container code-html">
      <header class="header">
        <nav class="navbar">
          <button class="button is-rounded today">Today</button>
          <button class="button is-rounded prev">
            <img
              alt="prev"
              src="./images/ic-arrow-line-left.png"
              srcset="./images/ic-arrow-line-left@2x.png 2x, ./images/ic-arrow-line-left@3x.png 3x"
            />
          </button>
          <button class="button is-rounded next">
            <img
              alt="prev"
              src="./images/ic-arrow-line-right.png"
              srcset="
                ./images/ic-arrow-line-right@2x.png 2x,
                ./images/ic-arrow-line-right@3x.png 3x
              "
            />
          </button>
          <span class="navbar--range"></span>
        </nav>
      </header>
      <main id="app"></main>
    </div>
    
    <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.min.js"></script>
    <script src="https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.js"></script>
    <script src="https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.3/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chance/1.1.8/chance.min.js"></script>
    <script src="https://uicdn.toast.com/calendar/latest/toastui-calendar.ie11.min.js"></script>
    <script src="assets/scripts/mock-data.js"></script>
    <script src="assets/scripts/utils.js"></script>

    <script type="text/javascript" class="code-js">
      var Calendar = window.tui.Calendar;

      var cal = new Calendar('#app', {
        defaultView: 'month',
        calendars: MOCK_CALENDARS,
        useFormPopup: true,
        useDetailPopup: true,
      });
    
      var todayButton = $('.today');
      var prevButton = $('.prev');
      var nextButton = $('.next');
      var range = $('.navbar--range');

      function displayEvents() {
        var events = generateRandomEvents(
          cal.getViewName(),
          cal.getDateRangeStart(),
          cal.getDateRangeEnd()
        );
        cal.clear();
        cal.createEvents(events);
      }

      function displayRenderRange() {
        range.textContent = getNavbarRange(cal.getDateRangeStart(), cal.getDateRangeEnd(), 'month');
      }

      todayButton.addEventListener('click', function () {
        cal.today();
        displayEvents();
        displayRenderRange();
      });
      prevButton.addEventListener('click', function () {
        cal.prev();
        displayEvents();
        displayRenderRange();
      });
      nextButton.addEventListener('click', function () {
        cal.next();
        displayEvents();
        displayRenderRange();
      });

      function bindInstanceEvents() {
    cal.on({
      clickMoreEventsBtn: function (btnInfo) {
        console.log('clickMoreEventsBtn', btnInfo);
      },
      clickEvent: function (eventInfo) {
        console.log('clickEvent', eventInfo);
      },
      clickDayName: function (dayNameInfo) {
        console.log('clickDayName', dayNameInfo);
      },
      selectDateTime: function (dateTimeInfo) {
        console.log('selectDateTime', dateTimeInfo);
      },
      beforeCreateEvent: function (event) {
        console.log('beforeCreateEvent', event);
        event.id = chance.guid();

        cal.createEvents([event]);
        cal.clearGridSelections();
      },
      beforeUpdateEvent: function (eventInfo) {
        var event, changes;

        console.log('beforeUpdateEvent', eventInfo);

        event = eventInfo.event;
        changes = eventInfo.changes;

        cal.updateEvent(event.id, event.calendarId, changes);
      },
      beforeDeleteEvent: function (eventInfo) {
        console.log('beforeDeleteEvent', eventInfo);

        cal.deleteEvent(eventInfo.id, eventInfo.calendarId);
      },
    });
  }
  bindInstanceEvents();
      displayEvents();
      displayRenderRange();
    </script>
  </body>
</html>
