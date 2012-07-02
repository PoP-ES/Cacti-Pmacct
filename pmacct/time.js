function get_timespan(timespan_given) {
	var now = new Date();
	var timeshift = 1000;
	var end;
	
	switch (timespan_given){
		case "1":
			timeshift *= 60 * 30;
			end = new Date(now - timeshift);
			break;
		case "2":
			timeshift *= 60 * 60;
			end = new Date(now - timeshift);
			break;
		case "3":
			timeshift *= 60 * 60 * 2;
			end = new Date(now - timeshift);
			break;
		case "4":
			timeshift *= 60 * 60 * 4;
			end = new Date(now - timeshift);
			break;
		case "5":
			timeshift *= 60 * 60 * 6;
			end = new Date(now - timeshift);
			break;
		case "6":
			timeshift *= 60 * 60 * 12;
			end = new Date(now - timeshift);
			break;
		case "7":
			timeshift *= 60 * 60 * 24;
			end = new Date(now - timeshift);
			break;
		case "8":
			timeshift *= 60 * 60 * 24 * 2;
			end = new Date(now - timeshift);
			break;
		case "9":
			timeshift *= 60 * 60 * 24 * 3;
			end = new Date(now - timeshift);
			break;
		case "10":
			timeshift *= 60 * 60 * 24 * 4;
			end = new Date(now - timeshift);
			break;
		case "11":
			timeshift *= 60 * 60 * 24 * 7;
			end = new Date(now - timeshift);
			break;
		case "12":
			timeshift *= 60 * 60 * 24 * 7 * 2;
			end = new Date(now - timeshift);
			break;
		default:
			timeshift *= 60 * 60 * 24;
			end = new Date(now - timeshift);
			break;
	}
	var hour = end.getHours();
	if (hour < 10)
		hour = '0'+hour;
	var minute = end.getMinutes();
	if (minute < 10)
		minute = '0'+minute;
	var monthnumber = end.getMonth() + 1;
	if (monthnumber < 10)
		monthnumber = '0'+monthnumber;
	var monthday = end.getDate();
	if (monthday < 10)
		monthday = '0'+monthday;
	var year = end.getFullYear();
	return (year+'-'+monthnumber+'-'+monthday+' '+hour+':'+minute);
}


function add_timeshift(timespan_given,php_date) {
	var amd = php_date.split(" ");
	var hm = amd[1].split(':');
	amd = amd[0].split('-');
	var now = new Date(amd[0],amd[1]-1,amd[2],hm[0],hm[1]);
	var hour = now.getHours();
	var minute = now.getMinutes();
	var monthnumber = now.getMonth();
	var monthday = now.getDate();
	var year = now.getFullYear();
	
	var end;
	
	switch (timespan_given){
		case "1":
			end = new Date(year,monthnumber,monthday,hour,minute+30);
			break;
		case "2":
			end = new Date(year,monthnumber,monthday,hour+1,minute);
			break;
		case "3":
			end = new Date(year,monthnumber,monthday,hour+12,minute);
			break;
		case "4":
			end = new Date(year,monthnumber,monthday+1,hour,minute);
			break;
		case "5":
			end = new Date(year,monthnumber,monthday+7,hour,minute);
			break;
		case "6":
			end = new Date(year,monthnumber+1,monthday,hour,minute);
			break;
		case "7":
			end = new Date(year,monthnumber+6,monthday,hour,minute);
			break;
		case "8":
			end = new Date(year,monthnumber+12,monthday,hour,minute);
			break;
		default:
			end = new Date(now);
			break;
	}
	var hour = end.getHours();
	if (hour < 10)
		hour = '0'+hour;
	var minute = end.getMinutes();
	if (minute < 10)
		minute = '0'+minute;
	var monthnumber = end.getMonth() ;
	monthnumber = monthnumber + 1;
	if (monthnumber < 10)
		monthnumber = '0'+monthnumber;
	var monthday = end.getDate();
	if (monthday < 10)
		monthday = '0'+monthday;
	var year = end.getFullYear();
	return (year+'-'+monthnumber+'-'+monthday+' '+hour+':'+minute);
}

