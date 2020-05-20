"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

var Steps = function Steps(props) {
  var _props$size = props.size,
      size = _props$size === void 0 ? 'default' : _props$size,
      steps = props.steps,
      _props$percent = props.percent,
      percent = _props$percent === void 0 ? 0 : _props$percent,
      _props$strokeWidth = props.strokeWidth,
      strokeWidth = _props$strokeWidth === void 0 ? 8 : _props$strokeWidth,
      strokeColor = props.strokeColor,
      prefixCls = props.prefixCls,
      children = props.children;

  var getStyledSteps = function getStyledSteps() {
    var current = Math.floor(steps * (percent / 100));
    var stepWidth = size === 'small' ? 2 : 14;
    var styleSteps = [];

    for (var i = 0; i < steps; i++) {
      var color = void 0;

      if (i <= current - 1) {
        color = strokeColor;
      }

      var stepStyle = {
        backgroundColor: "".concat(color),
        width: "".concat(stepWidth, "px"),
        height: "".concat(strokeWidth, "px")
      };
      styleSteps.push( /*#__PURE__*/React.createElement("div", {
        key: i,
        className: "".concat(prefixCls, "-steps-item"),
        style: stepStyle
      }));
    }

    return styleSteps;
  };

  return /*#__PURE__*/React.createElement("div", {
    className: "".concat(prefixCls, "-steps-outer")
  }, getStyledSteps(), children);
};

var _default = Steps;
exports["default"] = _default;