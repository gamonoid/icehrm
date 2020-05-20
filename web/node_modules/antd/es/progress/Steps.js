import * as React from 'react';

var Steps = function Steps(props) {
  var _props$size = props.size,
      size = _props$size === void 0 ? 'default' : _props$size,
      steps = props.steps,
      _props$percent = props.percent,
      percent = _props$percent === void 0 ? 0 : _props$percent,
      _props$strokeWidth = props.strokeWidth,
      strokeWidth = _props$strokeWidth === void 0 ? 8 : _props$strokeWidth,
      strokeColor = props.strokeColor,
      prefixCls = props.prefixCls,
      children = props.children;

  var getStyledSteps = function getStyledSteps() {
    var current = Math.floor(steps * (percent / 100));
    var stepWidth = size === 'small' ? 2 : 14;
    var styleSteps = [];

    for (var i = 0; i < steps; i++) {
      var color = void 0;

      if (i <= current - 1) {
        color = strokeColor;
      }

      var stepStyle = {
        backgroundColor: "".concat(color),
        width: "".concat(stepWidth, "px"),
        height: "".concat(strokeWidth, "px")
      };
      styleSteps.push( /*#__PURE__*/React.createElement("div", {
        key: i,
        className: "".concat(prefixCls, "-steps-item"),
        style: stepStyle
      }));
    }

    return styleSteps;
  };

  return /*#__PURE__*/React.createElement("div", {
    className: "".concat(prefixCls, "-steps-outer")
  }, getStyledSteps(), children);
};

export default Steps;