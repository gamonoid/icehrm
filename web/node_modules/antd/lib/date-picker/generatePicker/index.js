"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.getTimeProps = getTimeProps;
exports["default"] = exports.Components = void 0;

var _PickerButton = _interopRequireDefault(require("../PickerButton"));

var _PickerTag = _interopRequireDefault(require("../PickerTag"));

var _generateSinglePicker2 = _interopRequireDefault(require("./generateSinglePicker"));

var _generateRangePicker = _interopRequireDefault(require("./generateRangePicker"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var Components = {
  button: _PickerButton["default"],
  rangeItem: _PickerTag["default"]
};
exports.Components = Components;

function toArray(list) {
  if (!list) {
    return [];
  }

  return Array.isArray(list) ? list : [list];
}

function getTimeProps(props) {
  var format = props.format,
      picker = props.picker,
      showHour = props.showHour,
      showMinute = props.showMinute,
      showSecond = props.showSecond,
      use12Hours = props.use12Hours;
  var firstFormat = toArray(format)[0];

  var showTimeObj = _extends({}, props);

  if (firstFormat) {
    if (!firstFormat.includes('s') && showSecond === undefined) {
      showTimeObj.showSecond = false;
    }

    if (!firstFormat.includes('m') && showMinute === undefined) {
      showTimeObj.showMinute = false;
    }

    if (!firstFormat.includes('H') && !firstFormat.includes('h') && showHour === undefined) {
      showTimeObj.showHour = false;
    }

    if ((firstFormat.includes('a') || firstFormat.includes('A')) && use12Hours === undefined) {
      showTimeObj.use12Hours = true;
    }
  }

  if (picker === 'time') {
    return showTimeObj;
  }

  return {
    showTime: showTimeObj
  };
}

function generatePicker(generateConfig) {
  // =========================== Picker ===========================
  var _generateSinglePicker = (0, _generateSinglePicker2["default"])(generateConfig),
      DatePicker = _generateSinglePicker.DatePicker,
      WeekPicker = _generateSinglePicker.WeekPicker,
      MonthPicker = _generateSinglePicker.MonthPicker,
      YearPicker = _generateSinglePicker.YearPicker,
      TimePicker = _generateSinglePicker.TimePicker; // ======================== Range Picker ========================


  var RangePicker = (0, _generateRangePicker["default"])(generateConfig);
  var MergedDatePicker = DatePicker;
  MergedDatePicker.WeekPicker = WeekPicker;
  MergedDatePicker.MonthPicker = MonthPicker;
  MergedDatePicker.YearPicker = YearPicker;
  MergedDatePicker.RangePicker = RangePicker;
  MergedDatePicker.TimePicker = TimePicker;
  return MergedDatePicker;
}

var _default = generatePicker;
exports["default"] = _default;