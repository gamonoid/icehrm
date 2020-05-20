function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import * as React from 'react';
import classNames from 'classnames';

var Element = function Element(props) {
  var _classNames, _classNames2;

  var prefixCls = props.prefixCls,
      className = props.className,
      style = props.style,
      size = props.size,
      shape = props.shape;
  var sizeCls = classNames((_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-lg"), size === 'large'), _defineProperty(_classNames, "".concat(prefixCls, "-sm"), size === 'small'), _classNames));
  var shapeCls = classNames((_classNames2 = {}, _defineProperty(_classNames2, "".concat(prefixCls, "-circle"), shape === 'circle'), _defineProperty(_classNames2, "".concat(prefixCls, "-square"), shape === 'square'), _defineProperty(_classNames2, "".concat(prefixCls, "-round"), shape === 'round'), _classNames2));
  var sizeStyle = typeof size === 'number' ? {
    width: size,
    height: size,
    lineHeight: "".concat(size, "px")
  } : {};
  return /*#__PURE__*/React.createElement("span", {
    className: classNames(prefixCls, className, sizeCls, shapeCls),
    style: _extends(_extends({}, sizeStyle), style)
  });
};

export default Element;