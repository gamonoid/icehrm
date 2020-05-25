"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _select = _interopRequireDefault(require("../select"));

var _radio = require("../radio");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var YearSelectOffset = 10;
var YearSelectTotal = 20;

function YearSelect(props) {
  var fullscreen = props.fullscreen,
      validRange = props.validRange,
      generateConfig = props.generateConfig,
      locale = props.locale,
      prefixCls = props.prefixCls,
      value = props.value,
      _onChange = props.onChange,
      divRef = props.divRef;
  var year = generateConfig.getYear(value);
  var start = year - YearSelectOffset;
  var end = start + YearSelectTotal;

  if (validRange) {
    start = generateConfig.getYear(validRange[0]);
    end = generateConfig.getYear(validRange[1]) + 1;
  }

  var suffix = locale && locale.year === '年' ? '年' : '';
  var options = [];

  for (var index = start; index < end; index++) {
    options.push({
      label: "".concat(index).concat(suffix),
      value: index
    });
  }

  return /*#__PURE__*/React.createElement(_select["default"], {
    size: fullscreen ? undefined : 'small',
    options: options,
    value: year,
    className: "".concat(prefixCls, "-year-select"),
    onChange: function onChange(numYear) {
      var newDate = generateConfig.setYear(value, numYear);

      if (validRange) {
        var _validRange = _slicedToArray(validRange, 2),
            startDate = _validRange[0],
            endDate = _validRange[1];

        var newYear = generateConfig.getYear(newDate);
        var newMonth = generateConfig.getMonth(newDate);

        if (newYear === generateConfig.getYear(endDate) && newMonth > generateConfig.getMonth(endDate)) {
          newDate = generateConfig.setMonth(newDate, generateConfig.getMonth(endDate));
        }

        if (newYear === generateConfig.getYear(startDate) && newMonth < generateConfig.getMonth(startDate)) {
          newDate = generateConfig.setMonth(newDate, generateConfig.getMonth(startDate));
        }
      }

      _onChange(newDate);
    },
    getPopupContainer: function getPopupContainer() {
      return divRef.current;
    }
  });
}

function MonthSelect(props) {
  var prefixCls = props.prefixCls,
      fullscreen = props.fullscreen,
      validRange = props.validRange,
      value = props.value,
      generateConfig = props.generateConfig,
      locale = props.locale,
      _onChange2 = props.onChange,
      divRef = props.divRef;
  var month = generateConfig.getMonth(value);
  var start = 0;
  var end = 12;

  if (validRange) {
    var _validRange2 = _slicedToArray(validRange, 2),
        rangeStart = _validRange2[0],
        rangeEnd = _validRange2[1];

    var currentYear = generateConfig.getYear(value);

    if (generateConfig.getYear(rangeEnd) === currentYear) {
      end = generateConfig.getMonth(rangeEnd);
    }

    if (generateConfig.getYear(rangeStart) === currentYear) {
      start = generateConfig.getMonth(rangeStart);
    }
  }

  var months = locale.shortMonths || generateConfig.locale.getShortMonths(locale.locale);
  var options = [];

  for (var index = start; index < end; index += 1) {
    options.push({
      label: months[index],
      value: index
    });
  }

  return /*#__PURE__*/React.createElement(_select["default"], {
    size: fullscreen ? undefined : 'small',
    className: "".concat(prefixCls, "-month-select"),
    value: month,
    options: options,
    onChange: function onChange(newMonth) {
      _onChange2(generateConfig.setMonth(value, newMonth));
    },
    getPopupContainer: function getPopupContainer() {
      return divRef.current;
    }
  });
}

function ModeSwitch(props) {
  var prefixCls = props.prefixCls,
      locale = props.locale,
      mode = props.mode,
      fullscreen = props.fullscreen,
      onModeChange = props.onModeChange;
  return /*#__PURE__*/React.createElement(_radio.Group, {
    onChange: function onChange(_ref) {
      var value = _ref.target.value;
      onModeChange(value);
    },
    value: mode,
    size: fullscreen ? undefined : 'small',
    className: "".concat(prefixCls, "-mode-switch")
  }, /*#__PURE__*/React.createElement(_radio.Button, {
    value: "month"
  }, locale.month), /*#__PURE__*/React.createElement(_radio.Button, {
    value: "year"
  }, locale.year));
}

function CalendarHeader(props) {
  var prefixCls = props.prefixCls,
      fullscreen = props.fullscreen,
      mode = props.mode,
      onChange = props.onChange,
      onModeChange = props.onModeChange;
  var divRef = React.useRef(null);

  var sharedProps = _extends(_extends({}, props), {
    onChange: onChange,
    fullscreen: fullscreen,
    divRef: divRef
  });

  return /*#__PURE__*/React.createElement("div", {
    className: "".concat(prefixCls, "-header"),
    ref: divRef
  }, /*#__PURE__*/React.createElement(YearSelect, sharedProps), mode === 'month' && /*#__PURE__*/React.createElement(MonthSelect, sharedProps), /*#__PURE__*/React.createElement(ModeSwitch, _extends({}, sharedProps, {
    onModeChange: onModeChange
  })));
}

var _default = CalendarHeader;
exports["default"] = _default;