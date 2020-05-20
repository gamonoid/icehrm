"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = exports.YEAR_COL_COUNT = void 0;

var React = _interopRequireWildcard(require("react"));

var _ = require(".");

var _useCellClassName = _interopRequireDefault(require("../../hooks/useCellClassName"));

var _dateUtil = require("../../utils/dateUtil");

var _RangeContext = _interopRequireDefault(require("../../RangeContext"));

var _PanelBody = _interopRequireDefault(require("../PanelBody"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

var YEAR_COL_COUNT = 3;
exports.YEAR_COL_COUNT = YEAR_COL_COUNT;
var YEAR_ROW_COUNT = 4;

function YearBody(props) {
  var prefixCls = props.prefixCls,
      value = props.value,
      viewDate = props.viewDate,
      locale = props.locale,
      generateConfig = props.generateConfig;

  var _React$useContext = React.useContext(_RangeContext.default),
      rangedValue = _React$useContext.rangedValue,
      hoverRangedValue = _React$useContext.hoverRangedValue;

  var yearPrefixCls = "".concat(prefixCls, "-cell"); // =============================== Year ===============================

  var yearNumber = generateConfig.getYear(viewDate);

  var startYear = Math.floor(yearNumber / _.YEAR_DECADE_COUNT) * _.YEAR_DECADE_COUNT;

  var endYear = startYear + _.YEAR_DECADE_COUNT - 1;
  var baseYear = generateConfig.setYear(viewDate, startYear - Math.ceil((YEAR_COL_COUNT * YEAR_ROW_COUNT - _.YEAR_DECADE_COUNT) / 2));

  var isInView = function isInView(date) {
    var currentYearNumber = generateConfig.getYear(date);
    return startYear <= currentYearNumber && currentYearNumber <= endYear;
  };

  var getCellClassName = (0, _useCellClassName.default)({
    cellPrefixCls: yearPrefixCls,
    value: value,
    generateConfig: generateConfig,
    rangedValue: rangedValue,
    hoverRangedValue: hoverRangedValue,
    isSameCell: function isSameCell(current, target) {
      return (0, _dateUtil.isSameYear)(generateConfig, current, target);
    },
    isInView: isInView,
    offsetCell: function offsetCell(date, offset) {
      return generateConfig.addYear(date, offset);
    }
  });
  return React.createElement(_PanelBody.default, Object.assign({}, props, {
    rowNum: YEAR_ROW_COUNT,
    colNum: YEAR_COL_COUNT,
    baseDate: baseYear,
    getCellText: generateConfig.getYear,
    getCellClassName: getCellClassName,
    getCellDate: generateConfig.addYear,
    titleCell: function titleCell(date) {
      return generateConfig.locale.format(locale.locale, date, 'YYYY');
    }
  }));
}

var _default = YearBody;
exports.default = _default;