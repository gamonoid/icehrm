function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import * as React from 'react';
import { Circle as RCCircle } from 'rc-progress';
import classNames from 'classnames';
import { validProgress } from './utils';

function getPercentage(_ref) {
  var percent = _ref.percent,
      successPercent = _ref.successPercent;
  var ptg = validProgress(percent);

  if (!successPercent) {
    return ptg;
  }

  var successPtg = validProgress(successPercent);
  return [successPercent, validProgress(ptg - successPtg)];
}

function getStrokeColor(_ref2) {
  var successPercent = _ref2.successPercent,
      strokeColor = _ref2.strokeColor;
  var color = strokeColor || null;

  if (!successPercent) {
    return color;
  }

  return [null, color];
}

var Circle = function Circle(props) {
  var prefixCls = props.prefixCls,
      width = props.width,
      strokeWidth = props.strokeWidth,
      trailColor = props.trailColor,
      strokeLinecap = props.strokeLinecap,
      gapPosition = props.gapPosition,
      gapDegree = props.gapDegree,
      type = props.type,
      children = props.children;
  var circleSize = width || 120;
  var circleStyle = {
    width: circleSize,
    height: circleSize,
    fontSize: circleSize * 0.15 + 6
  };
  var circleWidth = strokeWidth || 6;
  var gapPos = gapPosition || type === 'dashboard' && 'bottom' || 'top'; // Support gapDeg = 0 when type = 'dashboard'

  var gapDeg;

  if (gapDegree || gapDegree === 0) {
    gapDeg = gapDegree;
  } else if (type === 'dashboard') {
    gapDeg = 75;
  } // using className to style stroke color


  var strokeColor = getStrokeColor(props);
  var isGradient = Object.prototype.toString.call(strokeColor) === '[object Object]';
  var wrapperClassName = classNames("".concat(prefixCls, "-inner"), _defineProperty({}, "".concat(prefixCls, "-circle-gradient"), isGradient));
  return /*#__PURE__*/React.createElement("div", {
    className: wrapperClassName,
    style: circleStyle
  }, /*#__PURE__*/React.createElement(RCCircle, {
    percent: getPercentage(props),
    strokeWidth: circleWidth,
    trailWidth: circleWidth,
    strokeColor: strokeColor,
    strokeLinecap: strokeLinecap,
    trailColor: trailColor,
    prefixCls: prefixCls,
    gapDegree: gapDeg,
    gapPosition: gapPos
  }), children);
};

export default Circle;