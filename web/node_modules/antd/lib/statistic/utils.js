"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.formatTimeStr = formatTimeStr;
exports.formatCountdown = formatCountdown;

var _padStart = _interopRequireDefault(require("lodash/padStart"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

// Countdown
var timeUnits = [['Y', 1000 * 60 * 60 * 24 * 365], ['M', 1000 * 60 * 60 * 24 * 30], ['D', 1000 * 60 * 60 * 24], ['H', 1000 * 60 * 60], ['m', 1000 * 60], ['s', 1000], ['S', 1]];

function formatTimeStr(duration, format) {
  var leftDuration = duration;
  var escapeRegex = /\[[^\]]*]/g;
  var keepList = (format.match(escapeRegex) || []).map(function (str) {
    return str.slice(1, -1);
  });
  var templateText = format.replace(escapeRegex, '[]');
  var replacedText = timeUnits.reduce(function (current, _ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        name = _ref2[0],
        unit = _ref2[1];

    if (current.indexOf(name) !== -1) {
      var value = Math.floor(leftDuration / unit);
      leftDuration -= value * unit;
      return current.replace(new RegExp("".concat(name, "+"), 'g'), function (match) {
        var len = match.length;
        return (0, _padStart["default"])(value.toString(), len, '0');
      });
    }

    return current;
  }, templateText);
  var index = 0;
  return replacedText.replace(escapeRegex, function () {
    var match = keepList[index];
    index += 1;
    return match;
  });
}

function formatCountdown(value, config) {
  var _config$format = config.format,
      format = _config$format === void 0 ? '' : _config$format;
  var target = new Date(value).getTime();
  var current = Date.now();
  var diff = Math.max(target - current, 0);
  return formatTimeStr(diff, format);
}