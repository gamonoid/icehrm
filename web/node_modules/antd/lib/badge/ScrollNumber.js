"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _omit = _interopRequireDefault(require("omit.js"));

var _classnames = _interopRequireDefault(require("classnames"));

var _configProvider = require("../config-provider");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

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

function getNumberArray(num) {
  return num ? num.toString().split('').reverse().map(function (i) {
    var current = Number(i);
    return isNaN(current) ? i : current;
  }) : [];
}

function renderNumberList(position, className) {
  var childrenToReturn = [];

  for (var i = 0; i < 30; i++) {
    childrenToReturn.push( /*#__PURE__*/React.createElement("p", {
      key: i.toString(),
      className: (0, _classnames["default"])(className, {
        current: position === i
      })
    }, i % 10));
  }

  return childrenToReturn;
}

var ScrollNumber = /*#__PURE__*/function (_React$Component) {
  _inherits(ScrollNumber, _React$Component);

  var _super = _createSuper(ScrollNumber);

  function ScrollNumber(props) {
    var _this;

    _classCallCheck(this, ScrollNumber);

    _this = _super.call(this, props);

    _this.onAnimated = function () {
      var onAnimated = _this.props.onAnimated;

      if (onAnimated) {
        onAnimated();
      }
    };

    _this.renderScrollNumber = function (_ref) {
      var getPrefixCls = _ref.getPrefixCls;
      var _this$props = _this.props,
          customizePrefixCls = _this$props.prefixCls,
          className = _this$props.className,
          style = _this$props.style,
          title = _this$props.title,
          _this$props$component = _this$props.component,
          component = _this$props$component === void 0 ? 'sup' : _this$props$component,
          displayComponent = _this$props.displayComponent; // fix https://fb.me/react-unknown-prop

      var restProps = (0, _omit["default"])(_this.props, ['count', 'onAnimated', 'component', 'prefixCls', 'displayComponent']);
      var prefixCls = getPrefixCls('scroll-number', customizePrefixCls);

      var newProps = _extends(_extends({}, restProps), {
        className: (0, _classnames["default"])(prefixCls, className),
        title: title
      }); // allow specify the border
      // mock border-color by box-shadow for compatible with old usage:
      // <Badge count={4} style={{ backgroundColor: '#fff', color: '#999', borderColor: '#d9d9d9' }} />


      if (style && style.borderColor) {
        newProps.style = _extends(_extends({}, style), {
          boxShadow: "0 0 0 1px ".concat(style.borderColor, " inset")
        });
      }

      if (displayComponent) {
        return React.cloneElement(displayComponent, {
          className: (0, _classnames["default"])("".concat(prefixCls, "-custom-component"), displayComponent.props && displayComponent.props.className)
        });
      }

      return React.createElement(component, newProps, _this.renderNumberElement(prefixCls));
    };

    _this.state = {
      animateStarted: true,
      count: props.count
    };
    return _this;
  }

  _createClass(ScrollNumber, [{
    key: "componentDidUpdate",
    value: function componentDidUpdate(_, prevState) {
      var _this2 = this;

      this.lastCount = prevState.count;
      var animateStarted = this.state.animateStarted;

      if (animateStarted) {
        this.clearTimeout(); // Let browser has time to reset the scroller before actually
        // performing the transition.

        this.timeout = setTimeout(function () {
          // eslint-disable-next-line react/no-did-update-set-state
          _this2.setState(function (__, props) {
            return {
              animateStarted: false,
              count: props.count
            };
          }, _this2.onAnimated);
        });
      }
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      this.clearTimeout();
    }
  }, {
    key: "getPositionByNum",
    value: function getPositionByNum(num, i) {
      var count = this.state.count;
      var currentCount = Math.abs(Number(count));
      var lastCount = Math.abs(Number(this.lastCount));
      var currentDigit = Math.abs(getNumberArray(this.state.count)[i]);
      var lastDigit = Math.abs(getNumberArray(this.lastCount)[i]);

      if (this.state.animateStarted) {
        return 10 + num;
      } // 同方向则在同一侧切换数字


      if (currentCount > lastCount) {
        if (currentDigit >= lastDigit) {
          return 10 + num;
        }

        return 20 + num;
      }

      if (currentDigit <= lastDigit) {
        return 10 + num;
      }

      return num;
    }
  }, {
    key: "renderCurrentNumber",
    value: function renderCurrentNumber(prefixCls, num, i) {
      if (typeof num === 'number') {
        var position = this.getPositionByNum(num, i);
        var removeTransition = this.state.animateStarted || getNumberArray(this.lastCount)[i] === undefined;
        return React.createElement('span', {
          className: "".concat(prefixCls, "-only"),
          style: {
            transition: removeTransition ? 'none' : undefined,
            msTransform: "translateY(".concat(-position * 100, "%)"),
            WebkitTransform: "translateY(".concat(-position * 100, "%)"),
            transform: "translateY(".concat(-position * 100, "%)")
          },
          key: i
        }, renderNumberList(position, "".concat(prefixCls, "-only-unit")));
      }

      return /*#__PURE__*/React.createElement("span", {
        key: "symbol",
        className: "".concat(prefixCls, "-symbol")
      }, num);
    }
  }, {
    key: "renderNumberElement",
    value: function renderNumberElement(prefixCls) {
      var _this3 = this;

      var count = this.state.count;

      if (count && Number(count) % 1 === 0) {
        return getNumberArray(count).map(function (num, i) {
          return _this3.renderCurrentNumber(prefixCls, num, i);
        }).reverse();
      }

      return count;
    }
  }, {
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(_configProvider.ConfigConsumer, null, this.renderScrollNumber);
    }
  }, {
    key: "clearTimeout",
    value: function (_clearTimeout) {
      function clearTimeout() {
        return _clearTimeout.apply(this, arguments);
      }

      clearTimeout.toString = function () {
        return _clearTimeout.toString();
      };

      return clearTimeout;
    }(function () {
      if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = undefined;
      }
    })
  }], [{
    key: "getDerivedStateFromProps",
    value: function getDerivedStateFromProps(nextProps, nextState) {
      if ('count' in nextProps) {
        if (nextState.count === nextProps.count) {
          return null;
        }

        return {
          animateStarted: true
        };
      }

      return null;
    }
  }]);

  return ScrollNumber;
}(React.Component);

ScrollNumber.defaultProps = {
  count: null,
  onAnimated: function onAnimated() {}
};
var _default = ScrollNumber;
exports["default"] = _default;