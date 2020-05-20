"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _fr_FR = _interopRequireDefault(require("rc-picker/lib/locale/fr_FR"));

var _fr_FR2 = _interopRequireDefault(require("../../time-picker/locale/fr_FR"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

// Merge into a locale object
var locale = {
  lang: _extends({
    placeholder: 'Sélectionner une date',
    yearPlaceholder: 'Sélectionner une année',
    quarterPlaceholder: 'Sélectionner un trimestre',
    monthPlaceholder: 'Sélectionner un mois',
    weekPlaceholder: 'Sélectionner une semaine',
    rangePlaceholder: ['Date de début', 'Date de fin'],
    rangeYearPlaceholder: ['Année de début', 'Année de fin'],
    rangeMonthPlaceholder: ['Mois de début', 'Mois de fin'],
    rangeWeekPlaceholder: ['Semaine de début', 'Semaine de fin']
  }, _fr_FR["default"]),
  timePickerLocale: _extends({}, _fr_FR2["default"])
}; // All settings at:
// https://github.com/ant-design/ant-design/issues/424

var _default = locale;
exports["default"] = _default;