"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _az_AZ = _interopRequireDefault(require("rc-picker/lib/locale/az_AZ"));

var _az_AZ2 = _interopRequireDefault(require("../../time-picker/locale/az_AZ"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var locale = {
  lang: _extends({
    placeholder: 'Tarix seçin',
    rangePlaceholder: ['Başlama tarixi', 'Bitmə tarixi']
  }, _az_AZ["default"]),
  timePickerLocale: _extends({}, _az_AZ2["default"])
};
var _default = locale;
exports["default"] = _default;