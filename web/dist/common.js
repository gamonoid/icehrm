(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
var NotificationManager = /*#__PURE__*/function () {
  function NotificationManager() {
    _classCallCheck(this, NotificationManager);

    this.baseUrl = '';
    this.templates = {};
  }

  _createClass(NotificationManager, [{
    key: "setBaseUrl",
    value: function setBaseUrl(url) {
      this.baseUrl = url;
    }
  }, {
    key: "setTemplates",
    value: function setTemplates(data) {
      this.templates = data;
    }
  }, {
    key: "setTimeUtils",
    value: function setTimeUtils(timeUtils) {
      this.timeUtils = timeUtils;
    }
  }, {
    key: "getNotifications",
    value: function getNotifications(name, data) {
      var that = this;
      $.getJSON(this.baseUrl, {
        a: 'getNotifications'
      }, function (_data) {
        if (_data.status === 'SUCCESS') {
          that.renderNotifications(_data.data[1], _data.data[0]);
        }
      });
    }
  }, {
    key: "clearPendingNotifications",
    value: function clearPendingNotifications(name, data) {
      var that = this;
      $.getJSON(this.baseUrl, {
        a: 'clearNotifications'
      }, function (_data) {});
    }
  }, {
    key: "renderNotifications",
    value: function renderNotifications(notifications, unreadCount) {
      if (notifications.length === 0) {
        return;
      }

      var t = this.templates.notifications;

      if (unreadCount > 0) {
        t = t.replace('#_count_#', unreadCount);

        if (unreadCount > 1) {
          t = t.replace('#_header_#', "You have ".concat(unreadCount, " new notifications"));
        } else {
          t = t.replace('#_header_#', "You have ".concat(unreadCount, " new notification"));
        }
      } else {
        t = t.replace('#_count_#', '');
        t = t.replace('#_header_#', 'You have no new notifications');
      }

      var notificationStr = '';

      for (var index in notifications) {
        notificationStr += this.renderNotification(notifications[index]);
      }

      t = t.replace('#_notifications_#', notificationStr);
      var $obj = $(t);

      if (unreadCount === 0) {
        $obj.find('.label-danger').remove();
      }

      $obj.attr('id', 'notifications');
      var k = $('#notifications');
      k.replaceWith($obj);
      $('.navbar .menu').slimscroll({
        height: '320px',
        alwaysVisible: false,
        size: '3px'
      }).css('width', '100%');
      this.timeUtils.convertToRelativeTime($('.notificationTime'));
    }
  }, {
    key: "renderNotification",
    value: function renderNotification(notification) {
      var t = this.templates.notification;
      t = t.replace('#_image_#', notification.image);

      try {
        var json = JSON.parse(notification.action);
        t = t.replace('#_url_#', this.baseUrl.replace('service.php', '?') + json.url);
      } catch (e) {
        t = t.replace('#_url_#', '');
      }

      t = t.replace('#_time_#', notification.time);
      t = t.replace('#_fromName_#', notification.type);
      t = t.replace('#_message_#', this.getLineBreakString(notification.message, 27));
      return t;
    }
  }, {
    key: "getLineBreakString",
    value: function getLineBreakString(str, len) {
      var t = '';

      try {
        var arr = str.split(' ');
        var count = 0;

        for (var i = 0; i < arr.length; i++) {
          count += arr[i].length + 1;

          if (count > len) {
            t += "".concat(arr[i], "<br/>");
            count = 0;
          } else {
            t += "".concat(arr[i], " ");
          }
        }
      } catch (e) {// Do nothing
      }

      return t;
    }
  }]);

  return NotificationManager;
}();

var _default = NotificationManager;
exports["default"] = _default;

},{}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * RequestCache
 */
var MemoryStorage = /*#__PURE__*/function () {
  function MemoryStorage() {
    _classCallCheck(this, MemoryStorage);

    this.data = {};
  }

  _createClass(MemoryStorage, [{
    key: "getItem",
    value: function getItem(key) {
      return this.data[key];
    }
  }, {
    key: "setItem",
    value: function setItem(key, data) {
      this.data[key] = data;
    }
  }, {
    key: "removeAllByPrefix",
    value: function removeAllByPrefix(prefix) {
      var keys = Object.keys(this.data);

      for (var i = 0; i < keys.length; i++) {
        if (keys[i].indexOf(prefix) > 0) {
          delete this.data[keys[i]];
        }
      }
    }
  }]);

  return MemoryStorage;
}();

