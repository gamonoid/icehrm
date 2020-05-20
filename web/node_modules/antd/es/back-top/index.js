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

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var __decorate = this && this.__decorate || function (decorators, target, key, desc) {
  var c = arguments.length,
      r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc,
      d;
  if ((typeof Reflect === "undefined" ? "undefined" : _typeof(Reflect)) === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);else for (var i = decorators.length - 1; i >= 0; i--) {
    if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
  }
  return c > 3 && r && Object.defineProperty(target, key, r), r;
};

import * as React from 'react';
import Animate from 'rc-animate';
import addEventListener from "rc-util/es/Dom/addEventListener";
import classNames from 'classnames';
import omit from 'omit.js';
import { throttleByAnimationFrameDecorator } from '../_util/throttleByAnimationFrame';
import { ConfigConsumer } from '../config-provider';
import getScroll from '../_util/getScroll';
import scrollTo from '../_util/scrollTo';

var BackTop = /*#__PURE__*/function (_React$Component) {
  _inherits(BackTop, _React$Component);

  var _super = _createSuper(BackTop);

  function BackTop() {
    var _this;

    _classCallCheck(this, BackTop);

    _this = _super.apply(this, arguments);
    _this.state = {
      visible: false
    };

    _this.getDefaultTarget = function () {
      return _this.node && _this.node.ownerDocument ? _this.node.ownerDocument : window;
    };

    _this.saveDivRef = function (node) {
      _this.node = node;
    };

    _this.scrollToTop = function (e) {
      var _this$props = _this.props,
          onClick = _this$props.onClick,
          target = _this$props.target;
      scrollTo(0, {
        getContainer: target || _this.getDefaultTarget
      });

      if (typeof onClick === 'function') {
        onClick(e);
      }
    };

    _this.renderBackTop = function (_ref) {
      var getPrefixCls = _ref.getPrefixCls,
          direction = _ref.direction;
      var _this$props2 = _this.props,
          customizePrefixCls = _this$props2.prefixCls,
          _this$props2$classNam = _this$props2.className,
          className = _this$props2$classNam === void 0 ? '' : _this$props2$classNam;
      var prefixCls = getPrefixCls('back-top', customizePrefixCls);
      var classString = classNames(prefixCls, className, _defineProperty({}, "".concat(prefixCls, "-rtl"), direction === 'rtl')); // fix https://fb.me/react-unknown-prop

      var divProps = omit(_this.props, ['prefixCls', 'className', 'children', 'visibilityHeight', 'target', 'visible']);
      return /*#__PURE__*/React.createElement("div", _extends({}, divProps, {
        className: classString,
        onClick: _this.scrollToTop,
        ref: _this.saveDivRef
      }), _this.renderChildren({
        prefixCls: prefixCls
      }));
    };

    return _this;
  }

  _createClass(BackTop, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      this.bindScrollEvent();
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      var target = this.props.target;

      if (prevProps.target !== target) {
        this.bindScrollEvent();
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      if (this.scrollEvent) {
        this.scrollEvent.remove();
      }

      this.handleScroll.cancel();
    }
  }, {
    key: "bindScrollEvent",
    value: function bindScrollEvent() {
      var _this2 = this;

      if (this.scrollEvent) {
        this.scrollEvent.remove();
      }

      var target = this.props.target;
      var getTarget = target || this.getDefaultTarget;
      var container = getTarget();
      this.scrollEvent = addEventListener(container, 'scroll', function (e) {
        _this2.handleScroll(e);
      });
      this.handleScroll({
        target: container
      });
    }
  }, {
    key: "getVisible",
    value: function getVisible() {
      if ('visible' in this.props) {
        return this.props.visible;
      }

      return this.state.visible;
    }
  }, {
    key: "handleScroll",
    value: function handleScroll(e) {
      var _this$props$visibilit = this.props.visibilityHeight,
          visibilityHeight = _this$props$visibilit === void 0 ? 0 : _this$props$visibilit;
      var scrollTop = getScroll(e.target, true);
      this.setState({
        visible: scrollTop > visibilityHeight
      });
    }
  }, {
    key: "renderChildren",
    value: function renderChildren(_ref2) {
      var prefixCls = _ref2.prefixCls;
      var children = this.props.children;
      var defaultElement = /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-content")
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-icon")
      }));
      return /*#__PURE__*/React.createElement(Animate, {
        component: "",
        transitionName: "fade"
      }, this.getVisible() ? /*#__PURE__*/React.createElement("div", null, children || defaultElement) : null);
    }
  }, {
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderBackTop);
    }
  }]);

  return BackTop;
}(React.Component);

export { BackTop as default };
BackTop.defaultProps = {
  visibilityHeight: 400
};

__decorate([throttleByAnimationFrameDecorator()], BackTop.prototype, "handleScroll", null);