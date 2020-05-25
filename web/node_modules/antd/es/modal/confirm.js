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
import * as ReactDOM from 'react-dom';
import InfoCircleOutlined from '@ant-design/icons/InfoCircleOutlined';
import CheckCircleOutlined from '@ant-design/icons/CheckCircleOutlined';
import CloseCircleOutlined from '@ant-design/icons/CloseCircleOutlined';
import ExclamationCircleOutlined from '@ant-design/icons/ExclamationCircleOutlined';
import { getConfirmLocale } from './locale';
import { destroyFns } from './Modal';
import ConfirmDialog from './ConfirmDialog';
export default function confirm(config) {
  var div = document.createElement('div');
  document.body.appendChild(div); // eslint-disable-next-line no-use-before-define

  var currentConfig = _extends(_extends({}, config), {
    close: close,
    visible: true
  });

  function destroy() {
    var unmountResult = ReactDOM.unmountComponentAtNode(div);

    if (unmountResult && div.parentNode) {
      div.parentNode.removeChild(div);
    }

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    var triggerCancel = args.some(function (param) {
      return param && param.triggerCancel;
    });

    if (config.onCancel && triggerCancel) {
      config.onCancel.apply(config, args);
    }

    for (var i = 0; i < destroyFns.length; i++) {
      var fn = destroyFns[i]; // eslint-disable-next-line no-use-before-define

      if (fn === close) {
        destroyFns.splice(i, 1);
        break;
      }
    }
  }

  function render(_a) {
    var okText = _a.okText,
        cancelText = _a.cancelText,
        props = __rest(_a, ["okText", "cancelText"]);

    var runtimeLocale = getConfirmLocale();
    ReactDOM.render( /*#__PURE__*/React.createElement(ConfirmDialog, _extends({}, props, {
      okText: okText || (props.okCancel ? runtimeLocale.okText : runtimeLocale.justOkText),
      cancelText: cancelText || runtimeLocale.cancelText
    })), div);
  }

  function close() {
    for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
      args[_key2] = arguments[_key2];
    }

    currentConfig = _extends(_extends({}, currentConfig), {
      visible: false,
      afterClose: destroy.bind.apply(destroy, [this].concat(args))
    });
    render(currentConfig);
  }

  function update(newConfig) {
    currentConfig = _extends(_extends({}, currentConfig), newConfig);
    render(currentConfig);
  }

  render(currentConfig);
  destroyFns.push(close);
  return {
    destroy: close,
    update: update
  };
}
export function withWarn(props) {
  return _extends({
    type: 'warning',
    icon: /*#__PURE__*/React.createElement(ExclamationCircleOutlined, null),
    okCancel: false
  }, props);
}
export function withInfo(props) {
  return _extends({
    type: 'info',
    icon: /*#__PURE__*/React.createElement(InfoCircleOutlined, null),
    okCancel: false
  }, props);
}
export function withSuccess(props) {
  return _extends({
    type: 'success',
    icon: /*#__PURE__*/React.createElement(CheckCircleOutlined, null),
    okCancel: false
  }, props);
}
export function withError(props) {
  return _extends({
    type: 'error',
    icon: /*#__PURE__*/React.createElement(CloseCircleOutlined, null),
    okCancel: false
  }, props);
}
export function withConfirm(props) {
  return _extends({
    type: 'confirm',
    okCancel: true
  }, props);
}