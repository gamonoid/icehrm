import React from 'react';
import classNames from 'classnames';

var PopupInner = function PopupInner(props, ref) {
  var prefixCls = props.prefixCls,
      className = props.className,
      visible = props.visible,
      style = props.style,
      children = props.children,
      onMouseEnter = props.onMouseEnter,
      onMouseLeave = props.onMouseLeave,
      onMouseDown = props.onMouseDown,
      onTouchStart = props.onTouchStart;
  var childNode = children;

  if (React.Children.count(children) > 1) {
    childNode = React.createElement("div", {
      className: "".concat(prefixCls, "-content")
    }, children);
  }

  return React.createElement("div", {
    ref: ref,
    className: classNames(className, !visible && "".concat(props.hiddenClassName)),
    onMouseEnter: onMouseEnter,
    onMouseLeave: onMouseLeave,
    onMouseDown: onMouseDown,
    onTouchStart: onTouchStart,
    style: style
  }, childNode);
};

var RefPopupInner = React.forwardRef(PopupInner);
RefPopupInner.displayName = 'PopupInner';
export default RefPopupInner;