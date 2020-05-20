function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import * as React from 'react';
import classnames from 'classnames';
import CheckCircleFilled from '@ant-design/icons/CheckCircleFilled';
import CloseCircleFilled from '@ant-design/icons/CloseCircleFilled';
import ExclamationCircleFilled from '@ant-design/icons/ExclamationCircleFilled';
import WarningFilled from '@ant-design/icons/WarningFilled';
import { ConfigConsumer } from '../config-provider';
import warning from '../_util/warning';
import noFound from './noFound';
import serverError from './serverError';
import unauthorized from './unauthorized';
export var IconMap = {
  success: CheckCircleFilled,
  error: CloseCircleFilled,
  info: ExclamationCircleFilled,
  warning: WarningFilled
};
export var ExceptionMap = {
  '404': noFound,
  '500': serverError,
  '403': unauthorized
}; // ExceptionImageMap keys

var ExceptionStatus = Object.keys(ExceptionMap);
/**
 * render icon
 * if ExceptionStatus includes ,render svg image
 * else render iconNode
 * @param prefixCls
 * @param {status, icon}
 */

var renderIcon = function renderIcon(prefixCls, _ref) {
  var status = _ref.status,
      icon = _ref.icon;
  var className = classnames("".concat(prefixCls, "-icon"));
  warning(!(typeof icon === 'string' && icon.length > 2), 'Result', "`icon` is using ReactNode instead of string naming in v4. Please check `".concat(icon, "` at https://ant.design/components/icon"));

  if (ExceptionStatus.includes("".concat(status))) {
    var SVGComponent = ExceptionMap[status];
    return /*#__PURE__*/React.createElement("div", {
      className: "".concat(className, " ").concat(prefixCls, "-image")
    }, /*#__PURE__*/React.createElement(SVGComponent, null));
  }

  var iconNode = React.createElement(IconMap[status]);
  return /*#__PURE__*/React.createElement("div", {
    className: className
  }, icon || iconNode);
};

var renderExtra = function renderExtra(prefixCls, _ref2) {
  var extra = _ref2.extra;
  return extra && /*#__PURE__*/React.createElement("div", {
    className: "".concat(prefixCls, "-extra")
  }, extra);
};

var Result = function Result(props) {
  return /*#__PURE__*/React.createElement(ConfigConsumer, null, function (_ref3) {
    var getPrefixCls = _ref3.getPrefixCls,
        direction = _ref3.direction;
    var customizePrefixCls = props.prefixCls,
        customizeClassName = props.className,
        subTitle = props.subTitle,
        title = props.title,
        style = props.style,
        children = props.children,
        status = props.status;
    var prefixCls = getPrefixCls('result', customizePrefixCls);
    var className = classnames(prefixCls, "".concat(prefixCls, "-").concat(status), customizeClassName, _defineProperty({}, "".concat(prefixCls, "-rtl"), direction === 'rtl'));
    return /*#__PURE__*/React.createElement("div", {
      className: className,
      style: style
    }, renderIcon(prefixCls, props), /*#__PURE__*/React.createElement("div", {
      className: "".concat(prefixCls, "-title")
    }, title), subTitle && /*#__PURE__*/React.createElement("div", {
      className: "".concat(prefixCls, "-subtitle")
    }, subTitle), children && /*#__PURE__*/React.createElement("div", {
      className: "".concat(prefixCls, "-content")
    }, children), renderExtra(prefixCls, props));
  });
};

Result.defaultProps = {
  status: 'info'
};
Result.PRESENTED_IMAGE_403 = ExceptionMap[403];
Result.PRESENTED_IMAGE_404 = ExceptionMap[404];
Result.PRESENTED_IMAGE_500 = ExceptionMap[500];
export default Result;