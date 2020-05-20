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
import RcSteps from 'rc-steps';
import CheckOutlined from '@ant-design/icons/CheckOutlined';
import CloseOutlined from '@ant-design/icons/CloseOutlined';
import classNames from 'classnames';
import { ConfigConsumer } from '../config-provider';

var Steps = /*#__PURE__*/function (_React$Component) {
  _inherits(Steps, _React$Component);

  var _super = _createSuper(Steps);

  function Steps() {
    var _this;

    _classCallCheck(this, Steps);

    _this = _super.apply(this, arguments);

    _this.renderSteps = function (_ref) {
      var getPrefixCls = _ref.getPrefixCls,
          direction = _ref.direction;
      var prefixCls = getPrefixCls('steps', _this.props.prefixCls);
      var iconPrefix = getPrefixCls('', _this.props.iconPrefix);
      var className = classNames(_this.props.className, _defineProperty({}, "".concat(prefixCls, "-rtl"), direction === 'rtl'));
      var icons = {
        finish: /*#__PURE__*/React.createElement(CheckOutlined, {
          className: "".concat(prefixCls, "-finish-icon")
        }),
        error: /*#__PURE__*/React.createElement(CloseOutlined, {
          className: "".concat(prefixCls, "-error-icon")
        })
      };
      return /*#__PURE__*/React.createElement(RcSteps, _extends({
        icons: icons
      }, _this.props, {
        prefixCls: prefixCls,
        iconPrefix: iconPrefix,
        className: className
      }));
    };

    return _this;
  }

  _createClass(Steps, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(ConfigConsumer, null, this.renderSteps);
    }
  }]);

  return Steps;
}(React.Component);

export { Steps as default };
Steps.Step = RcSteps.Step;
Steps.defaultProps = {
  current: 0
};