function del_timeshift(timespan_given,php_date) {
	var amd = php_date.split(" ");
	var hm = amd[1].split(':');
	amd = amd[0].split('-');
	var now = new Date(amd[0],amd[1]-1,amd[2],hm[0],hm[1]);
	var hour = now.getHours();
	var minute = now.getMinutes();
	var monthnumber = now.getMonth();
	var monthday = now.getDate();
	var year = now.getFullYear();
	
	var end;
	
	switch (timespan_given){
		case "1":
			end = new Date(year,monthnumber,monthday,hour,minute-30);
			break;
		case "2":
			end = new Date(year,monthnumber,monthday,hour-1,minute);
			break;
		case "3":
			end = new Date(year,monthnumber,monthday,hour-12,minute);
			break;
		case "4":
			end = new Date(year,monthnumber,monthday-1,hour,minute);
			break;
		case "5":
			end = new Date(year,monthnumber,monthday-7,hour,minute);
			break;
		case "6":
			end = new Date(year,monthnumber-1,monthday,hour,minute);
			break;
		case "7":
			end = new Date(year,monthnumber-6,monthday,hour,minute);
			break;
		case "8":
			end = new Date(year,monthnumber-12,monthday,hour,minute);
			break;
		default:
			end = new Date(now);
			break;
	}
	var hour = end.getHours();
	if (hour < 10)
		hour = '0'+hour;
	var minute = end.getMinutes();
	if (minute < 10)
		minute = '0'+minute;
	var monthnumber = end.getMonth();
	monthnumber = monthnumber + 1;
	if (monthnumber < 10)
		monthnumber = '0'+monthnumber;
	var monthday = end.getDate();
	if (monthday < 10)
		monthday = '0'+monthday;
	var year = end.getFullYear();
	return (year+'-'+monthnumber+'-'+monthday+' '+hour+':'+minute);
}
// Simulates PHP's date function
Date.prototype.format = function(format) {
	var returnStr = '';
	var replace = Date.replaceChars;
	for (var i = 0; i < format.length; i++) {
		var curChar = format.charAt(i);
		if (replace[curChar]) {
			returnStr += replace[curChar].call(this);
		} else {
			returnStr += curChar;
		}
	}
	return returnStr;
};
Date.replaceChars = {
	shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
	longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
	
	// Day
	d: function() { return (this.getDate() < 10 ? '0' : '') + this.getDate(); },
	D: function() { return Date.replaceChars.shortDays[this.getDay()]; },
	j: function() { return this.getDate(); },
	l: function() { return Date.replaceChars.longDays[this.getDay()]; },
	N: function() { return this.getDay() + 1; },
	S: function() { return (this.getDate() % 10 == 1 && this.getDate() != 11 ? 'st' : (this.getDate() % 10 == 2 && this.getDate() != 12 ? 'nd' : (this.getDate() % 10 == 3 && this.getDate() != 13 ? 'rd' : 'th'))); },
	w: function() { return this.getDay(); },
	z: function() { return "Not Yet Supported"; },
	// Week
	W: function() { return "Not Yet Supported"; },
	// Month
	F: function() { return Date.replaceChars.longMonths[this.getMonth()]; },
	m: function() { return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1); },
	M: function() { return Date.replaceChars.shortMonths[this.getMonth()]; },
	n: function() { return this.getMonth() + 1; },
	t: function() { return "Not Yet Supported"; },
	// Year
	L: function() { return (((this.getFullYear()%4==0)&&(this.getFullYear()%100 != 0)) || (this.getFullYear()%400==0)) ? '1' : '0'; },
	o: function() { return "Not Supported"; },
	Y: function() { return this.getFullYear(); },
	y: function() { return ('' + this.getFullYear()).substr(2); },
	// Time
	a: function() { return this.getHours() < 12 ? 'am' : 'pm'; },
	A: function() { return this.getHours() < 12 ? 'AM' : 'PM'; },
	B: function() { return "Not Yet Supported"; },
	g: function() { return this.getHours() % 12 || 12; },
	G: function() { return this.getHours(); },
	h: function() { return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12); },
	H: function() { return (this.getHours() < 10 ? '0' : '') + this.getHours(); },
	i: function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
	s: function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
	// Timezone
	e: function() { return "Not Yet Supported"; },
	I: function() { return "Not Supported"; },
	O: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + '00'; },
	P: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + ':' + (Math.abs(this.getTimezoneOffset() % 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() % 60)); },
	T: function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
	Z: function() { return -this.getTimezoneOffset() * 60; },
	// Full Date/Time
	c: function() { return this.format("Y-m-d") + "T" + this.format("H:i:sP"); },
	r: function() { return this.toString(); },
	U: function() { return this.getTime() / 1000; }
};
