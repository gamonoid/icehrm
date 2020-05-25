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
import { List } from 'rc-field-form';
import warning from '../_util/warning';

var FormList = function FormList(_a) {
  var children = _a.children,
      props = __rest(_a, ["children"]);

  warning(!!props.name, 'Form.List', 'Miss `name` prop.');
  return /*#__PURE__*/React.createElement(List, props, function (fields, operation) {
    return children(fields.map(function (field) {
      return _extends(_extends({}, field), {
        fieldKey: field.key
      });
    }), operation);
  });
};

export default FormList;