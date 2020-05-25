function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

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
import RcPagination from 'rc-pagination';
import enUS from "rc-pagination/es/locale/en_US";
import classNames from 'classnames';
import LeftOutlined from '@ant-design/icons/LeftOutlined';
import RightOutlined from '@ant-design/icons/RightOutlined';
import DoubleLeftOutlined from '@ant-design/icons/DoubleLeftOutlined';
import DoubleRightOutlined from '@ant-design/icons/DoubleRightOutlined';
import ResponsiveObserve from '../_util/responsiveObserve';
import MiniSelect from './MiniSelect';
import Select from '../select';
import LocaleReceiver from '../locale-provider/LocaleReceiver';
import { ConfigConsumer } from '../config-provider';

var Pagination = /*#__PURE__*/function (_React$Component) {
  _inherits(Pagination, _React$Component);

  var _super = _createSuper(Pagination);

  function Pagination() {
    var _this;

    _classCallCheck(this, Pagination);

    _this = _super.apply(this, arguments);
    _this.inferredSmall = false;

    _this.getIconsProps = function (prefixCls, direction) {
      var prevIcon = /*#__PURE__*/React.createElement("a", {
        className: "".concat(prefixCls, "-item-link")
      }, /*#__PURE__*/React.createElement(LeftOutlined, null));
      var nextIcon = /*#__PURE__*/React.createElement("a", {
        className: "".concat(prefixCls, "-item-link")
      }, /*#__PURE__*/React.createElement(RightOutlined, null));
      var jumpPrevIcon = /*#__PURE__*/React.createElement("a", {
        className: "".concat(prefixCls, "-item-link")
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-item-container")
      }, /*#__PURE__*/React.createElement(DoubleLeftOutlined, {
        className: "".concat(prefixCls, "-item-link-icon")
      }), /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-item-ellipsis")
      }, "\u2022\u2022\u2022")));
      var jumpNextIcon = /*#__PURE__*/React.createElement("a", {
        className: "".concat(prefixCls, "-item-link")
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-item-container")
      }, /*#__PURE__*/React.createElement(DoubleRightOutlined, {
        className: "".concat(prefixCls, "-item-link-icon")
      }), /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-item-ellipsis")
      }, "\u2022\u2022\u2022"))); // change arrows direction in right-to-left direction

      if (direction === 'rtl') {
        var temp;
        temp = prevIcon;
        prevIcon = nextIcon;
        nextIcon = temp;
        temp = jumpPrevIcon;
        jumpPrevIcon = jumpNextIcon;
        jumpNextIcon = temp;
      }

      return {
        prevIcon: prevIcon,
        nextIcon: nextIcon,
        jumpPrevIcon: jumpPrevIcon,
        jumpNextIcon: jumpNextIcon
      };
    };

    _this.renderPagination = function (contextLocale) {
      var _a = _this.props,
          customizePrefixCls = _a.prefixCls,
          customizeSelectPrefixCls = _a.selectPrefixCls,
          className = _a.className,
          size = _a.size,
          customLocale = _a.locale,
          restProps = __rest(_a, ["prefixCls", "selectPrefixCls", "className", "size", "locale"]);

      var locale = _extends(_extends({}, contextLocale), customLocale);

      var isSmall = size === 'small' || _this.inferredSmall;
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, function (_ref) {
        var getPrefixCls = _ref.getPrefixCls,
            direction = _ref.direction;
        var prefixCls = getPrefixCls('pagination', customizePrefixCls);
        var selectPrefixCls = getPrefixCls('select', customizeSelectPrefixCls);
        var extendedClassName = classNames(className, _defineProperty({
          mini: isSmall
        }, "".concat(prefixCls, "-rtl"), direction === 'rtl'));
        return /*#__PURE__*/React.createElement(RcPagination, _extends({}, restProps, {
          prefixCls: prefixCls,
          selectPrefixCls: selectPrefixCls
        }, _this.getIconsProps(prefixCls, direction), {
          className: extendedClassName,
          selectComponentClass: isSmall ? MiniSelect : Select,
          locale: locale
        }));
      });
    };

    return _this;
  }

  _createClass(Pagination, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      this.token = ResponsiveObserve.subscribe(function (screens) {
        var xs = screens.xs;
        var _this2$props = _this2.props,
            size = _this2$props.size,
            responsive = _this2$props.responsive;
        var inferredSmall = !!(xs && !size && responsive);

        if (_this2.inferredSmall !== inferredSmall) {
          _this2.inferredSmall = inferredSmall;

          _this2.forceUpdate();
        }
      });
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      ResponsiveObserve.unsubscribe(this.token);
    }
  }, {
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(LocaleReceiver, {
        componentName: "Pagination",
        defaultLocale: enUS
      }, this.renderPagination);
    }
  }]);

  return Pagination;
}(React.Component);

export { Pagination as default };