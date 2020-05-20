function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { return function () { var Super = _getPrototypeOf(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import CloseOutlined from '@ant-design/icons/CloseOutlined';
import CheckCircleOutlined from '@ant-design/icons/CheckCircleOutlined';
import ExclamationCircleOutlined from '@ant-design/icons/ExclamationCircleOutlined';
import InfoCircleOutlined from '@ant-design/icons/InfoCircleOutlined';
import CloseCircleOutlined from '@ant-design/icons/CloseCircleOutlined';
import CheckCircleFilled from '@ant-design/icons/CheckCircleFilled';
import ExclamationCircleFilled from '@ant-design/icons/ExclamationCircleFilled';
import InfoCircleFilled from '@ant-design/icons/InfoCircleFilled';
import CloseCircleFilled from '@ant-design/icons/CloseCircleFilled';
import Animate from 'rc-animate';
import classNames from 'classnames';
import { ConfigConsumer } from '../config-provider';
import getDataOrAriaProps from '../_util/getDataOrAriaProps';
import ErrorBoundary from './ErrorBoundary';

function noop() {}

var iconMapFilled = {
  success: CheckCircleFilled,
  info: InfoCircleFilled,
  error: CloseCircleFilled,
  warning: ExclamationCircleFilled
};
var iconMapOutlined = {
  success: CheckCircleOutlined,
  info: InfoCircleOutlined,
  error: CloseCircleOutlined,
  warning: ExclamationCircleOutlined
};

var Alert = /*#__PURE__*/function (_React$Component) {
  _inherits(Alert, _React$Component);

  var _super = _createSuper(Alert);

  function Alert() {
    var _this;

    _classCallCheck(this, Alert);

    _this = _super.apply(this, arguments);
    _this.state = {
      closing: false,
      closed: false
    };

    _this.handleClose = function (e) {
      e.preventDefault();
      var dom = ReactDOM.findDOMNode(_assertThisInitialized(_this));
      dom.style.height = "".concat(dom.offsetHeight, "px"); // Magic code
      // 重复一次后才能正确设置 height

      dom.style.height = "".concat(dom.offsetHeight, "px");

      _this.setState({
        closing: true
      });

      (_this.props.onClose || noop)(e);
    };

    _this.animationEnd = function () {
      _this.setState({
        closing: false,
        closed: true
      });

      (_this.props.afterClose || noop)();
    };

    _this.renderAlert = function (_ref) {
      var _classNames;

      var getPrefixCls = _ref.getPrefixCls,
          direction = _ref.direction;
      var _this$props = _this.props,
          description = _this$props.description,
          customizePrefixCls = _this$props.prefixCls,
          message = _this$props.message,
          closeText = _this$props.closeText,
          banner = _this$props.banner,
          _this$props$className = _this$props.className,
          className = _this$props$className === void 0 ? '' : _this$props$className,
          style = _this$props.style,
          icon = _this$props.icon,
          onMouseEnter = _this$props.onMouseEnter,
          onMouseLeave = _this$props.onMouseLeave,
          onClick = _this$props.onClick;
      var _this$props2 = _this.props,
          closable = _this$props2.closable,
          type = _this$props2.type,
          showIcon = _this$props2.showIcon;
      var _this$state = _this.state,
          closing = _this$state.closing,
          closed = _this$state.closed;
      var prefixCls = getPrefixCls('alert', customizePrefixCls); // banner模式默认有 Icon

      showIcon = banner && showIcon === undefined ? true : showIcon; // banner模式默认为警告

      type = banner && type === undefined ? 'warning' : type || 'info'; // use outline icon in alert with description

      var iconType = (description ? iconMapOutlined : iconMapFilled)[type] || null; // closeable when closeText is assigned

      if (closeText) {
        closable = true;
      }

      var alertCls = classNames(prefixCls, "".concat(prefixCls, "-").concat(type), (_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-closing"), closing), _defineProperty(_classNames, "".concat(prefixCls, "-with-description"), !!description), _defineProperty(_classNames, "".concat(prefixCls, "-no-icon"), !showIcon), _defineProperty(_classNames, "".concat(prefixCls, "-banner"), !!banner), _defineProperty(_classNames, "".concat(prefixCls, "-closable"), closable), _defineProperty(_classNames, "".concat(prefixCls, "-rtl"), direction === 'rtl'), _classNames), className);
      var closeIcon = closable ? /*#__PURE__*/React.createElement("button", {
        type: "button",
        onClick: _this.handleClose,
        className: "".concat(prefixCls, "-close-icon"),
        tabIndex: 0
      }, closeText ? /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-close-text")
      }, closeText) : /*#__PURE__*/React.createElement(CloseOutlined, null)) : null;
      var dataOrAriaProps = getDataOrAriaProps(_this.props);
      var iconNode = icon && (React.isValidElement(icon) ? React.cloneElement(icon, {
        className: classNames("".concat(prefixCls, "-icon"), _defineProperty({}, icon.props.className, icon.props.className))
      }) : /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-icon")
      }, icon)) || React.createElement(iconType, {
        className: "".concat(prefixCls, "-icon")
      });
      return closed ? null : /*#__PURE__*/React.createElement(Animate, {
        component: "",
        showProp: "data-show",
        transitionName: "".concat(prefixCls, "-slide-up"),
        onEnd: _this.animationEnd
      }, /*#__PURE__*/React.createElement("div", _extends({
        "data-show": !closing,
        className: alertCls,
        style: style,
        onMouseEnter: onMouseEnter,
        onMouseLeave: onMouseLeave,
        onClick: onClick
      }, dataOrAriaProps), showIcon ? iconNode : null, /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-message")
      }, message), /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-description")
      }, description), closeIcon));
    };

    return _this;
  }

  _createClass(Alert, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderAlert);
    }
  }]);

  return Alert;
}(React.Component);

export { Alert as default };
Alert.ErrorBoundary = ErrorBoundary;