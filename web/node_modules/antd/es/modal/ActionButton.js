function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Button from '../button';

var ActionButton = /*#__PURE__*/function (_React$Component) {
  _inherits(ActionButton, _React$Component);

  var _super = _createSuper(ActionButton);

  function ActionButton() {
    var _this;

    _classCallCheck(this, ActionButton);

    _this = _super.apply(this, arguments);
    _this.state = {
      loading: false
    };

    _this.onClick = function () {
      var _this$props = _this.props,
          actionFn = _this$props.actionFn,
          closeModal = _this$props.closeModal;

      if (_this.clicked) {
        return;
      }

      _this.clicked = true;

      if (!actionFn) {
        closeModal();
        return;
      }

      var returnValueOfOnOk;

      if (actionFn.length) {
        returnValueOfOnOk = actionFn(closeModal); // https://github.com/ant-design/ant-design/issues/23358

        _this.clicked = false;
      } else {
        returnValueOfOnOk = actionFn();

        if (!returnValueOfOnOk) {
          closeModal();
          return;
        }
      }

      _this.handlePromiseOnOk(returnValueOfOnOk);
    };

    return _this;
  }

  _createClass(ActionButton, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      if (this.props.autoFocus) {
        var $this = ReactDOM.findDOMNode(this);
        this.timeoutId = setTimeout(function () {
          return $this.focus();
        });
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      clearTimeout(this.timeoutId);
    }
  }, {
    key: "handlePromiseOnOk",
    value: function handlePromiseOnOk(returnValueOfOnOk) {
      var _this2 = this;

      var closeModal = this.props.closeModal;

      if (!returnValueOfOnOk || !returnValueOfOnOk.then) {
        return;
      }

      this.setState({
        loading: true
      });
      returnValueOfOnOk.then(function () {
        // It's unnecessary to set loading=false, for the Modal will be unmounted after close.
        // this.setState({ loading: false });
        closeModal.apply(void 0, arguments);
      }, function (e) {
        // Emit error when catch promise reject
        // eslint-disable-next-line no-console
        console.error(e); // See: https://github.com/ant-design/ant-design/issues/6183

        _this2.setState({
          loading: false
        });

        _this2.clicked = false;
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
          type = _this$props2.type,
          children = _this$props2.children,
          buttonProps = _this$props2.buttonProps;
      var loading = this.state.loading;
      return /*#__PURE__*/React.createElement(Button, _extends({
        type: type,
        onClick: this.onClick,
        loading: loading
      }, buttonProps), children);
    }
  }]);

  return ActionButton;
}(React.Component);

export { ActionButton as default };