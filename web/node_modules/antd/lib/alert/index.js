"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var ReactDOM = _interopRequireWildcard(require("react-dom"));

var _CloseOutlined = _interopRequireDefault(require("@ant-design/icons/CloseOutlined"));

var _CheckCircleOutlined = _interopRequireDefault(require("@ant-design/icons/CheckCircleOutlined"));

var _ExclamationCircleOutlined = _interopRequireDefault(require("@ant-design/icons/ExclamationCircleOutlined"));

var _InfoCircleOutlined = _interopRequireDefault(require("@ant-design/icons/InfoCircleOutlined"));

var _CloseCircleOutlined = _interopRequireDefault(require("@ant-design/icons/CloseCircleOutlined"));

var _CheckCircleFilled = _interopRequireDefault(require("@ant-design/icons/CheckCircleFilled"));

var _ExclamationCircleFilled = _interopRequireDefault(require("@ant-design/icons/ExclamationCircleFilled"));

var _InfoCircleFilled = _interopRequireDefault(require("@ant-design/icons/InfoCircleFilled"));

var _CloseCircleFilled = _interopRequireDefault(require("@ant-design/icons/CloseCircleFilled"));

var _rcAnimate = _interopRequireDefault(require("rc-animate"));

var _classnames = _interopRequireDefault(require("classnames"));

var _configProvider = require("../config-provider");

var _getDataOrAriaProps = _interopRequireDefault(require("../_util/getDataOrAriaProps"));

var _ErrorBoundary = _interopRequireDefault(require("./ErrorBoundary"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

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

function noop() {}

var iconMapFilled = {
  success: _CheckCircleFilled["default"],
  info: _InfoCircleFilled["default"],
  error: _CloseCircleFilled["default"],
  warning: _ExclamationCircleFilled["default"]
};
var iconMapOutlined = {
  success: _CheckCircleOutlined["default"],
  info: _InfoCircleOutlined["default"],
  error: _CloseCircleOutlined["default"],
  warning: _ExclamationCircleOutlined["default"]
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

      var alertCls = (0, _classnames["default"])(prefixCls, "".concat(prefixCls, "-").concat(type), (_classNames = {}, _defineProperty(_classNames, "".concat(prefixCls, "-closing"), closing), _defineProperty(_classNames, "".concat(prefixCls, "-with-description"), !!description), _defineProperty(_classNames, "".concat(prefixCls, "-no-icon"), !showIcon), _defineProperty(_classNames, "".concat(prefixCls, "-banner"), !!banner), _defineProperty(_classNames, "".concat(prefixCls, "-closable"), closable), _defineProperty(_classNames, "".concat(prefixCls, "-rtl"), direction === 'rtl'), _classNames), className);
      var closeIcon = closable ? /*#__PURE__*/React.createElement("button", {
        type: "button",
        onClick: _this.handleClose,
        className: "".concat(prefixCls, "-close-icon"),
        tabIndex: 0
      }, closeText ? /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-close-text")
      }, closeText) : /*#__PURE__*/React.createElement(_CloseOutlined["default"], null)) : null;
      var dataOrAriaProps = (0, _getDataOrAriaProps["default"])(_this.props);
      var iconNode = icon && (React.isValidElement(icon) ? React.cloneElement(icon, {
        className: (0, _classnames["default"])("".concat(prefixCls, "-icon"), _defineProperty({}, icon.props.className, icon.props.className))
      }) : /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-icon")
      }, icon)) || React.createElement(iconType, {
        className: "".concat(prefixCls, "-icon")
      });
      return closed ? null : /*#__PURE__*/React.createElement(_rcAnimate["default"], {
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
      return /*#__PURE__*/React.createElement(_configProvider.ConfigConsumer, null, this.renderAlert);
    }
  }]);

  return Alert;
}(React.Component);

exports["default"] = Alert;
Alert.ErrorBoundary = _ErrorBoundary["default"];