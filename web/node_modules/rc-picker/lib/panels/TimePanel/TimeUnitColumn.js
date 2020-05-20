"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

var _uiUtil = require("../../utils/uiUtil");

var _PanelContext = _interopRequireDefault(require("../../PanelContext"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function TimeUnitColumn(props) {
  var prefixCls = props.prefixCls,
      units = props.units,
      onSelect = props.onSelect,
      value = props.value,
      active = props.active,
      hideDisabledOptions = props.hideDisabledOptions;
  var cellPrefixCls = "".concat(prefixCls, "-cell");

  var _React$useContext = React.useContext(_PanelContext.default),
      open = _React$useContext.open;

  var ulRef = React.useRef(null);
  var liRefs = React.useRef(new Map()); // `useLayoutEffect` here to avoid blink by duration is 0

  React.useLayoutEffect(function () {
    var li = liRefs.current.get(value);

    if (li && open !== false) {
      (0, _uiUtil.scrollTo)(ulRef.current, li.offsetTop, 120);
    }
  }, [value]);
  React.useLayoutEffect(function () {
    if (open) {
      var li = liRefs.current.get(value);

      if (li) {
        (0, _uiUtil.scrollTo)(ulRef.current, li.offsetTop, 0);
      }
    }
  }, [open]);
  return React.createElement("ul", {
    className: (0, _classnames.default)("".concat(prefixCls, "-column"), _defineProperty({}, "".concat(prefixCls, "-column-active"), active)),
    ref: ulRef,
    style: {
      position: 'relative'
    }
  }, units.map(function (unit) {
    var _classNames2;

    if (hideDisabledOptions && unit.disabled) {
      return null;
    }

    return React.createElement("li", {
      key: unit.value,
      ref: function ref(element) {
        liRefs.current.set(unit.value, element);
      },
      className: (0, _classnames.default)(cellPrefixCls, (_classNames2 = {}, _defineProperty(_classNames2, "".concat(cellPrefixCls, "-disabled"), unit.disabled), _defineProperty(_classNames2, "".concat(cellPrefixCls, "-selected"), value === unit.value), _classNames2)),
      onClick: function onClick() {
        if (unit.disabled) {
          return;
        }

        onSelect(unit.value);
      }
    }, React.createElement("div", {
      className: "".concat(cellPrefixCls, "-inner")
    }, unit.label));
  }));
}

var _default = TimeUnitColumn;
exports.default = _default;