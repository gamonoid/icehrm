"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var Content = function Content(props) {
  var overlay = props.overlay,
      prefixCls = props.prefixCls,
      id = props.id;
  return _react.default.createElement("div", {
    className: "".concat(prefixCls, "-inner"),
    id: id,
    role: "tooltip"
  }, typeof overlay === 'function' ? overlay() : overlay);
};

var _default = Content;
exports.default = _default;