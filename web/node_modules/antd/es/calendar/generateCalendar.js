function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

import * as React from 'react';
import classNames from 'classnames';
import padStart from 'lodash/padStart';
import { PickerPanel as RCPickerPanel } from 'rc-picker';
import LocaleReceiver from '../locale-provider/LocaleReceiver';
import enUS from './locale/en_US';
import { ConfigContext } from '../config-provider';
import CalendarHeader from './Header';

function generateCalendar(generateConfig) {
  function isSameMonth(date1, date2) {
    return date1 === date2 || date1 && date2 && generateConfig.getYear(date1) === generateConfig.getYear(date2) && generateConfig.getMonth(date1) === generateConfig.getMonth(date2);
  }

  function isSameDate(date1, date2) {
    return isSameMonth(date1, date2) && generateConfig.getDate(date1) === generateConfig.getDate(date2);
  }

  var Calendar = function Calendar(props) {
    var customizePrefixCls = props.prefixCls,
        className = props.className,
        style = props.style,
        dateFullCellRender = props.dateFullCellRender,
        dateCellRender = props.dateCellRender,
        monthFullCellRender = props.monthFullCellRender,
        monthCellRender = props.monthCellRender,
        headerRender = props.headerRender,
        value = props.value,
        defaultValue = props.defaultValue,
        disabledDate = props.disabledDate,
        mode = props.mode,
        validRange = props.validRange,
        _props$fullscreen = props.fullscreen,
        fullscreen = _props$fullscreen === void 0 ? true : _props$fullscreen,
        onChange = props.onChange,
        onPanelChange = props.onPanelChange,
        onSelect = props.onSelect;

    var _React$useContext = React.useContext(ConfigContext),
        getPrefixCls = _React$useContext.getPrefixCls;

    var prefixCls = getPrefixCls('picker', customizePrefixCls);
    var calendarPrefixCls = "".concat(prefixCls, "-calendar");
    var today = generateConfig.getNow(); // ====================== State =======================
    // Value

    var _React$useState = React.useState(function () {
      return value || defaultValue || generateConfig.getNow();
    }),
        _React$useState2 = _slicedToArray(_React$useState, 2),
        innerValue = _React$useState2[0],
        setInnerValue = _React$useState2[1];

    var mergedValue = value || innerValue; // Mode

    var _React$useState3 = React.useState(function () {
      return mode || 'month';
    }),
        _React$useState4 = _slicedToArray(_React$useState3, 2),
        innerMode = _React$useState4[0],
        setInnerMode = _React$useState4[1];

    var mergedMode = mode || innerMode;
    var panelMode = React.useMemo(function () {
      return mergedMode === 'year' ? 'month' : 'date';
    }, [mergedMode]); // Disabled Date

    var mergedDisabledDate = React.useMemo(function () {
      if (validRange) {
        return function (date) {
          return generateConfig.isAfter(validRange[0], date) || generateConfig.isAfter(date, validRange[1]);
        };
      }

      return disabledDate;
    }, [disabledDate, validRange]); // ====================== Events ======================

    var triggerPanelChange = function triggerPanelChange(date, newMode) {
      if (onPanelChange) {
        onPanelChange(date, newMode);
      }
    };

    var triggerChange = function triggerChange(date) {
      setInnerValue(date);

      if (!isSameDate(date, mergedValue)) {
        triggerPanelChange(date, mergedMode);

        if (onChange) {
          onChange(date);
        }
      }
    };

    var triggerModeChange = function triggerModeChange(newMode) {
      setInnerMode(newMode);
      triggerPanelChange(mergedValue, newMode);
    };

    var onInternalSelect = function onInternalSelect(date) {
      triggerChange(date);

      if (onSelect) {
        onSelect(date);
      }
    }; // ====================== Locale ======================


    var getDefaultLocale = function getDefaultLocale() {
      var locale = props.locale;

      var result = _extends(_extends({}, enUS), locale);

      result.lang = _extends(_extends({}, result.lang), (locale || {}).lang);
      return result;
    }; // ====================== Render ======================


    var dateRender = React.useCallback(function (date) {
      if (dateFullCellRender) {
        return dateFullCellRender(date);
      }

      return /*#__PURE__*/React.createElement("div", {
        className: classNames("".concat(prefixCls, "-cell-inner"), "".concat(calendarPrefixCls, "-date"), _defineProperty({}, "".concat(calendarPrefixCls, "-date-today"), isSameDate(today, date)))
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(calendarPrefixCls, "-date-value")
      }, padStart(String(generateConfig.getDate(date)), 2, '0')), /*#__PURE__*/React.createElement("div", {
        className: "".concat(calendarPrefixCls, "-date-content")
      }, dateCellRender && dateCellRender(date)));
    }, [dateFullCellRender, dateCellRender]);
    var monthRender = React.useCallback(function (date, locale) {
      if (monthFullCellRender) {
        return monthFullCellRender(date);
      }

      var months = locale.shortMonths || generateConfig.locale.getShortMonths(locale.locale);
      return /*#__PURE__*/React.createElement("div", {
        className: classNames("".concat(prefixCls, "-cell-inner"), "".concat(calendarPrefixCls, "-date"), _defineProperty({}, "".concat(calendarPrefixCls, "-date-today"), isSameMonth(today, date)))
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(calendarPrefixCls, "-date-value")
      }, months[generateConfig.getMonth(date)]), /*#__PURE__*/React.createElement("div", {
        className: "".concat(calendarPrefixCls, "-date-content")
      }, monthCellRender && monthCellRender(date)));
    }, [monthFullCellRender, monthCellRender]);
    return /*#__PURE__*/React.createElement(LocaleReceiver, {
      componentName: "Calendar",
      defaultLocale: getDefaultLocale
    }, function (mergedLocale) {
      var _classNames3;

      return /*#__PURE__*/React.createElement("div", {
        className: classNames(calendarPrefixCls, className, (_classNames3 = {}, _defineProperty(_classNames3, "".concat(calendarPrefixCls, "-full"), fullscreen), _defineProperty(_classNames3, "".concat(calendarPrefixCls, "-mini"), !fullscreen), _classNames3)),
        style: style
      }, headerRender ? headerRender({
        value: mergedValue,
        type: mergedMode,
        onChange: onInternalSelect,
        onTypeChange: triggerModeChange
      }) : /*#__PURE__*/React.createElement(CalendarHeader, {
        prefixCls: calendarPrefixCls,
        value: mergedValue,
        generateConfig: generateConfig,
        mode: mergedMode,
        fullscreen: fullscreen,
        locale: mergedLocale.lang,
        validRange: validRange,
        onChange: onInternalSelect,
        onModeChange: triggerModeChange
      }), /*#__PURE__*/React.createElement(RCPickerPanel, {
        value: mergedValue,
        prefixCls: prefixCls,
        locale: mergedLocale.lang,
        generateConfig: generateConfig,
        dateRender: dateRender,
        monthCellRender: function monthCellRender(date) {
          return monthRender(date, mergedLocale.lang);
        },
        onSelect: onInternalSelect,
        mode: panelMode,
        picker: panelMode,
        disabledDate: mergedDisabledDate,
        hideHeader: true
      }));
    });
  };

  return Calendar;
}

export default generateCalendar;