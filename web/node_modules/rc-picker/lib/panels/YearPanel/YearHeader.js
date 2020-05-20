"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _Header = _interopRequireDefault(require("../Header"));

var _ = require(".");

var _PanelContext = _interopRequireDefault(require("../../PanelContext"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function YearHeader(props) {
  var prefixCls = props.prefixCls,
      generateConfig = props.generateConfig,
      viewDate = props.viewDate,
      onPrevDecade = props.onPrevDecade,
      onNextDecade = props.onNextDecade,
      onDecadeClick = props.onDecadeClick;

  var _React$useContext = React.useContext(_PanelContext.default),
      hideHeader = _React$useContext.hideHeader;

  if (hideHeader) {
    return null;
  }

  var headerPrefixCls = "".concat(prefixCls, "-header");
  var yearNumber = generateConfig.getYear(viewDate);

  var startYear = Math.floor(yearNumber / _.YEAR_DECADE_COUNT) * _.YEAR_DECADE_COUNT;

  var endYear = startYear + _.YEAR_DECADE_COUNT - 1;
  return React.createElement(_Header.default, Object.assign({}, props, {
    prefixCls: headerPrefixCls,
    onSuperPrev: onPrevDecade,
    onSuperNext: onNextDecade
  }), React.createElement("button", {
    type: "button",
    key: "year",
    onClick: onDecadeClick,
    className: "".concat(prefixCls, "-decade-btn")
  }, startYear, "-", endYear));
}

var _default = YearHeader;
exports.default = _default;