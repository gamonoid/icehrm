"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _InkTabBarNode = _interopRequireDefault(require("./InkTabBarNode"));

var _TabBarTabsNode = _interopRequireDefault(require("./TabBarTabsNode"));

var _TabBarRootNode = _interopRequireDefault(require("./TabBarRootNode"));

var _ScrollableTabBarNode = _interopRequireDefault(require("./ScrollableTabBarNode"));

var _SaveRef = _interopRequireDefault(require("./SaveRef"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ScrollableInkTabBar = /*#__PURE__*/function (_React$Component) {
  _inherits(ScrollableInkTabBar, _React$Component);

  function ScrollableInkTabBar() {
    _classCallCheck(this, ScrollableInkTabBar);

    return _possibleConstructorReturn(this, _getPrototypeOf(ScrollableInkTabBar).apply(this, arguments));
  }

  _createClass(ScrollableInkTabBar, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          renderTabBarNode = _this$props.children,
          restProps = _objectWithoutProperties(_this$props, ["children"]);

      return _react.default.createElement(_SaveRef.default, null, function (saveRef, getRef) {
        return _react.default.createElement(_TabBarRootNode.default, _extends({
          saveRef: saveRef,
          getRef: getRef
        }, restProps), _react.default.createElement(_ScrollableTabBarNode.default, _extends({
          saveRef: saveRef,
          getRef: getRef
        }, restProps), _react.default.createElement(_TabBarTabsNode.default, _extends({
          saveRef: saveRef,
          renderTabBarNode: renderTabBarNode
        }, restProps)), _react.default.createElement(_InkTabBarNode.default, _extends({
          saveRef: saveRef,
          getRef: getRef
        }, restProps))));
      });
    }
  }]);

  return ScrollableInkTabBar;
}(_react.default.Component);

exports.default = ScrollableInkTabBar;