"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var PopupInner = function PopupInner(props, ref) {
  var prefixCls = props.prefixCls,
      className = props.className,
      visible = props.visible,
      style = props.style,
      children = props.children,
      onMouseEnter = props.onMouseEnter,
      onMouseLeave = props.onMouseLeave,
      onMouseDown = props.onMouseDown,
      onTouchStart = props.onTouchStart;
  var childNode = children;

  if (_react.default.Children.count(children) > 1) {
    childNode = _react.default.createElement("div", {
      className: "".concat(prefixCls, "-content")
    }, children);
  }

  return _react.default.createElement("div", {
    ref: ref,
    className: (0, _classnames.default)(className, !visible && "".concat(props.hiddenClassName)),
    onMouseEnter: onMouseEnter,
    onMouseLeave: onMouseLeave,
    onMouseDown: onMouseDown,
    onTouchStart: onTouchStart,
    style: style
  }, childNode);
};

var RefPopupInner = _react.default.forwardRef(PopupInner);

RefPopupInner.displayName = 'PopupInner';
var _default = RefPopupInner;
exports.default = _default;