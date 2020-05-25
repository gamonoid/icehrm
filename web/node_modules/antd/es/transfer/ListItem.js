function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import * as React from 'react';
import classNames from 'classnames';
import Checkbox from '../checkbox';

var ListItem = function ListItem(props) {
  var _classNames;

  var renderedText = props.renderedText,
      renderedEl = props.renderedEl,
      item = props.item,
      checked = props.checked,
      disabled = props.disabled,
      prefixCls = props.prefixCls,
      onClick = props.onClick;
  var className = classNames((_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-content-item"), true), _defineProperty(_classNames, "".concat(prefixCls, "-content-item-disabled"), disabled || item.disabled), _defineProperty(_classNames, "".concat(prefixCls, "-content-item-checked"), checked), _classNames));
  var title;

  if (typeof renderedText === 'string' || typeof renderedText === 'number') {
    title = String(renderedText);
  }

  var listItem = /*#__PURE__*/React.createElement("li", {
    className: className,
    title: title,
    onClick: disabled || item.disabled ? undefined : function () {
      return onClick(item);
    }
  }, /*#__PURE__*/React.createElement(Checkbox, {
    checked: checked,
    disabled: disabled || item.disabled
  }), /*#__PURE__*/React.createElement("span", {
    className: "".concat(prefixCls, "-content-item-text")
  }, renderedEl));
  return listItem;
};

export default React.memo(ListItem);