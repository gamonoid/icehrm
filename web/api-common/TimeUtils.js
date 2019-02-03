/* eslint-disable camelcase,brace-style */

/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

class TimeUtils {
  setServerGMToffset(serverGMToffset) {
    this.serverGMToffset = serverGMToffset;
  }

  getMySQLFormatDate(date) {
    const format = function (val) {
      if (val < 10) { return `0${val}`; }
      return val;
    };

    return `${date.getUTCFullYear()}-${format(date.getUTCMonth() + 1)}-${format(date.getUTCDate())}`;
  }

  convertToRelativeTime(selector) {
    const that = this;

    const getAmPmTime = function (curHour, curMin) {
      let amPm = 'am';
      let amPmHour = curHour;
      if (amPmHour >= 12) {
        amPm = 'pm';
        if (amPmHour > 12) {
          amPmHour -= 12;
        }
      }
      let prefixCurMin = '';
      if (curMin < 10) {
        prefixCurMin = '0';
      }

      let prefixCurHour = '';
      if (curHour === 0) {
        prefixCurHour = '0';
      }
      return ` at ${prefixCurHour}${amPmHour}:${prefixCurMin}${curMin}${amPm}`;
    };

    const getBrowserTimeZone = function () {
      const current_date = new Date();
      const gmt_offset = current_date.getTimezoneOffset() / 60;
      return -gmt_offset;
    };

    const curDate = new Date();
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];


    const timezoneDiff = this.serverGMToffset - getBrowserTimeZone();
    const timezoneTimeDiff = timezoneDiff * 60 * 60 * 1000;


    selector.each(function () {
      try {
        const thisValue = $(this).html();
        // Split value into date and time
        const thisValueArray = thisValue.split(' ');
        const thisValueDate = thisValueArray[0];
        const thisValueTime = thisValueArray[1];

        // Split date into components
        const thisValueDateArray = thisValueDate.split('-');
        const curYear = thisValueDateArray[0];
        const curMonth = thisValueDateArray[1] - 1;
        const curDay = thisValueDateArray[2];

        // Split time into components
        const thisValueTimeArray = thisValueTime.split(':');
        const curHour = thisValueTimeArray[0];
        const curMin = thisValueTimeArray[1];
        const curSec = thisValueTimeArray[2];

        // Create this date
        const thisDate = new Date(curYear, curMonth, curDay, curHour, curMin, curSec);
        const thisTime = thisDate.getTime();
        const tzDate = new Date(thisTime - timezoneTimeDiff);
        // var tzDay = tzDate.getDay();//getDay will return the day of the week not the month
        // var tzDay = tzDate.getUTCDate(); //getUTCDate will return the day of the month
        const tzDay = tzDate.toString('d'); //
        const tzYear = tzDate.getFullYear();
        const tzHour = tzDate.getHours();
        const tzMin = tzDate.getMinutes();

        // Create the full date
        // var fullDate = days[tzDate.getDay()] + ", " + months[tzDate.getMonth()] + " " + tzDay + ", " + tzYear + getAmPmTime(tzHour, tzMin);
        const fullDate = `${days[tzDate.getDay()]}, ${months[tzDate.getMonth()]} ${tzDay}, ${tzYear}${getAmPmTime(tzHour, tzMin)}`;

        // Get the time different
        const timeDiff = (curDate.getTime() - tzDate.getTime()) / 1000;
        const minDiff = Math.abs(timeDiff / 60);
        const hourDiff = Math.abs(timeDiff / (60 * 60));
        const dayDiff = Math.abs(timeDiff / (60 * 60 * 24));
        const yearDiff = Math.abs(timeDiff / (60 * 60 * 24 * 365));

        // If more than a day old, display the month, day and time (and year, if applicable)
        let fbDate = '';
        if (dayDiff > 1) {
          // fbDate = curDay + " " + months[tzDate.getMonth()].substring(0,3);
          fbDate = `${tzDay} ${months[tzDate.getMonth()].substring(0, 3)}`;
          // Add the year, if applicable
          if (yearDiff > 1) {
            fbDate = `${fbDate} ${curYear}`;
          }

          // Add the time
          fbDate += getAmPmTime(tzHour, tzMin);
        }
        // Less than a day old, and more than an hour old
        else if (hourDiff >= 1) {
          const roundedHour = Math.round(hourDiff);
          if (roundedHour === 1) fbDate = 'about an hour ago';
          else fbDate = `${roundedHour} hours ago`;
        }
        // Less than an hour, and more than a minute
        else if (minDiff >= 1) {
          const roundedMin = Math.round(minDiff);
          if (roundedMin === 1) fbDate = 'about a minute ago';
          else fbDate = `${roundedMin} minutes ago`;
        }
        // Less than a minute
        else if (minDiff < 1) {
          fbDate = 'less than a minute ago';
        }

        // Update this element
        $(this).html(fbDate);
        $(this).attr('title', fullDate);
      } catch (e) {
        // Do nothing
      }
    });
  }
}

export default TimeUtils;
