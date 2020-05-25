"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function notEmpty(val) {
  return val !== undefined && val !== null;
}

var Cell = function Cell(_ref) {
  var itemPrefixCls = _ref.itemPrefixCls,
      component = _ref.component,
      span = _ref.span,
      className = _ref.className,
      style = _ref.style,
      bordered = _ref.bordered,
      label = _ref.label,
      content = _ref.content,
      colon = _ref.colon;
  var Component = component;

  if (bordered) {
    var _classNames;

    return /*#__PURE__*/React.createElement(Component, {
      className: (0, _classnames["default"])((_classNames = {}, _defineProperty(_classNames, "".concat(itemPrefixCls, "-item-label"), notEmpty(label)), _defineProperty(_classNames, "".concat(itemPrefixCls, "-item-content"), notEmpty(content)), _classNames), className),
      style: style,
      colSpan: span
    }, notEmpty(label) ? label : content);
  }

  return /*#__PURE__*/React.createElement(Component, {
    className: (0, _classnames["default"])("".concat(itemPrefixCls, "-item"), className),
    style: style,
    colSpan: span
  }, label && /*#__PURE__*/React.createElement("span", {
    className: (0, _classnames["default"])("".concat(itemPrefixCls, "-item-label"), _defineProperty({}, "".concat(itemPrefixCls, "-item-colon"), colon))
  }, label), content && /*#__PURE__*/React.createElement("span", {
    className: (0, _classnames["default"])("".concat(itemPrefixCls, "-item-content"))
  }, content));
};

var _default = Cell;
exports["default"] = _default;