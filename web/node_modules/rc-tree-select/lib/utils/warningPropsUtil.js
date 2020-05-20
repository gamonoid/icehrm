"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _warning = _interopRequireDefault(require("rc-util/lib/warning"));

var _valueUtil = require("./valueUtil");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function warningProps(props) {
  var searchPlaceholder = props.searchPlaceholder,
      treeCheckStrictly = props.treeCheckStrictly,
      treeCheckable = props.treeCheckable,
      labelInValue = props.labelInValue,
      value = props.value,
      multiple = props.multiple;
  (0, _warning.default)(!searchPlaceholder, '`searchPlaceholder` has been removed.');

  if (treeCheckStrictly && labelInValue === false) {
    (0, _warning.default)(false, '`treeCheckStrictly` will force set `labelInValue` to `true`.');
  }

  if (labelInValue || treeCheckStrictly) {
    (0, _warning.default)((0, _valueUtil.toArray)(value).every(function (val) {
      return val && _typeof(val) === 'object' && 'value' in val;
    }), 'Invalid prop `value` supplied to `TreeSelect`. You should use { label: string, value: string | number } or [{ label: string, value: string | number }] instead.');
  }

  if (treeCheckStrictly || multiple || treeCheckable) {
    (0, _warning.default)(!value || Array.isArray(value), '`value` should be an array when `TreeSelect` is checkable or multiple.');
  } else {
    (0, _warning.default)(!Array.isArray(value), '`value` should not be array when `TreeSelect` is single mode.');
  }
}

var _default = warningProps;
exports.default = _default;