var RequestCache = /*#__PURE__*/function () {
  function RequestCache(storage) {
    _classCallCheck(this, RequestCache);

    if (!storage) {
      this.storage = new MemoryStorage();
    } else {
      this.storage = storage;
    }
  }

  _createClass(RequestCache, [{
    key: "getKey",
    value: function getKey(url, params) {
      var key = "".concat(url, "|");

      for (var index in params) {
        key += "".concat(index, "=").concat(params[index], "|");
      }

      return key;
    }
    /*
    invalidateTable(table) {
      let key;
      for (let i = 0; i < this.storage.length; i++) {
        key = this.storage.key(i);
        if (key.indexOf(`t=${table}`) > 0) {
          this.storage.removeItem(key);
        }
      }
    }
    */

  }, {
    key: "invalidateTable",
    value: function invalidateTable(table) {
      this.storage.removeAllByPrefix("t=".concat(table));
    }
  }, {
    key: "getData",
    value: function getData(key) {
      var data = this.storage.getItem(key);

      if (!data) {
        return null;
      }

      return data;
    }
  }, {
    key: "setData",
    value: function setData(key, data) {
      if (data.status !== undefined && data.status != null && data.status !== 'SUCCESS') {
        return null;
      }

      this.storage.setItem(key, data);
      return data;
    }
  }]);

  return RequestCache;
}();

var _default = RequestCache;
exports["default"] = _default;

},{}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

/* eslint-disable no-restricted-globals */

/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
var SocialShare = {
  facebook: function facebook(url) {
    var w = 700;
    var h = 500;
    var left = screen.width / 2 - w / 2;
    var top = screen.height / 2 - h / 2;
    url = "https://www.facebook.com/sharer/sharer.php?u=".concat(encodeURIComponent(url));
    window.open(url, 'Share on Facebook', "width=".concat(w, ",height=").concat(h, ",left=").concat(left, ",top=").concat(top));
    return false;
  },
  google: function google(url) {
    var w = 500;
    var h = 500;
    var left = screen.width / 2 - w / 2;
    var top = screen.height / 2 - h / 2;
    url = "https://plus.google.com/share?url=".concat(encodeURIComponent(url));
    window.open(url, 'Share on Google', "width=".concat(w, ",height=").concat(h, ",left=").concat(left, ",top=").concat(top));
    return false;
  },
  linkedin: function linkedin(url) {
    var w = 500;
    var h = 500;
    var left = screen.width / 2 - w / 2;
    var top = screen.height / 2 - h / 2;
    url = "https://www.linkedin.com/cws/share?url=".concat(encodeURIComponent(url));
    window.open(url, 'Share on Linked in', "width=".concat(w, ",height=").concat(h, ",left=").concat(left, ",top=").concat(top));
    return false;
  },
  twitter: function twitter(url, msg) {
    window.open("http://twitter.com/share?text=".concat(escape(msg), "&url=").concat(escape(url)), 'popup', 'width=550,height=260,scrollbars=yes,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=200,top=200');
    return false;
  }
};
var _default = SocialShare;
exports["default"] = _default;

},{}],4:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/* eslint-disable camelcase,brace-style */

