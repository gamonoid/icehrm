(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';
// For more information about browser field, check out the browser field at https://github.com/substack/browserify-handbook#browser-field.

var styleElementsInsertedAtTop = [];

var insertStyleElement = function(styleElement, options) {
    var head = document.head || document.getElementsByTagName('head')[0];
    var lastStyleElementInsertedAtTop = styleElementsInsertedAtTop[styleElementsInsertedAtTop.length - 1];

    options = options || {};
    options.insertAt = options.insertAt || 'bottom';

    if (options.insertAt === 'top') {
        if (!lastStyleElementInsertedAtTop) {
            head.insertBefore(styleElement, head.firstChild);
        } else if (lastStyleElementInsertedAtTop.nextSibling) {
            head.insertBefore(styleElement, lastStyleElementInsertedAtTop.nextSibling);
        } else {
            head.appendChild(styleElement);
        }
        styleElementsInsertedAtTop.push(styleElement);
    } else if (options.insertAt === 'bottom') {
        head.appendChild(styleElement);
    } else {
        throw new Error('Invalid value for parameter \'insertAt\'. Must be \'top\' or \'bottom\'.');
    }
};

module.exports = {
    // Create a <link> tag with optional data attributes
    createLink: function(href, attributes) {
        var head = document.head || document.getElementsByTagName('head')[0];
        var link = document.createElement('link');

        link.href = href;
        link.rel = 'stylesheet';

        for (var key in attributes) {
            if ( ! attributes.hasOwnProperty(key)) {
                continue;
            }
            var value = attributes[key];
            link.setAttribute('data-' + key, value);
        }

        head.appendChild(link);
    },
    // Create a <style> tag with optional data attributes
    createStyle: function(cssText, attributes, extraOptions) {
        extraOptions = extraOptions || {};

        var style = document.createElement('style');
        style.type = 'text/css';

        for (var key in attributes) {
            if ( ! attributes.hasOwnProperty(key)) {
                continue;
            }
            var value = attributes[key];
            style.setAttribute('data-' + key, value);
        }

        if (style.sheet) { // for jsdom and IE9+
            style.innerHTML = cssText;
            style.sheet.cssText = cssText;
            insertStyleElement(style, { insertAt: extraOptions.insertAt });
        } else if (style.styleSheet) { // for IE8 and below
            insertStyleElement(style, { insertAt: extraOptions.insertAt });
            style.styleSheet.cssText = cssText;
        } else { // for Chrome, Firefox, and Safari
            style.appendChild(document.createTextNode(cssText));
            insertStyleElement(style, { insertAt: extraOptions.insertAt });
        }
    }
};

},{}],2:[function(require,module,exports){
"use strict";

var _react = _interopRequireDefault(require("react"));

var _reactDom = _interopRequireDefault(require("react-dom"));

var _UserViewSwitch = _interopRequireDefault(require("../components/UserViewSwitch"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

window.handleViewChange = function (view, userLevel) {
  if (userLevel !== 'Admin' && userLevel !== 'Manager') {
    $('.skeletonSideMenu').hide();
    $('.skeletonContent').hide();
    $('.skeletonTabs').hide();
    $('.sidebar').show();
    return;
  }

  if (view == null) {
    view = localStorage.getItem('user-view') === 'user' ? 'user' : 'admin';
  } // Data-driven, drift-proof: hide ALL admin menu groups in Employee view and ALL
  // user menu groups in Admin view, by id prefix. The previous version hid a
  // hardcoded list of group ids, which drifted — e.g. #menu_admin_Marketplace was
  // never added, so the admin-only Marketplace leaked into Employee view. Using a
  // prefix selector means any current/future group is handled automatically.
  // (Admin menu groups render as #menu_admin_*, user groups as #menu_module_*.)


  if (view === 'admin') {
    $('[id^="menu_module_"]').hide();
    $('[id^="menu_admin_"]').show();
  } else {
    $('[id^="menu_module_"]').show();
    $('[id^="menu_admin_"]').hide();
  }

  $('.skeletonSideMenu').hide();
  $('.skeletonContent').hide();
  $('.skeletonTabs').hide();
  $('.sidebar').show();
};

window.showUserViewSwitch = function (userLevel) {
  if (userLevel !== 'Admin' && userLevel !== 'Manager') {
    return;
  }

  _reactDom["default"].render( /*#__PURE__*/_react["default"].createElement(_UserViewSwitch["default"], {
    userLevel: userLevel
  }), document.getElementById('UserViewSwitch'));
};

},{"../components/UserViewSwitch":3,"react":"react","react-dom":"react-dom"}],3:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _react = _interopRequireDefault(require("react"));

var _antd = require("antd");

require("./css/user-view-switch.css");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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

var UserViewSwitch = /*#__PURE__*/function (_React$Component) {
  _inherits(UserViewSwitch, _React$Component);

  var _super = _createSuper(UserViewSwitch);

  function UserViewSwitch(props) {
    var _this;

    _classCallCheck(this, UserViewSwitch);

    _this = _super.call(this, props);
    _this.state = {
      view: localStorage.getItem('user-view') === 'user' ? 'user' : 'admin'
    };
    return _this;
  }

  _createClass(UserViewSwitch, [{
    key: "componentDidMount",
    value: function componentDidMount() {}
  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState, snapshot) {}
  }, {
    key: "handleChange",
    value: function handleChange(value) {
      var view = value ? 'admin' : 'user';
      localStorage.setItem('user-view', view);
      this.setState({
        view: view
      });

      if (view === 'user') {
        window.location = "".concat(CLIENT_BASE_URL, "?g=modules&n=dashboard&m=module_Personal_Information");
      } else if (this.props.userLevel === 'Manager') {
        window.location = "".concat(CLIENT_BASE_URL, "?g=modules&n=dashboard&m=module_Personal_Information");
      } else if (this.props.userLevel === 'Admin') {
        window.location = "".concat(CLIENT_BASE_URL, "?g=admin&n=dashboard&m=admin_Admin");
      } //handleViewChange(view);

    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;

      return /*#__PURE__*/_react["default"].createElement(_antd.ConfigProvider, {
        theme: {
          components: {
            Switch: {
              handleSize: 27,
              // Size of the inner circle (handle)
              trackHeight: 30,
              // Height of the switch
              borderRadius: 2,
              // Square border (use 0 for a perfect square)
              backgroundColor: "#f39c05"
            }
          }
        }
      }, /*#__PURE__*/_react["default"].createElement(_antd.Switch, {
        className: "custom-switch",
        checkedChildren: this.props.userLevel === 'Admin' ? 'Admin View' : 'Manager View',
        unCheckedChildren: "Employee View",
        defaultChecked: true,
        style: {
          padding: "0 16px",
          width: '100%',
          borderRadius: 0,
          fontSize: 18
        },
        checked: this.state.view === 'admin',
        onChange: function onChange(event) {
          return _this2.handleChange(event);
        }
      }));
    }
  }]);

  return UserViewSwitch;
}(_react["default"].Component);

var _default = UserViewSwitch;
exports["default"] = _default;

},{"./css/user-view-switch.css":4,"antd":"antd","react":"react"}],4:[function(require,module,exports){
var css = "#UserViewSwitch  button {\n  background-color: #4E545C !important;\n  /* Custom handle color */\n}\n#UserViewSwitch  .ant-switch-inner-checked {\n  font-size: 14px !important;\n  font-weight: 400 !important;\n}\n#UserViewSwitch  .ant-switch-inner-unchecked {\n  font-size: 14px !important;\n  font-weight: 400 !important;\n}\n"; (require("browserify-css").createStyle(css, { "href": "web/components/css/user-view-switch.css" }, { "insertAt": "bottom" })); module.exports = css;
},{"browserify-css":1}]},{},[2])

//# sourceMappingURL=common-bundle.js.map
