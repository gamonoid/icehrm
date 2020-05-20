function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

import * as React from 'react';
import classNames from 'classnames';
import { withConfigConsumer } from '../config-provider/context';
import StatisticNumber from './Number';

var Statistic = function Statistic(props) {
  var prefixCls = props.prefixCls,
      className = props.className,
      style = props.style,
      valueStyle = props.valueStyle,
      _props$value = props.value,
      value = _props$value === void 0 ? 0 : _props$value,
      title = props.title,
      valueRender = props.valueRender,
      prefix = props.prefix,
      suffix = props.suffix,
      direction = props.direction;
  var valueNode = /*#__PURE__*/React.createElement(StatisticNumber, _extends({}, props, {
    value: value
  }));

  if (valueRender) {
    valueNode = valueRender(valueNode);
  }

  var cls = classNames(prefixCls, className, _defineProperty({}, "".concat(prefixCls, "-rtl"), direction === 'rtl'));
  return /*#__PURE__*/React.createElement("div", {
    className: cls,
    style: style
  }, title && /*#__PURE__*/React.createElement("div", {
    className: "".concat(prefixCls, "-title")
  }, title), /*#__PURE__*/React.createElement("div", {
    style: valueStyle,
    className: "".concat(prefixCls, "-content")
  }, prefix && /*#__PURE__*/React.createElement("span", {
    className: "".concat(prefixCls, "-content-prefix")
  }, prefix), valueNode, suffix && /*#__PURE__*/React.createElement("span", {
    className: "".concat(prefixCls, "-content-suffix")
  }, suffix)));
};

Statistic.defaultProps = {
  decimalSeparator: '.',
  groupSeparator: ','
};
var WrapperStatistic = withConfigConsumer({
  prefixCls: 'statistic'
})(Statistic);
export default WrapperStatistic;