/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
var TimeUtils = /*#__PURE__*/function () {
  function TimeUtils() {
    _classCallCheck(this, TimeUtils);
  }

  _createClass(TimeUtils, [{
    key: "setServerGMToffset",
    value: function setServerGMToffset(serverGMToffset) {
      this.serverGMToffset = serverGMToffset;
    }
  }, {
    key: "getMySQLFormatDate",
    value: function getMySQLFormatDate(date) {
      var format = function format(val) {
        if (val < 10) {
          return "0".concat(val);
        }

        return val;
      };

      return "".concat(date.getUTCFullYear(), "-").concat(format(date.getUTCMonth() + 1), "-").concat(format(date.getUTCDate()));
    }
  }, {
    key: "convertToRelativeTime",
    value: function convertToRelativeTime(selector) {
      var that = this;

      var getAmPmTime = function getAmPmTime(curHour, curMin) {
        var amPm = 'am';
        var amPmHour = curHour;

        if (amPmHour >= 12) {
          amPm = 'pm';

          if (amPmHour > 12) {
            amPmHour -= 12;
          }
        }

        var prefixCurMin = '';

        if (curMin < 10) {
          prefixCurMin = '0';
        }

        var prefixCurHour = '';

        if (curHour === 0) {
          prefixCurHour = '0';
        }

        return " at ".concat(prefixCurHour).concat(amPmHour, ":").concat(prefixCurMin).concat(curMin).concat(amPm);
      };

      var getBrowserTimeZone = function getBrowserTimeZone() {
        var current_date = new Date();
        var gmt_offset = current_date.getTimezoneOffset() / 60;
        return -gmt_offset;
      };

      var curDate = new Date();
      var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      var timezoneDiff = this.serverGMToffset - getBrowserTimeZone();
      var timezoneTimeDiff = timezoneDiff * 60 * 60 * 1000;
      selector.each(function () {
        try {
          var thisValue = $(this).html(); // Split value into date and time

          var thisValueArray = thisValue.split(' ');
          var thisValueDate = thisValueArray[0];
          var thisValueTime = thisValueArray[1]; // Split date into components

          var thisValueDateArray = thisValueDate.split('-');
          var curYear = thisValueDateArray[0];
          var curMonth = thisValueDateArray[1] - 1;
          var curDay = thisValueDateArray[2]; // Split time into components

          var thisValueTimeArray = thisValueTime.split(':');
          var curHour = thisValueTimeArray[0];
          var curMin = thisValueTimeArray[1];
          var curSec = thisValueTimeArray[2]; // Create this date

          var thisDate = new Date(curYear, curMonth, curDay, curHour, curMin, curSec);
          var thisTime = thisDate.getTime();
          var tzDate = new Date(thisTime - timezoneTimeDiff); // var tzDay = tzDate.getDay();//getDay will return the day of the week not the month
          // var tzDay = tzDate.getUTCDate(); //getUTCDate will return the day of the month

          var tzDay = tzDate.toString('d'); //

          var tzYear = tzDate.getFullYear();
          var tzHour = tzDate.getHours();
          var tzMin = tzDate.getMinutes(); // Create the full date
          // var fullDate = days[tzDate.getDay()] + ", " + months[tzDate.getMonth()] + " " + tzDay + ", " + tzYear + getAmPmTime(tzHour, tzMin);

          var fullDate = "".concat(days[tzDate.getDay()], ", ").concat(months[tzDate.getMonth()], " ").concat(tzDay, ", ").concat(tzYear).concat(getAmPmTime(tzHour, tzMin)); // Get the time different

          var timeDiff = (curDate.getTime() - tzDate.getTime()) / 1000;
          var minDiff = Math.abs(timeDiff / 60);
          var hourDiff = Math.abs(timeDiff / (60 * 60));
          var dayDiff = Math.abs(timeDiff / (60 * 60 * 24));
          var yearDiff = Math.abs(timeDiff / (60 * 60 * 24 * 365)); // If more than a day old, display the month, day and time (and year, if applicable)

          var fbDate = '';

          if (dayDiff > 1) {
            // fbDate = curDay + " " + months[tzDate.getMonth()].substring(0,3);
            fbDate = "".concat(tzDay, " ").concat(months[tzDate.getMonth()].substring(0, 3)); // Add the year, if applicable

            if (yearDiff > 1) {
              fbDate = "".concat(fbDate, " ").concat(curYear);
            } // Add the time


            fbDate += getAmPmTime(tzHour, tzMin);
          } // Less than a day old, and more than an hour old
          else if (hourDiff >= 1) {
              var roundedHour = Math.round(hourDiff);
              if (roundedHour === 1) fbDate = 'about an hour ago';else fbDate = "".concat(roundedHour, " hours ago");
            } // Less than an hour, and more than a minute
            else if (minDiff >= 1) {
                var roundedMin = Math.round(minDiff);
                if (roundedMin === 1) fbDate = 'about a minute ago';else fbDate = "".concat(roundedMin, " minutes ago");
              } // Less than a minute
              else if (minDiff < 1) {
                  fbDate = 'less than a minute ago';
                } // Update this element


          $(this).html(fbDate);
          $(this).attr('title', fullDate);
        } catch (e) {// Do nothing
        }
      });
    }
  }]);

  return TimeUtils;
}();

var _default = TimeUtils;
exports["default"] = _default;

},{}],5:[function(require,module,exports){
"use strict";

var _Notifications = _interopRequireDefault(require("./Notifications"));

var _TimeUtils = _interopRequireDefault(require("./TimeUtils"));

var _RequestCache = _interopRequireDefault(require("./RequestCache"));

var _SocialShare = _interopRequireDefault(require("./SocialShare"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

/* global timeUtils */

/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
window.RequestCache = _RequestCache["default"];
window.SocialShare = _SocialShare["default"];

window.setupTimeUtils = function (diffHoursBetweenServerTimezoneWithGMT) {
  var timeUtils = new _TimeUtils["default"]();
  timeUtils.setServerGMToffset(diffHoursBetweenServerTimezoneWithGMT);
  return timeUtils;
};

window.setupNotifications = function (baseUrl) {
  var notificationManager = new _Notifications["default"]();
  notificationManager.setBaseUrl(baseUrl);
  notificationManager.setTimeUtils(timeUtils);
  return notificationManager;
};

},{"./Notifications":1,"./RequestCache":2,"./SocialShare":3,"./TimeUtils":4}]},{},[5])

//# sourceMappingURL=common.js.map
