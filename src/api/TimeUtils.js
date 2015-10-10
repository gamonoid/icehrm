/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

function TimeUtils() {
	
}

TimeUtils.method('setServerGMToffset' , function(serverGMToffset) {
	this.serverGMToffset = serverGMToffset;
});

TimeUtils.method('convertToRelativeTime',function(selector) {
	
	var that = this;
	
	var getAmPmTime = function(curHour, curMin) {
	    var amPm = "am";
	    var amPmHour = curHour;
    	if (amPmHour >= 12) {
      		amPm = "pm";
      		if (amPmHour > 12) {
       			amPmHour = amPmHour - 12;
      		}
     	}
    	var prefixCurMin = "";
    	if (curMin < 10) {
    		prefixCurMin = "0";
    	}
    	
    	var prefixCurHour = "";
    	if (curHour == 0) {
    		prefixCurHour = "0";
    	}
     	return " at " + prefixCurHour + amPmHour + ":" + prefixCurMin + curMin + amPm;
    };

	var getBrowserTimeZone = function() {
        var current_date = new Date();
        var gmt_offset = current_date.getTimezoneOffset() / 60;
        return -gmt_offset;
	};
    
   	var curDate = new Date();
   	var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
   
	
    var timezoneDiff = this.serverGMToffset - getBrowserTimeZone();
    var timezoneTimeDiff = timezoneDiff*60*60*1000;
    
   	
   	selector.each(function () {
   		try{
		    var thisValue = $(this).html();
		    // Split value into date and time
		    var thisValueArray = thisValue.split(" ");
		    var thisValueDate = thisValueArray[0];
		    var thisValueTime = thisValueArray[1];
	
		    // Split date into components
		    var thisValueDateArray = thisValueDate.split("-");
		    var curYear = thisValueDateArray[0];
		    var curMonth = thisValueDateArray[1]-1;
		    var curDay = thisValueDateArray[2];
	    
		    // Split time into components
		    var thisValueTimeArray = thisValueTime.split(":");
		    var curHour = thisValueTimeArray[0];
		    var curMin = thisValueTimeArray[1];
		    var curSec = thisValueTimeArray[2];
	
		    // Create this date
		    var thisDate = new Date(curYear, curMonth, curDay, curHour, curMin, curSec);
		    var thisTime = thisDate.getTime();
		    var tzDate = new Date(thisTime - timezoneTimeDiff);
		    //var tzDay = tzDate.getDay();//getDay will return the day of the week not the month
		    //var tzDay = tzDate.getUTCDate(); //getUTCDate will return the day of the month
		    var tzDay = tzDate.toString('d'); //
		    var tzYear = tzDate.getFullYear();
		    var tzHour = tzDate.getHours();
		    var tzMin = tzDate.getMinutes();
	    
		    // Create the full date
		    //var fullDate = days[tzDate.getDay()] + ", " + months[tzDate.getMonth()] + " " + tzDay + ", " + tzYear + getAmPmTime(tzHour, tzMin);
		    var fullDate = days[tzDate.getDay()] + ", " + months[tzDate.getMonth()] + " " + tzDay + ", " + tzYear + getAmPmTime(tzHour, tzMin);
	
		    // Get the time different
		    var timeDiff = (curDate.getTime() - tzDate.getTime())/1000;
		    var minDiff = Math.abs(timeDiff/60);
		    var hourDiff = Math.abs(timeDiff/(60*60));
		    var dayDiff = Math.abs(timeDiff/(60*60*24));
		    var yearDiff = Math.abs(timeDiff/(60*60*24*365));
	
		    // If more than a day old, display the month, day and time (and year, if applicable)
		    var fbDate = '';
		    if (dayDiff > 1) {
			    //fbDate = curDay + " " + months[tzDate.getMonth()].substring(0,3);
			    fbDate = tzDay  + " " + months[tzDate.getMonth()].substring(0,3);
			    // Add the year, if applicable
			    if (yearDiff > 1) {
					fbDate = fbDate + " "+ curYear;
	     		}
	
	     		// Add the time
	     		fbDate = fbDate + getAmPmTime(tzHour, tzMin);
	    	}
	    	// Less than a day old, and more than an hour old
	    	else if (hourDiff >= 1) {
	     		var roundedHour = Math.round(hourDiff);
	     		if (roundedHour == 1) 
	      			fbDate = "about an hour ago";
	     		else 
	      			fbDate = roundedHour + " hours ago";
	    	}
	    	// Less than an hour, and more than a minute
	    	else if (minDiff >= 1) {
	     		var roundedMin = Math.round(minDiff);
	     		if (roundedMin == 1) 
	      			fbDate = "about a minute ago";
	     		else 
	      			fbDate = roundedMin + " minutes ago";
	    	}
	    	// Less than a minute
	    	else if (minDiff < 1) {
	     		fbDate = "less than a minute ago";
	    	}
	
	    	// Update this element
	    	$(this).html(fbDate);
	    	$(this).attr('title', fullDate);
   		}catch(e){}
	});
});