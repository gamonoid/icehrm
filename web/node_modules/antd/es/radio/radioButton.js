function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var __rest = this && this.__rest || function (s, e) {
  var t = {};

  for (var p in s) {
    if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0) t[p] = s[p];
  }

  if (s != null && typeof Object.getOwnPropertySymbols === "function") for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
    if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i])) t[p[i]] = s[p[i]];
  }
  return t;
};

import * as React from 'react';
import Radio from './radio';
import { ConfigConsumer } from '../config-provider';
import RadioGroupContext from './context';

var RadioButton = function RadioButton(props, ref) {
  var radioGroupContext = React.useContext(RadioGroupContext);
  return /*#__PURE__*/React.createElement(ConfigConsumer, null, function (_ref) {
    var getPrefixCls = _ref.getPrefixCls;

    var customizePrefixCls = props.prefixCls,
        radioProps = __rest(props, ["prefixCls"]);

    var prefixCls = getPrefixCls('radio-button', customizePrefixCls);

    if (radioGroupContext) {
      radioProps.checked = props.value === radioGroupContext.value;
      radioProps.disabled = props.disabled || radioGroupContext.disabled;
    }

    return /*#__PURE__*/React.createElement(Radio, _extends({
      prefixCls: prefixCls
    }, radioProps, {
      type: "radio",
      ref: ref
    }));
  });
};

export default React.forwardRef(RadioButton);