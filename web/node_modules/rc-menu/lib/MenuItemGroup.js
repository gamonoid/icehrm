"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _react = _interopRequireDefault(require("react"));

var _util = require("./util");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _createSuper(Derived) { return function () { var Super = _getPrototypeOf(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var MenuItemGroup = /*#__PURE__*/function (_React$Component) {
  _inherits(MenuItemGroup, _React$Component);

  var _super = _createSuper(MenuItemGroup);

  function MenuItemGroup() {
    var _this;

    _classCallCheck(this, MenuItemGroup);

    _this = _super.apply(this, arguments);

    _this.renderInnerMenuItem = function (item) {
      var _this$props = _this.props,
          renderMenuItem = _this$props.renderMenuItem,
          index = _this$props.index;
      return renderMenuItem(item, index, _this.props.subMenuKey);
    };

    return _this;
  }

  _createClass(MenuItemGroup, [{
    key: "render",
    value: function render() {
      var props = _extends({}, this.props);

      var _props$className = props.className,
          className = _props$className === void 0 ? '' : _props$className,
          rootPrefixCls = props.rootPrefixCls;
      var titleClassName = "".concat(rootPrefixCls, "-item-group-title");
      var listClassName = "".concat(rootPrefixCls, "-item-group-list");
      var title = props.title,
          children = props.children;

      _util.menuAllProps.forEach(function (key) {
        return delete props[key];
      }); // Set onClick to null, to ignore propagated onClick event


      delete props.onClick;
      delete props.direction;
      return _react.default.createElement("li", Object.assign({}, props, {
        className: "".concat(className, " ").concat(rootPrefixCls, "-item-group")
      }), _react.default.createElement("div", {
        className: titleClassName,
        title: typeof title === 'string' ? title : undefined
      }, title), _react.default.createElement("ul", {
        className: listClassName
      }, _react.default.Children.map(children, this.renderInnerMenuItem)));
    }
  }]);

  return MenuItemGroup;
}(_react.default.Component);

MenuItemGroup.isMenuItemGroup = true;
MenuItemGroup.defaultProps = {
  disabled: true
};
var _default = MenuItemGroup;
exports.default = _default;