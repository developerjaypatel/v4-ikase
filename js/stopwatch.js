var	clsStopwatch = function() {
		// Private vars
		var	startAt	= 0;	// Time of last start / resume. (0 if not running)
		var	lapTime	= 0;	// Time on the clock when last stopped in milliseconds

		var	now	= function() {
				return (new Date()).getTime(); 
			}; 
 
		// Public methods
		// Start or resume
		this.startTimer = function() {
				startAt	= startAt ? startAt : now();
			};

		// Stop or pause
		this.stopTimer = function() {
				// If running, update elapsed time otherwise keep it
				lapTime	= startAt ? lapTime + now() - startAt : lapTime;
				startAt	= 0; // Paused
			};

		// Reset
		this.resetTimer = function() {
				lapTime = startAt = 0;
			};

		// Duration
		this.timeTimer = function() {
				return lapTime + (startAt ? now() - startAt : 0); 
			};
	};

var x = new clsStopwatch();
var $time;
var clocktimer;

function pad(num, size) {
	var s = "0000" + num;
	return s.substr(s.length - size);
}

function formatTime(time) {
	var h = m = s = ms = 0;
	var newTime = '';

	h = Math.floor( time / (60 * 60 * 1000) );
	time = time % (60 * 60 * 1000);
	m = Math.floor( time / (60 * 1000) );
	time = time % (60 * 1000);
	s = Math.floor( time / 1000 );
	ms = time % 1000;

	newTime = pad(h, 2) + ':' + pad(m, 2) + ':' + pad(s, 2);
	return newTime;
}

function showTheWatch() {
	$time = document.getElementById('time_timer_span');
	updateTheWatch();
}

function updateTheWatch() {
	$time.innerHTML = formatTime(x.timeTimer());
}

function startTheWatch() {
	clocktimer = setInterval("updateTheWatch()", 1);
	x.startTimer();
}

function stopTheWatch() {
	x.stopTimer();
	clearInterval(clocktimer);
}

function resetTheWatch() {
	stopTheWatch();
	x.resetTimer();
	updateTheWatch();
}