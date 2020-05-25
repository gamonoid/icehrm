"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = exports.MenuItem = void 0;

var _react = _interopRequireDefault(require("react"));

var _reactDom = _interopRequireDefault(require("react-dom"));

var _KeyCode = _interopRequireDefault(require("rc-util/lib/KeyCode"));

var _classnames = _interopRequireDefault(require("classnames"));

var _scrollIntoViewIfNeeded = _interopRequireDefault(require("scroll-into-view-if-needed"));

var _miniStore = require("mini-store");

var _util = require("./util");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

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

var MenuItem = /*#__PURE__*/function (_React$Component) {
  _inherits(MenuItem, _React$Component);

  var _super = _createSuper(MenuItem);

  function MenuItem() {
    var _this;

    _classCallCheck(this, MenuItem);

    _this = _super.apply(this, arguments);

    _this.onKeyDown = function (e) {
      var keyCode = e.keyCode;

      if (keyCode === _KeyCode.default.ENTER) {
        _this.onClick(e);

        return true;
      }

      return undefined;
    };

    _this.onMouseLeave = function (e) {
      var _this$props = _this.props,
          eventKey = _this$props.eventKey,
          onItemHover = _this$props.onItemHover,
          onMouseLeave = _this$props.onMouseLeave;
      onItemHover({
        key: eventKey,
        hover: false
      });
      onMouseLeave({
        key: eventKey,
        domEvent: e
      });
    };

    _this.onMouseEnter = function (e) {
      var _this$props2 = _this.props,
          eventKey = _this$props2.eventKey,
          onItemHover = _this$props2.onItemHover,
          onMouseEnter = _this$props2.onMouseEnter;
      onItemHover({
        key: eventKey,
        hover: true
      });
      onMouseEnter({
        key: eventKey,
        domEvent: e
      });
    };

    _this.onClick = function (e) {
      var _this$props3 = _this.props,
          eventKey = _this$props3.eventKey,
          multiple = _this$props3.multiple,
          onClick = _this$props3.onClick,
          onSelect = _this$props3.onSelect,
          onDeselect = _this$props3.onDeselect,
          isSelected = _this$props3.isSelected;
      var info = {
        key: eventKey,
        keyPath: [eventKey],
        item: _assertThisInitialized(_this),
        domEvent: e
      };
      onClick(info);

      if (multiple) {
        if (isSelected) {
          onDeselect(info);
        } else {
          onSelect(info);
        }
      } else if (!isSelected) {
        onSelect(info);
      }
    };

    _this.saveNode = function (node) {
      _this.node = node;
    };

    return _this;
  }

  _createClass(MenuItem, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      // invoke customized ref to expose component to mixin
      this.callRef();
    }
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps) {
      var _this$props4 = this.props,
          active = _this$props4.active,
          parentMenu = _this$props4.parentMenu,
          eventKey = _this$props4.eventKey; // 在 parentMenu 上层保存滚动状态，避免重复的 MenuItem key 导致滚动跳动
      // https://github.com/ant-design/ant-design/issues/16181

      if (!prevProps.active && active && (!parentMenu || !parentMenu["scrolled-".concat(eventKey)])) {
        if (this.node) {
          (0, _scrollIntoViewIfNeeded.default)(this.node, {
            scrollMode: 'if-needed',
            // eslint-disable-next-line react/no-find-dom-node
            boundary: _reactDom.default.findDOMNode(parentMenu),
            block: 'nearest'
          });
          parentMenu["scrolled-".concat(eventKey)] = true;
        }
      } else if (parentMenu && parentMenu["scrolled-".concat(eventKey)]) {
        delete parentMenu["scrolled-".concat(eventKey)];
      }

      this.callRef();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      var props = this.props;

      if (props.onDestroy) {
        props.onDestroy(props.eventKey);
      }
    }
  }, {
    key: "getPrefixCls",
    value: function getPrefixCls() {
      return "".concat(this.props.rootPrefixCls, "-item");
    }
  }, {
    key: "getActiveClassName",
    value: function getActiveClassName() {
      return "".concat(this.getPrefixCls(), "-active");
    }
  }, {
    key: "getSelectedClassName",
    value: function getSelectedClassName() {
      return "".concat(this.getPrefixCls(), "-selected");
    }
  }, {
    key: "getDisabledClassName",
    value: function getDisabledClassName() {
      return "".concat(this.getPrefixCls(), "-disabled");
    }
  }, {
    key: "callRef",
    value: function callRef() {
      if (this.props.manualRef) {
        this.props.manualRef(this);
      }
    }
  }, {
    key: "render",
    value: function render() {
      var _classNames;

      var props = _objectSpread({}, this.props);

      var className = (0, _classnames.default)(this.getPrefixCls(), props.className, (_classNames = {}, _defineProperty(_classNames, this.getActiveClassName(), !props.disabled && props.active), _defineProperty(_classNames, this.getSelectedClassName(), props.isSelected), _defineProperty(_classNames, this.getDisabledClassName(), props.disabled), _classNames));

      var attrs = _objectSpread({}, props.attribute, {
        title: props.title,
        className: className,
        // set to menuitem by default
        role: props.role || 'menuitem',
        'aria-disabled': props.disabled
      });

      if (props.role === 'option') {
        // overwrite to option
        attrs = _objectSpread({}, attrs, {
          role: 'option',
          'aria-selected': props.isSelected
        });
      } else if (props.role === null || props.role === 'none') {
        // sometimes we want to specify role inside <li/> element
        // <li><a role='menuitem'>Link</a></li> would be a good example
        // in this case the role on <li/> should be "none" to
        // remove the implied listitem role.
        // https://www.w3.org/TR/wai-aria-practices-1.1/examples/menubar/menubar-1/menubar-1.html
        attrs.role = 'none';
      } // In case that onClick/onMouseLeave/onMouseEnter is passed down from owner


      var mouseEvent = {
        onClick: props.disabled ? null : this.onClick,
        onMouseLeave: props.disabled ? null : this.onMouseLeave,
        onMouseEnter: props.disabled ? null : this.onMouseEnter
      };

      var style = _objectSpread({}, props.style);

      if (props.mode === 'inline') {
        if (props.direction === 'rtl') {
          style.paddingRight = props.inlineIndent * props.level;
        } else {
          style.paddingLeft = props.inlineIndent * props.level;
        }
      }

      _util.menuAllProps.forEach(function (key) {
        return delete props[key];
      });

      delete props.direction;
      var icon = this.props.itemIcon;

      if (typeof this.props.itemIcon === 'function') {
        // TODO: This is a bug which should fixed after TS refactor
        icon = _react.default.createElement(this.props.itemIcon, this.props);
      }

      return _react.default.createElement("li", Object.assign({}, props, attrs, mouseEvent, {
        style: style,
        ref: this.saveNode
      }), props.children, icon);
    }
  }]);

  return MenuItem;
}(_react.default.Component);

exports.MenuItem = MenuItem;
MenuItem.isMenuItem = true;
MenuItem.defaultProps = {
  onSelect: _util.noop,
  onMouseEnter: _util.noop,
  onMouseLeave: _util.noop,
  manualRef: _util.noop
};
var connected = (0, _miniStore.connect)(function (_ref, _ref2) {
  var activeKey = _ref.activeKey,
      selectedKeys = _ref.selectedKeys;
  var eventKey = _ref2.eventKey,
      subMenuKey = _ref2.subMenuKey;
  return {
    active: activeKey[subMenuKey] === eventKey,
    isSelected: selectedKeys.indexOf(eventKey) !== -1
  };
})(MenuItem);
var _default = connected;
exports.default = _default;