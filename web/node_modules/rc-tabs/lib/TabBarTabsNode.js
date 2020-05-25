"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _warning = _interopRequireDefault(require("warning"));

var _utils = require("./utils");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var TabBarTabsNode = /*#__PURE__*/function (_React$Component) {
  _inherits(TabBarTabsNode, _React$Component);

  function TabBarTabsNode() {
    _classCallCheck(this, TabBarTabsNode);

    return _possibleConstructorReturn(this, _getPrototypeOf(TabBarTabsNode).apply(this, arguments));
  }

  _createClass(TabBarTabsNode, [{
    key: "render",
    value: function render() {
      var _this = this;

      var _this$props = this.props,
          children = _this$props.panels,
          activeKey = _this$props.activeKey,
          prefixCls = _this$props.prefixCls,
          tabBarGutter = _this$props.tabBarGutter,
          saveRef = _this$props.saveRef,
          tabBarPosition = _this$props.tabBarPosition,
          renderTabBarNode = _this$props.renderTabBarNode,
          direction = _this$props.direction;
      var rst = [];

      _react.default.Children.forEach(children, function (child, index) {
        if (!child) {
          return;
        }

        var key = child.key;
        var cls = activeKey === key ? "".concat(prefixCls, "-tab-active") : '';
        cls += " ".concat(prefixCls, "-tab");
        var events = {};

        if (child.props.disabled) {
          cls += " ".concat(prefixCls, "-tab-disabled");
        } else {
          events = {
            onClick: _this.props.onTabClick.bind(_this, key)
          };
        }

        var ref = {};

        if (activeKey === key) {
          ref.ref = saveRef('activeTab');
        }

        var gutter = tabBarGutter && index === children.length - 1 ? 0 : tabBarGutter;
        var marginProperty = direction === 'rtl' ? 'marginLeft' : 'marginRight';

        var style = _defineProperty({}, (0, _utils.isVertical)(tabBarPosition) ? 'marginBottom' : marginProperty, gutter);

        (0, _warning.default)('tab' in child.props, 'There must be `tab` property on children of Tabs.');
        var id = _this.props.id ? "".concat(key, "-").concat(_this.props.id) : key;

        var node = _react.default.createElement("div", _extends({
          role: "tab",
          "aria-disabled": child.props.disabled ? 'true' : 'false',
          "aria-selected": activeKey === key ? 'true' : 'false',
          tabIndex: activeKey === key ? 0 : -1
        }, events, {
          className: cls,
          key: key,
          style: style,
          id: "tab-".concat(id),
          "aria-controls": "tabpane-".concat(id)
        }, ref), child.props.tab);

        if (renderTabBarNode) {
          node = renderTabBarNode(node);
        }

        rst.push(node);
      });

      return _react.default.createElement("div", {
        ref: saveRef('navTabsContainer')
      }, rst);
    }
  }]);

  return TabBarTabsNode;
}(_react.default.Component);

exports.default = TabBarTabsNode;
TabBarTabsNode.defaultProps = {
  panels: [],
  prefixCls: [],
  tabBarGutter: null,
  onTabClick: function onTabClick() {},
  saveRef: function saveRef() {}
};