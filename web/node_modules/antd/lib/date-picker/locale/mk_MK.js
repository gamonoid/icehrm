"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _mk_MK = _interopRequireDefault(require("rc-picker/lib/locale/mk_MK"));

var _mk_MK2 = _interopRequireDefault(require("../../time-picker/locale/mk_MK"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

// Merge into a locale object
var locale = {
  lang: _extends({
    placeholder: 'Избери датум',
    rangePlaceholder: ['Од датум', 'До датум']
  }, _mk_MK["default"]),
  timePickerLocale: _extends({}, _mk_MK2["default"])
}; // All settings at:
// https://github.com/ant-design/ant-design/blob/master/components/date-picker/locale/example.json

var _default = locale;
exports["default"] = _default;