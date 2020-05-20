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
import RcRate from 'rc-rate';
import omit from 'omit.js';
import classNames from 'classnames';
import StarFilled from '@ant-design/icons/StarFilled';
import Tooltip from '../tooltip';
import { ConfigConsumer } from '../config-provider';

var Rate = /*#__PURE__*/function (_React$Component) {
  _inherits(Rate, _React$Component);

  var _super = _createSuper(Rate);

  function Rate() {
    var _this;

    _classCallCheck(this, Rate);

    _this = _super.apply(this, arguments);

    _this.saveRate = function (node) {
      _this.rcRate = node;
    };

    _this.characterRender = function (node, _ref) {
      var index = _ref.index;
      var tooltips = _this.props.tooltips;
      if (!tooltips) return node;
      return /*#__PURE__*/React.createElement(Tooltip, {
        title: tooltips[index]
      }, node);
    };

    _this.renderRate = function (_ref2) {
      var getPrefixCls = _ref2.getPrefixCls,
          direction = _ref2.direction;

      var _a = _this.props,
          prefixCls = _a.prefixCls,
          className = _a.className,
          restProps = __rest(_a, ["prefixCls", "className"]);

      var rateProps = omit(restProps, ['tooltips']);
      var ratePrefixCls = getPrefixCls('rate', prefixCls);
      var rateClassNames = classNames(className, _defineProperty({}, "".concat(ratePrefixCls, "-rtl"), direction === 'rtl'));
      return /*#__PURE__*/React.createElement(RcRate, _extends({
        ref: _this.saveRate,
        characterRender: _this.characterRender
      }, rateProps, {
        prefixCls: ratePrefixCls,
        className: rateClassNames
      }));
    };

    return _this;
  }

  _createClass(Rate, [{
    key: "focus",
    value: function focus() {
      this.rcRate.focus();
    }
  }, {
    key: "blur",
    value: function blur() {
      this.rcRate.blur();
    }
  }, {
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderRate);
    }
  }]);

  return Rate;
}(React.Component);

export { Rate as default };
Rate.defaultProps = {
  character: /*#__PURE__*/React.createElement(StarFilled, null)
};