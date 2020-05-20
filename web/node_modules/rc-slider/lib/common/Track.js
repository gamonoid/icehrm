'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _extends2 = require('babel-runtime/helpers/extends');

var _extends3 = _interopRequireDefault(_extends2);

var _defineProperty2 = require('babel-runtime/helpers/defineProperty');

var _defineProperty3 = _interopRequireDefault(_defineProperty2);

var _react = require('react');

var _react2 = _interopRequireDefault(_react);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

var Track = function Track(props) {
  var _ref, _ref2;

  var className = props.className,
      included = props.included,
      vertical = props.vertical,
      style = props.style;
  var length = props.length,
      offset = props.offset,
      reverse = props.reverse;

  if (length < 0) {
    reverse = !reverse;
    length = Math.abs(length);
    offset = 100 - offset;
  }

  var positonStyle = vertical ? (_ref = {}, (0, _defineProperty3['default'])(_ref, reverse ? 'top' : 'bottom', offset + '%'), (0, _defineProperty3['default'])(_ref, reverse ? 'bottom' : 'top', 'auto'), (0, _defineProperty3['default'])(_ref, 'height', length + '%'), _ref) : (_ref2 = {}, (0, _defineProperty3['default'])(_ref2, reverse ? 'right' : 'left', offset + '%'), (0, _defineProperty3['default'])(_ref2, reverse ? 'left' : 'right', 'auto'), (0, _defineProperty3['default'])(_ref2, 'width', length + '%'), _ref2);

  var elStyle = (0, _extends3['default'])({}, style, positonStyle);
  return included ? _react2['default'].createElement('div', { className: className, style: elStyle }) : null;
};

exports['default'] = Track;
module.exports = exports['default'];