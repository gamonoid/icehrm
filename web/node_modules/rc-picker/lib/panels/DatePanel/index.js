"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

var _DateBody = _interopRequireDefault(require("./DateBody"));

var _DateHeader = _interopRequireDefault(require("./DateHeader"));

var _dateUtil = require("../../utils/dateUtil");

var _uiUtil = require("../../utils/uiUtil");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var DATE_ROW_COUNT = 6;

function DatePanel(props) {
  var prefixCls = props.prefixCls,
      _props$panelName = props.panelName,
      panelName = _props$panelName === void 0 ? 'date' : _props$panelName,
      keyboardConfig = props.keyboardConfig,
      active = props.active,
      operationRef = props.operationRef,
      generateConfig = props.generateConfig,
      value = props.value,
      viewDate = props.viewDate,
      onViewDateChange = props.onViewDateChange,
      onPanelChange = props.onPanelChange,
      _onSelect = props.onSelect;
  var panelPrefixCls = "".concat(prefixCls, "-").concat(panelName, "-panel"); // ======================= Keyboard =======================

  operationRef.current = {
    onKeyDown: function onKeyDown(event) {
      return (0, _uiUtil.createKeyDownHandler)(event, _objectSpread({
        onLeftRight: function onLeftRight(diff) {
          _onSelect(generateConfig.addDate(value || viewDate, diff), 'key');
        },
        onCtrlLeftRight: function onCtrlLeftRight(diff) {
          _onSelect(generateConfig.addYear(value || viewDate, diff), 'key');
        },
        onUpDown: function onUpDown(diff) {
          _onSelect(generateConfig.addDate(value || viewDate, diff * _dateUtil.WEEK_DAY_COUNT), 'key');
        },
        onPageUpDown: function onPageUpDown(diff) {
          _onSelect(generateConfig.addMonth(value || viewDate, diff), 'key');
        }
      }, keyboardConfig));
    }
  }; // ==================== View Operation ====================

  var onYearChange = function onYearChange(diff) {
    var newDate = generateConfig.addYear(viewDate, diff);
    onViewDateChange(newDate);
    onPanelChange(null, newDate);
  };

  var onMonthChange = function onMonthChange(diff) {
    var newDate = generateConfig.addMonth(viewDate, diff);
    onViewDateChange(newDate);
    onPanelChange(null, newDate);
  };

  return React.createElement("div", {
    className: (0, _classnames.default)(panelPrefixCls, _defineProperty({}, "".concat(panelPrefixCls, "-active"), active))
  }, React.createElement(_DateHeader.default, Object.assign({}, props, {
    prefixCls: prefixCls,
    value: value,
    viewDate: viewDate,
    // View Operation
    onPrevYear: function onPrevYear() {
      onYearChange(-1);
    },
    onNextYear: function onNextYear() {
      onYearChange(1);
    },
    onPrevMonth: function onPrevMonth() {
      onMonthChange(-1);
    },
    onNextMonth: function onNextMonth() {
      onMonthChange(1);
    },
    onMonthClick: function onMonthClick() {
      onPanelChange('month', viewDate);
    },
    onYearClick: function onYearClick() {
      onPanelChange('year', viewDate);
    }
  })), React.createElement(_DateBody.default, Object.assign({}, props, {
    onSelect: function onSelect(date) {
      return _onSelect(date, 'mouse');
    },
    prefixCls: prefixCls,
    value: value,
    viewDate: viewDate,
    rowCount: DATE_ROW_COUNT
  })));
}

var _default = DatePanel;
exports.default = _default;