(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = ApiAccess;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Paragraph = _antd.Typography.Paragraph; // Native "API Access" tab for modules::employees — shows the employee's REST API
// access token and lets them reset it. Reads from / writes to the same legacy
// MobileAppAdapter (window.modJsList.tabMobileApp) the Mobile App tab uses; the
// token/enabled flag are injected by initModulesEmployees.

function ApiAccess() {
  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var adapter = (window.modJsList || {}).tabMobileApp || null;
  var apiEnabled = adapter ? adapter.apiEnabled === '1' || adapter.apiEnabled === 1 : false;
  var apiBaseUrl = adapter ? adapter.apiBaseUrl : null;

  var _useState = (0, _react.useState)(adapter ? adapter.token : null),
      _useState2 = _slicedToArray(_useState, 2),
      apiToken = _useState2[0],
      setApiToken = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      resetting = _useState4[0],
      setResetting = _useState4[1];

  var cardStyle = {
    borderRadius: 12,
    boxShadow: _theme.MUI_SHADOW,
    marginBottom: 16
  };

  var resetToken = function resetToken() {
    if (!adapter) return;
    setResetting(true);

    adapter.resetApiTokenSuccessCallback = function (cb) {
      setResetting(false);
      var v = Array.isArray(cb) ? cb[0] : cb;
      var newToken = v && _typeof(v) === 'object' ? v.jwtToken : null;

      if (newToken) {
        adapter.token = newToken; // keep it in sync for the Mobile App tab / re-mounts

        setApiToken(newToken);

        _antd.message.success('API token reset. The previous token no longer works.', 5);
      } else {
        _antd.message.error('Could not reset the API token. Please try again.', 5);
      }
    };

    adapter.resetApiTokenFailCallback = function () {
      setResetting(false);

      _antd.message.error('Could not reset the API token. Please try again.', 5);
    };

    try {
      adapter.resetApiToken();
    } catch (e) {
      setResetting(false);
    }
  };

  if (!apiEnabled || !apiToken) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: '100%'
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      style: cardStyle,
      title: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, /*#__PURE__*/_react["default"].createElement(_icons.ApiOutlined, null), "API Access")
    }, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
      description: "REST API access is not enabled for your account."
    })));
  }

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: '100%'
    }
  }, apiBaseUrl ? /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    style: cardStyle,
    title: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, /*#__PURE__*/_react["default"].createElement(_icons.ApiOutlined, null), "API Base URL")
  }, /*#__PURE__*/_react["default"].createElement(Paragraph, {
    type: "secondary"
  }, "Base URL for all REST API requests."), /*#__PURE__*/_react["default"].createElement(Paragraph, {
    copyable: {
      text: apiBaseUrl
    },
    style: {
      wordBreak: 'break-all',
      fontFamily: 'monospace',
      fontSize: 12.5,
      background: token.colorFillTertiary,
      padding: 12,
      borderRadius: 8,
      marginBottom: 0
    }
  }, apiBaseUrl)) : null, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    style: cardStyle,
    title: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, /*#__PURE__*/_react["default"].createElement(_icons.ApiOutlined, null), "API Access Token"),
    extra: /*#__PURE__*/_react["default"].createElement(_antd.Popconfirm, {
      title: "Reset API token",
      description: "This immediately invalidates your current token. Any integration using it will stop working until updated.",
      okText: "Reset",
      cancelText: "Cancel",
      okButtonProps: {
        danger: true
      },
      onConfirm: resetToken
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      danger: true,
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ReloadOutlined, null),
      loading: resetting
    }, "Reset Token"))
  }, /*#__PURE__*/_react["default"].createElement(Paragraph, {
    type: "secondary"
  }, "Use this token to authenticate REST API requests (as a Bearer token). Keep it secret \u2014 anyone with this token can act as you."), /*#__PURE__*/_react["default"].createElement(Paragraph, {
    copyable: {
      text: apiToken
    },
    style: {
      wordBreak: 'break-all',
      fontFamily: 'monospace',
      fontSize: 12.5,
      background: token.colorFillTertiary,
      padding: 12,
      borderRadius: 8,
      marginBottom: 0
    }
  }, apiToken)));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],2:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = AppShell;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

var _Notifications = _interopRequireDefault(require("./Notifications"));

var _News = _interopRequireDefault(require("./News"));

var _Dashboard = _interopRequireDefault(require("./Dashboard"));

var _EmployeeDashboard = _interopRequireDefault(require("./EmployeeDashboard"));

var _LicenseRenewalBanner = _interopRequireDefault(require("./LicenseRenewalBanner"));

var _UpdateAvailableBanner = _interopRequireDefault(require("./UpdateAvailableBanner"));

var _NativeModuleHost = _interopRequireDefault(require("./NativeModuleHost"));

var _NativeDocumentModal = _interopRequireDefault(require("./NativeDocumentModal"));

var _ModuleSearch = _interopRequireDefault(require("./ModuleSearch"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Header = _antd.Layout.Header,
    Sider = _antd.Layout.Sider,
    Content = _antd.Layout.Content;
var useBreakpoint = _antd.Grid.useBreakpoint; // Map menu-group names to antd SVG icons (always render, unlike the FA webfont
// which is not fully deployed). Falls back to a generic icon.

var GROUP_ICONS = {
  Admin: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null),
  Employees: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
  Manage: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
  Reports: /*#__PURE__*/_react["default"].createElement(_icons.BarChartOutlined, null),
  'My Reports': /*#__PURE__*/_react["default"].createElement(_icons.BarChartOutlined, null),
  System: /*#__PURE__*/_react["default"].createElement(_icons.ControlOutlined, null),
  Insights: /*#__PURE__*/_react["default"].createElement(_icons.FundOutlined, null),
  Payroll: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
  Finance: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
  Recruitment: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
  Marketplace: /*#__PURE__*/_react["default"].createElement(_icons.ShopOutlined, null),
  'About You': /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
  Collaboration: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
  Leave: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
  Time: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
  'My Tasks': /*#__PURE__*/_react["default"].createElement(_icons.CheckSquareOutlined, null),
  Documents: /*#__PURE__*/_react["default"].createElement(_icons.FileTextOutlined, null),
  Training: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
  Performance: /*#__PURE__*/_react["default"].createElement(_icons.TrophyOutlined, null),
  Travel: /*#__PURE__*/_react["default"].createElement(_icons.CarOutlined, null)
}; // Area `icon` name (from the server area registry) -> antd icon. Falls back to a
// generic icon for extension-defined areas.

var AREA_ICONS = {
  home: /*#__PURE__*/_react["default"].createElement(_icons.HomeOutlined, null),
  people: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
  time: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
  leave: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
  pay: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
  recruitment: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
  learning: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
  performance: /*#__PURE__*/_react["default"].createElement(_icons.TrophyOutlined, null),
  documents: /*#__PURE__*/_react["default"].createElement(_icons.FileTextOutlined, null),
  reports: /*#__PURE__*/_react["default"].createElement(_icons.BarChartOutlined, null),
  configuration: /*#__PURE__*/_react["default"].createElement(_icons.BuildOutlined, null),
  system: /*#__PURE__*/_react["default"].createElement(_icons.ControlOutlined, null),
  more: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null)
};

var areaIcon = function areaIcon(name) {
  return AREA_ICONS[name] || /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null);
}; // A section header label inside the area menu (e.g. "MANAGE" / "PERSONAL"). Styled
// to read clearly as a heading, NOT a clickable menu item: small uppercase,
// letter-spaced, muted, with a leading icon and a hairline rule above.


function sectionHeader(icon, text) {
  return /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 7,
      paddingTop: 12,
      marginTop: 2,
      borderTop: '1px solid rgba(255,255,255,0.08)',
      fontSize: 10.5,
      fontWeight: 700,
      letterSpacing: 1.3,
      textTransform: 'uppercase',
      color: 'rgba(255,255,255,0.42)',
      cursor: 'default',
      userSelect: 'none'
    }
  }, _react["default"].cloneElement(icon, {
    style: {
      fontSize: 12,
      opacity: 0.85
    }
  }), /*#__PURE__*/_react["default"].createElement("span", null, text));
} // CSS injected into each module iframe (same-origin) to hide the legacy chrome
// (top bar + sidebar) so only the module content shows inside the new shell.
// No legacy files are modified — this is applied from the parent shell on load.


var EMBED_CSS = "\n  header.header { display: none !important; }\n  aside.left-side, .sidebar-offcanvas, .skeletonSideMenu { display: none !important; }\n  .right-side { margin-left: 0 !important; left: 0 !important; }\n  .wrapper, body, html { padding-top: 0 !important; margin-top: 0 !important; background: #f0f2f5 !important; }\n  body { min-width: 0 !important; }\n  #DemoModeNotice, #IceHrmConnectionNotice { display: none !important; }\n";
var KEY_SEP = '::'; // Build antd Menu `items` from a view's group list. Each item already carries its
// resolved { g, n } route from the server (MenuService.getViewMenus).

function buildItems(groups) {
  if (!Array.isArray(groups)) return [];
  return groups.map(function (group) {
    return {
      key: "grp:".concat(group.name),
      label: group.name,
      icon: GROUP_ICONS[group.name] || /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
      children: (group.items || []).map(function (it) {
        return {
          key: "".concat(it.g).concat(KEY_SEP).concat(it.n),
          label: it.label || it.name,
          data: {
            g: it.g,
            n: it.n,
            label: it.label || it.name
          }
        };
      })
    };
  });
}

function keyFor(g, n) {
  return "".concat(g).concat(KEY_SEP).concat(n);
} // Merge several group lists into one, de-duplicating groups by name (their items
// are concatenated, items de-duped by g::n) — used to give non-switch privileged
// users (e.g. Managers) a single combined menu of their admin + employee modules.


function mergeGroups() {
  var byName = new Map();
  var order = [];

  for (var _len = arguments.length, lists = new Array(_len), _key = 0; _key < _len; _key++) {
    lists[_key] = arguments[_key];
  }

  lists.forEach(function (list) {
    return (Array.isArray(list) ? list : []).forEach(function (group) {
      if (!byName.has(group.name)) {
        byName.set(group.name, {
          name: group.name,
          items: []
        });
        order.push(group.name);
      }

      var tgt = byName.get(group.name);
      var seen = new Set(tgt.items.map(function (it) {
        return keyFor(it.g, it.n);
      }));
      (group.items || []).forEach(function (it) {
        var k = keyFor(it.g, it.n);

        if (!seen.has(k)) {
          seen.add(k);
          tgt.items.push(it);
        }
      });
    });
  });
  return order.map(function (n) {
    return byName.get(n);
  });
}

function AppShell(_ref) {
  var bootstrap = _ref.bootstrap,
      config = _ref.config,
      _ref$colorMode = _ref.colorMode,
      colorMode = _ref$colorMode === void 0 ? 'light' : _ref$colorMode,
      onToggleColorMode = _ref.onToggleColorMode;
  var isDark = colorMode === 'dark';
  var screens = useBreakpoint();
  var isMobile = !screens.md; // < 768px

  var _useState = (0, _react.useState)(false),
      _useState2 = _slicedToArray(_useState, 2),
      drawerOpen = _useState2[0],
      setDrawerOpen = _useState2[1];

  var _useState3 = (0, _react.useState)(null),
      _useState4 = _slicedToArray(_useState3, 2),
      current = _useState4[0],
      setCurrent = _useState4[1]; // { g, n, label } | null — drives the iframe src
  // When a module navigates the iframe internally to ANOTHER module, this tracks
  // what the iframe is actually showing (for title/selection/hash) without
  // changing the iframe src (which would reload it).


  var _useState5 = (0, _react.useState)(null),
      _useState6 = _slicedToArray(_useState5, 2),
      override = _useState6[0],
      setOverride = _useState6[1];

  var _useState7 = (0, _react.useState)(false),
      _useState8 = _slicedToArray(_useState7, 2),
      iframeLoading = _useState8[0],
      setIframeLoading = _useState8[1];

  var iframeRef = (0, _react.useRef)(null);

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var company = bootstrap.company || {};
  var user = bootstrap.user || {};
  var profile = bootstrap.profile || {};
  var displayName = profile.firstName || user.first_name || user.email;
  var views = bootstrap.views || {
    admin: [],
    employee: []
  }; // "View as employee" (profile switch) — initiated from inside a module. The
  // switch state lives in the legacy (cookie) session, which the stateless REST
  // API can't see, so the shell derives the banner directly from the loaded
  // module page (the .switched-name marker) on every iframe load.
  // Seed from bootstrap (set when an admin has switched into an employee profile
  // and is viewing a natively-mounted module — no iframe marker to derive from).

  var _useState9 = (0, _react.useState)(bootstrap.switchedProfile || null),
      _useState10 = _slicedToArray(_useState9, 2),
      switchedProfile = _useState10[0],
      setSwitchedProfile = _useState10[1];

  var _useState11 = (0, _react.useState)(0),
      _useState12 = _slicedToArray(_useState11, 2),
      reloadNonce = _useState12[0],
      setReloadNonce = _useState12[1]; // A document (e.g. an editor task list) opened from a notification — mounted in
  // a shell-level modal rather than navigated to (the editor isn't a route).


  var _useState13 = (0, _react.useState)(null),
      _useState14 = _slicedToArray(_useState13, 2),
      doc = _useState14[0],
      setDoc = _useState14[1];

  var setDocUrl = function setDocUrl(url) {
    return setDoc(url ? {
      url: url,
      title: 'Task List'
    } : null);
  }; // Seam for bespoke extension views (NativeExtensionView trees, e.g. the learn
  // user "Open Course" button): a legacy document_link would navigate the SPA away
  // (and bounce to the dashboard), so views call this global to open the document
  // in the shell's native editor modal instead. Cleared on unmount so a stale
  // handler never outlives the shell.


  (0, _react.useEffect)(function () {
    window.iceShellOpenDocument = function (url, title) {
      return setDoc(url ? {
        url: url,
        title: title || 'Document'
      } : null);
    };

    return function () {
      delete window.iceShellOpenDocument;
    };
  }, []);

  var reloadIframe = function reloadIframe() {
    setIframeLoading(true);
    setReloadNonce(function (n) {
      return n + 1;
    });
  }; // AdapterBase.setAdminProfile posts this (instead of breaking the top window
  // out to the legacy app) after a switch/switch-back. Reload the current module;
  // the banner is then re-derived from the reloaded page in onIframeLoad.


  (0, _react.useEffect)(function () {
    var onMsg = function onMsg(e) {
      if (!e.data || e.data.iceShell !== 'profile-switched') return;
      reloadIframe();
    };

    window.addEventListener('message', onMsg);
    return function () {
      return window.removeEventListener('message', onMsg);
    };
  }, []); // Switch back. Inside an iframe, delegate to the module's (session-correct)
  // handler. For a natively-mounted module there is no iframe, so post the
  // switch-back to the legacy session endpoint ourselves and reload the shell.

  var switchBack = function switchBack() {
    try {
      var win = iframeRef.current && iframeRef.current.contentWindow;

      if (win && win.modJs && typeof win.modJs.setAdminProfile === 'function') {
        win.modJs.setAdminProfile('-1');
        return;
      }
    } catch (e) {
      /* fall through to native */
    }

    var body = new URLSearchParams({
      a: 'setAdminEmp',
      empid: '-1'
    });
    fetch("".concat(config.clientBaseUrl, "service.php"), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    }).then(function () {
      window.location.reload();
    })["catch"](function () {
      window.location.reload();
    });
  }; // There is no Admin/Employee switch any more — the menu is sliced by high-level
  // AREA (Home/People/Time and Work/…). Within an area, items are split into two
  // sections: "Manage" (admin/management modules, from the admin view) and "Mine"
  // (the user's own self-service modules, from the employee view).


  var areaRegistry = bootstrap.areas || []; // Build, per area, the Manage + Mine item lists (de-duped by route across both
  // views), plus a flat route -> data map and route -> area map. We must NOT dedupe
  // by label: an admin module and a self-service module can share a label but be
  // different destinations (e.g. "Expenses" = manage all vs "My Expenses" = apply
  // as an employee), disambiguated by the "My …" rename.

  var _useMemo = (0, _react.useMemo)(function () {
    var map = {};
    var aok = {};
    var byArea = {}; // area -> { manage:[], mine:[], keys:Set }

    var add = function add(groups, section) {
      return (Array.isArray(groups) ? groups : []).forEach(function (grp) {
        return (grp.items || []).forEach(function (it) {
          var area = it.area || 'more';
          var key = keyFor(it.g, it.n);
          var label = it.label || it.name;
          if (!byArea[area]) byArea[area] = {
            manage: [],
            mine: [],
            keys: new Set()
          };
          var b = byArea[area];
          if (b.keys.has(key)) return; // same route in both views: keep the first (Manage)

          b.keys.add(key); // areaOrder lets meta.json position an item within its section; unset
          // items default to 50 and keep their natural order via a stable sort.

          var order = it.areaOrder === 0 || it.areaOrder ? it.areaOrder : 50;
          b[section].push({
            key: key,
            label: label,
            order: order
          });

          if (!map[key]) {
            map[key] = {
              g: it.g,
              n: it.n,
              label: label,
              area: area,
              section: section
            };
            aok[key] = area;
          }
        });
      });
    };

    add(views.admin, 'manage');
    add(views.employee, 'mine'); // Stable sort each section by areaOrder (ties keep insertion/menu order).

    Object.keys(byArea).forEach(function (a) {
      var cmp = function cmp(x, y) {
        return x.order - y.order;
      };

      byArea[a].manage.sort(cmp);
      byArea[a].mine.sort(cmp);
    });
    return {
      itemMap: map,
      areaOfKey: aok,
      itemsByArea: byArea
    };
  }, [views]),
      itemMap = _useMemo.itemMap,
      areaOfKey = _useMemo.areaOfKey,
      itemsByArea = _useMemo.itemsByArea;

  var areaCount = function areaCount(id) {
    var b = itemsByArea[id];
    return b ? b.manage.length + b.mine.length : 0;
  };

  var firstKeyOf = function firstKeyOf(id) {
    var b = itemsByArea[id];
    if (!b) return null;
    var it = b.manage[0] || b.mine[0];
    return it ? it.key : null;
  }; // The module to land on when there is no hash: the user's home module, else a
  // dashboard. Must exist in the menu.


  var defaultKey = function defaultKey() {
    var hl = bootstrap.homeLink;
    var cands = [];
    if (hl && hl.group && hl.name) cands.push(keyFor(hl.group, hl.name));
    cands.push('admin::dashboard', 'modules::dashboard');
    return cands.find(function (k) {
      return itemMap[k];
    }) || null;
  }; // Areas that actually have ≥1 accessible item, in registry order.


  var availableAreas = (0, _react.useMemo)(function () {
    return areaRegistry.filter(function (a) {
      return areaCount(a.id) > 0;
    });
  }, [areaRegistry, itemsByArea] // eslint-disable-line react-hooks/exhaustive-deps
  );

  var _useState15 = (0, _react.useState)(function () {
    var saved = localStorage.getItem('shell-area');
    if (saved && areaCount(saved) > 0) return saved;
    var dk = defaultKey();
    var a = dk && areaOfKey[dk];
    if (a && areaCount(a) > 0) return a;
    var first = areaRegistry.find(function (ar) {
      return areaCount(ar.id) > 0;
    });
    return first ? first.id : null;
  }),
      _useState16 = _slicedToArray(_useState15, 2),
      selectedArea = _useState16[0],
      setSelectedArea = _useState16[1];

  (0, _react.useEffect)(function () {
    if (selectedArea) localStorage.setItem('shell-area', selectedArea);
  }, [selectedArea]); // The left menu = the selected area's items. When both Manage and Mine sections
  // have items, render labelled groups separated by a divider; if only one section
  // exists, render it flat (no redundant header).

  var menuItems = (0, _react.useMemo)(function () {
    var b = itemsByArea[selectedArea];
    if (!b) return [];

    var toItem = function toItem(it) {
      return {
        key: it.key,
        label: it.label
      };
    };

    if (b.manage.length && b.mine.length) {
      return [{
        type: 'group',
        key: 'grp-manage',
        label: sectionHeader( /*#__PURE__*/_react["default"].createElement(_icons.ControlOutlined, null), 'Manage'),
        children: b.manage.map(toItem)
      }, {
        type: 'group',
        key: 'grp-mine',
        label: sectionHeader( /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null), 'Personal'),
        children: b.mine.map(toItem)
      }];
    }

    return [].concat(_toConsumableArray(b.manage), _toConsumableArray(b.mine)).map(toItem);
  }, [itemsByArea, selectedArea]);

  var onAreaChange = function onAreaChange(areaId) {
    setSelectedArea(areaId);
    var first = firstKeyOf(areaId);
    if (first) openModule(first);
  }; // --- routing: hash <-> selected module -----------------------------------


  var applyHash = function applyHash() {
    var raw = (window.location.hash || '').replace(/^#\/?/, '');
    var key = raw ? decodeURIComponent(raw) : defaultKey();
    var data = key && itemMap[key];
    setOverride(null);

    if (data) {
      setCurrent(data);
      setIframeLoading(true);
    } else {
      setCurrent(null);
    }
  };

  (0, _react.useEffect)(function () {
    applyHash();

    var onHash = function onHash() {
      return applyHash();
    };

    window.addEventListener('hashchange', onHash);
    return function () {
      return window.removeEventListener('hashchange', onHash);
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemMap]);

  var openModule = function openModule(key) {
    var data = itemMap[key];
    if (!data) return;
    if (isMobile) setDrawerOpen(false);
    var hash = "#".concat(encodeURIComponent(key));

    if (window.location.hash === hash) {
      // same module: force reload of the iframe
      setIframeLoading(true);
      setCurrent(_objectSpread({}, data));
    } else {
      window.location.hash = hash; // triggers applyHash via hashchange
    }
  }; // What the iframe is actually showing (may differ from `current` after an
  // in-iframe navigation) — drives the title, sidebar selection and hash.


  var displayed = override || current; // Navigate to a module by group/name (used by notifications). Uses the menu
  // route if known, else loads the page directly.

  var navigateTo = function navigateTo(g, n) {
    if (!g || !n) return;
    var key = keyFor(g, n);
    if (isMobile) setDrawerOpen(false);

    if (itemMap[key]) {
      openModule(key);
    } else {
      setOverride(null);
      setCurrent({
        g: g,
        n: n,
        label: n
      });
      setIframeLoading(true);

      try {
        window.history.replaceState(null, '', "#".concat(encodeURIComponent(key)));
      } catch (z) {
        /* */
      }
    }
  };

  var selectedKey = displayed ? keyFor(displayed.g, displayed.n) : null; // Keep the area selector in sync with whatever module is actually showing
  // (deep links, in-iframe navigation to another area's module, etc.).

  (0, _react.useEffect)(function () {
    var a = selectedKey && areaOfKey[selectedKey];
    if (a && a !== selectedArea) setSelectedArea(a); // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedKey, areaOfKey]);

  var onIframeLoad = function onIframeLoad(e) {
    try {
      var _doc = e.target.contentDocument;

      if (_doc && _doc.head) {
        var style = _doc.createElement('style');

        style.setAttribute('data-shell-embed', '1');
        style.textContent = EMBED_CSS;

        _doc.head.appendChild(style);
      } // Derive the "viewing as employee" banner from the page's own switched
      // marker, so it stays correct on load, after a switch, and after switch-back.


      var nameEl = _doc && _doc.querySelector('.switched-name');

      setSwitchedProfile(nameEl ? {
        name: (nameEl.textContent || '').trim()
      } : null); // If the module navigated the iframe to ANOTHER module (e.g. a dashboard
      // widget link), sync the title/selection/hash to it WITHOUT reloading.

      var loc = e.target.contentWindow && e.target.contentWindow.location;
      var sp = loc && new URLSearchParams(loc.search);
      var g = sp && sp.get('g');
      var n = sp && sp.get('n');

      if (g && n) {
        var k = keyFor(g, n);

        if (current && k === keyFor(current.g, current.n)) {
          setOverride(null);
        } else if (itemMap[k]) {
          setOverride(itemMap[k]);

          try {
            window.history.replaceState(null, '', "#".concat(encodeURIComponent(k)));
          } catch (z) {
            /* */
          }
        }
      }
    } catch (err) {// cross-origin (shouldn't happen — same origin) — ignore
    }

    setIframeLoading(false);
  }; // The admin dashboard is rendered natively (React + charts), not via the iframe.


  var isNativeDashboard = current && current.g === 'admin' && current.n === 'dashboard'; // The employee/manager personal dashboard is also native.

  var isNativeEmployeeDashboard = current && current.g === 'modules' && current.n === 'dashboard'; // Modules registered for native in-shell mounting (no iframe).

  var nativeKey = current ? "".concat(current.g, "/").concat(current.n) : null;
  var isNativeModule = !!nativeKey && !isNativeDashboard && !isNativeEmployeeDashboard && (bootstrap.nativeModules || []).indexOf(nativeKey) !== -1;
  var iframeSrc = current && !isNativeDashboard && !isNativeEmployeeDashboard && !isNativeModule ? "".concat(config.clientBaseUrl, "?g=").concat(encodeURIComponent(current.g), "&n=").concat(encodeURIComponent(current.n), "&_embed=1") : null; // --- chrome --------------------------------------------------------------

  var sideMenu = /*#__PURE__*/_react["default"].createElement(_antd.Menu, {
    mode: "inline",
    theme: "dark",
    selectedKeys: selectedKey ? [selectedKey] : [],
    style: {
      borderInlineEnd: 0
    },
    items: menuItems,
    onClick: function onClick(_ref2) {
      var key = _ref2.key;
      return openModule(key);
    }
  });

  var userMenu = {
    items: [{
      key: 'name',
      label: "".concat(profile.firstName || user.first_name || '', " ").concat(profile.lastName || user.last_name || '').trim() || user.email,
      disabled: true
    }, {
      type: 'divider'
    }, {
      key: 'home',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.HomeOutlined, null),
      label: 'Home'
    }, {
      key: 'logout',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.LogoutOutlined, null),
      label: 'Logout'
    }],
    onClick: function onClick(_ref3) {
      var key = _ref3.key;
      if (key === 'logout') window.location.href = "".concat(config.clientBaseUrl, "logout.php");

      if (key === 'home') {
        window.location.hash = '';
        setCurrent(null);
      }
    }
  };

  var _useState17 = (0, _react.useState)(false),
      _useState18 = _slicedToArray(_useState17, 2),
      logoFailed = _useState18[0],
      setLogoFailed = _useState18[1]; // Prefer a company-uploaded logo (white-label); otherwise the compact IceHrm
  // mark. Left-aligned to line up with the menu items.


  var defaultLogo = "".concat(config.baseUrl || '', "images/logo-sq.png");
  var logoSrc = company.logoUrl || defaultLogo; // Pinned brand footer at the bottom of the sidebar: just version + copyright
  // (the logo now lives in the top bar).

  var logo = /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: '0 0 auto',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: 8,
      padding: '10px 16px',
      overflow: 'hidden',
      borderTop: '1px solid rgba(255,255,255,0.08)',
      lineHeight: 1.35
    }
  }, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      color: 'rgba(255,255,255,0.4)',
      fontSize: 10.5,
      whiteSpace: 'nowrap'
    }
  }, "\xA9 ".concat(new Date().getFullYear(), " IceHrm.com")), bootstrap.version && /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      color: 'rgba(255,255,255,0.72)',
      fontSize: 11,
      fontWeight: 600,
      whiteSpace: 'nowrap'
    }
  }, "v".concat(bootstrap.version))); // Every reachable module, for the top-bar global search (route map -> list).


  var allModules = (0, _react.useMemo)(function () {
    return Object.keys(itemMap).map(function (key) {
      return _objectSpread({
        key: key
      }, itemMap[key]);
    });
  }, [itemMap]); // Area selector — replaces the old Admin/Employee switch. Picking an area
  // filters the left menu to that functional domain. Styled as a dropdown (icon
  // tile + name + chevron) so it clearly reads as switchable.

  var currentArea = availableAreas.find(function (a) {
    return a.id === selectedArea;
  }) || availableAreas[0] || null;
  var areaSwitch = availableAreas.length > 1 && currentArea ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: '12px 16px 8px'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 10.5,
      letterSpacing: 1.2,
      fontWeight: 700,
      color: 'rgba(255,255,255,0.38)',
      marginBottom: 8,
      paddingLeft: 2
    }
  }, "AREA"), /*#__PURE__*/_react["default"].createElement(_antd.ConfigProvider, {
    theme: (0, _theme.buildTheme)('dark')
  }, /*#__PURE__*/_react["default"].createElement(_antd.Dropdown, {
    trigger: ['click'],
    menu: {
      selectedKeys: [selectedArea],
      items: availableAreas.map(function (a) {
        return {
          key: a.id,
          icon: areaIcon(a.icon),
          label: a.label
        };
      }),
      onClick: function onClick(_ref4) {
        var key = _ref4.key;
        return onAreaChange(key);
      }
    }
  }, /*#__PURE__*/_react["default"].createElement("button", {
    type: "button",
    className: "ice-role-switch",
    title: "Switch area",
    style: {
      width: '100%',
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      padding: '8px 10px',
      borderRadius: 12,
      cursor: 'pointer',
      color: '#fff',
      background: 'rgba(255,255,255,0.06)',
      border: '1px solid rgba(255,255,255,0.10)',
      textAlign: 'left'
    }
  }, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      width: 30,
      height: 30,
      borderRadius: 9,
      flex: '0 0 auto',
      fontSize: 15,
      background: '#4c9aff26',
      color: '#4c9aff',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, areaIcon(currentArea.icon)), /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      display: 'block',
      fontSize: 13.5,
      fontWeight: 600,
      lineHeight: 1.2,
      overflow: 'hidden',
      textOverflow: 'ellipsis',
      whiteSpace: 'nowrap'
    }
  }, currentArea.label), /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      display: 'block',
      fontSize: 11,
      color: 'rgba(255,255,255,0.45)',
      lineHeight: 1.2
    }
  }, "Switch area")), /*#__PURE__*/_react["default"].createElement(_icons.DownOutlined, {
    style: {
      fontSize: 12,
      color: 'rgba(255,255,255,0.5)',
      flex: '0 0 auto'
    }
  }))))) : null; // --- top bar (GA-style): brand · area switch · module search ----------------

  var headerBrand = /*#__PURE__*/_react["default"].createElement("div", {
    role: "button",
    tabIndex: 0,
    onClick: function onClick() {
      window.location.hash = '';
      setCurrent(null);
    },
    title: "Home",
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      cursor: 'pointer',
      paddingRight: 18,
      marginRight: 4,
      borderRight: '1px solid rgba(255,255,255,0.14)',
      height: 48
    }
  }, !logoFailed ? /*#__PURE__*/_react["default"].createElement("img", {
    src: logoSrc,
    alt: company.name || 'IceHrm',
    style: {
      height: 46,
      maxWidth: 200,
      objectFit: 'contain'
    },
    onError: function onError() {
      return setLogoFailed(true);
    }
  }) : /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      fontWeight: 700,
      fontSize: 22,
      color: '#fff',
      whiteSpace: 'nowrap'
    }
  }, company.name || 'IceHrm'));

  return /*#__PURE__*/_react["default"].createElement(_antd.Layout, {
    style: {
      minHeight: '100vh'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.ConfigProvider, {
    theme: (0, _theme.buildTheme)('dark')
  }, /*#__PURE__*/_react["default"].createElement(Header, {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      paddingInline: 16,
      background: _theme.SIDEBAR_BG,
      position: 'sticky',
      top: 0,
      zIndex: 20,
      boxShadow: '0 1px 4px rgba(0,0,0,0.25)'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      flex: '0 0 auto'
    }
  }, isMobile && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "text",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.MenuOutlined, {
      style: {
        color: '#fff'
      }
    }),
    onClick: function onClick() {
      return setDrawerOpen(true);
    }
  }), !isMobile && headerBrand, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      fontSize: 16,
      fontWeight: 600,
      color: 'rgba(255,255,255,0.92)',
      maxWidth: 260,
      overflow: 'hidden',
      textOverflow: 'ellipsis',
      whiteSpace: 'nowrap'
    }
  }, displayed ? displayed.label : 'Home')), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: 1,
      display: 'flex',
      justifyContent: 'center',
      minWidth: 0,
      padding: '0 16px'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: '100%',
      maxWidth: 560
    }
  }, /*#__PURE__*/_react["default"].createElement(_ModuleSearch["default"], {
    items: allModules,
    areas: areaRegistry,
    onSelect: function onSelect(key) {
      return navigateTo(itemMap[key].g, itemMap[key].n);
    },
    dark: true
  }))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 4,
      flex: '0 0 auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
    title: isDark ? 'Switch to light mode' : 'Switch to dark mode'
  }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "text",
    "aria-label": "Toggle colour mode",
    icon: isDark ? /*#__PURE__*/_react["default"].createElement(_icons.BulbFilled, {
      style: {
        color: '#fbc02d'
      }
    }) : /*#__PURE__*/_react["default"].createElement(_icons.BulbOutlined, {
      style: {
        color: '#fff'
      }
    }),
    onClick: onToggleColorMode
  })), /*#__PURE__*/_react["default"].createElement(_Notifications["default"], {
    clientBaseUrl: config.clientBaseUrl,
    onNavigate: navigateTo,
    onOpenDocument: setDocUrl
  }), /*#__PURE__*/_react["default"].createElement(_antd.Dropdown, {
    menu: userMenu,
    trigger: ['click']
  }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "text",
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 8,
      color: 'rgba(255,255,255,0.92)'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
    size: "small",
    src: profile.image || undefined,
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null)
  }), !isMobile && /*#__PURE__*/_react["default"].createElement("span", null, displayName)))))), /*#__PURE__*/_react["default"].createElement(_antd.Layout, null, !isMobile && /*#__PURE__*/_react["default"].createElement(Sider, {
    width: 240,
    style: {
      height: 'calc(100vh - 64px)',
      position: 'sticky',
      top: 64,
      insetInlineStart: 0,
      background: _theme.SIDEBAR_BG
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      flexDirection: 'column'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: 1,
      minHeight: 0,
      overflow: 'auto',
      paddingTop: 8
    }
  }, areaSwitch, sideMenu), logo)), isMobile && /*#__PURE__*/_react["default"].createElement(_antd.Drawer, {
    placement: "left",
    open: drawerOpen,
    onClose: function onClose() {
      return setDrawerOpen(false);
    },
    width: 260,
    styles: {
      body: {
        padding: 0,
        background: _theme.SIDEBAR_BG
      },
      header: {
        display: 'none'
      }
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      height: '100%',
      display: 'flex',
      flexDirection: 'column'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: 1,
      minHeight: 0,
      overflow: 'auto',
      paddingTop: 8
    }
  }, areaSwitch, sideMenu), logo)), /*#__PURE__*/_react["default"].createElement(_antd.Layout, null, switchedProfile && /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "warning",
    banner: true,
    showIcon: true,
    message: /*#__PURE__*/_react["default"].createElement("span", null, "Viewing as ", /*#__PURE__*/_react["default"].createElement("strong", null, switchedProfile.name)),
    action: /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "small",
      onClick: switchBack
    }, "Switch back")
  }), bootstrap.showConnectBanner && /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "warning",
    banner: true,
    showIcon: true,
    style: {
      padding: '18px 28px',
      alignItems: 'center'
    },
    message: /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        lineHeight: 1.35
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 17,
        fontWeight: 700,
        marginBottom: 3
      }
    }, "Connect to icehrm.com to unlock the marketplace"), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 14,
        opacity: 0.9
      }
    }, "This installation isn't connected yet. Connect it to install marketplace", ' ', "extensions and receive product updates.")),
    action: /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "large",
      type: "primary",
      style: {
        fontWeight: 600
      },
      onClick: function onClick() {
        return navigateTo('extension', 'marketplace|admin');
      }
    }, "Connect now")
  }), /*#__PURE__*/_react["default"].createElement(Content, {
    style: {
      background: token.colorBgLayout,
      position: 'relative',
      height: "calc(100vh - 64px".concat(switchedProfile ? ' - 40px' : '').concat(bootstrap.showConnectBanner ? ' - 84px' : '', ")")
    }
  }, isNativeDashboard ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      height: '100%',
      overflow: 'auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(_UpdateAvailableBanner["default"], {
    updateAvailable: bootstrap.updateAvailable
  }), /*#__PURE__*/_react["default"].createElement(_LicenseRenewalBanner["default"], {
    licenseRenewal: bootstrap.licenseRenewal
  }), /*#__PURE__*/_react["default"].createElement(_News["default"], {
    config: config
  }), /*#__PURE__*/_react["default"].createElement(_Dashboard["default"], {
    config: config,
    onNavigate: navigateTo
  })) : isNativeEmployeeDashboard ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      height: '100%',
      overflow: 'auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(_UpdateAvailableBanner["default"], {
    updateAvailable: bootstrap.updateAvailable
  }), /*#__PURE__*/_react["default"].createElement(_LicenseRenewalBanner["default"], {
    licenseRenewal: bootstrap.licenseRenewal
  }), /*#__PURE__*/_react["default"].createElement(_EmployeeDashboard["default"], {
    config: config,
    onNavigate: navigateTo,
    onOpenDocument: setDocUrl
  })) : isNativeModule ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      height: '100%',
      overflow: 'auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(_NativeModuleHost["default"], {
    key: nativeKey,
    group: current.g,
    name: current.n,
    shellConfig: config
  })) : iframeSrc ? /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, iframeLoading && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2,
      background: token.colorBgLayout
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
    size: "large"
  })), /*#__PURE__*/_react["default"].createElement("iframe", {
    ref: iframeRef,
    key: "".concat(iframeSrc, "#").concat(reloadNonce),
    title: current ? current.label : '',
    src: iframeSrc,
    onLoad: onIframeLoad,
    style: {
      width: '100%',
      height: '100%',
      border: 0,
      display: 'block',
      visibility: iframeLoading ? 'hidden' : 'visible'
    }
  })) : /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 24
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: token.colorBgContainer,
      borderRadius: 8,
      padding: 32,
      maxWidth: 720,
      boxShadow: '0 1px 4px rgba(0,0,0,0.06)',
      color: token.colorText
    }
  }, /*#__PURE__*/_react["default"].createElement("h2", {
    style: {
      marginTop: 0
    }
  }, "Welcome to the new IceHrm"), /*#__PURE__*/_react["default"].createElement("p", {
    style: {
      color: token.colorTextSecondary
    }
  }, "This is the new React + Ant Design interface. Pick a module from the menu to get started. The sidebar reflects your permissions and collapses into a drawer on mobile."), /*#__PURE__*/_react["default"].createElement("p", {
    style: {
      color: token.colorTextSecondary,
      fontSize: 13
    }
  }, "Signed in as ", /*#__PURE__*/_react["default"].createElement("strong", null, user.email), " (", bootstrap.userLevel, ").")))))), /*#__PURE__*/_react["default"].createElement(_NativeDocumentModal["default"], {
    documentUrl: doc && doc.url,
    bundle: "editor/user/dist/editor.js",
    mountFn: "mountEditorDocument",
    deps: ['dist/vendorOther.js', 'dist/third-party.js', 'dist/common.js'],
    title: doc && doc.title || 'Document',
    shellConfig: config,
    onClose: function onClose() {
      return setDoc(null);
    }
  }));
}

},{"./Dashboard":4,"./EmployeeDashboard":5,"./LicenseRenewalBanner":12,"./ModuleSearch":14,"./NativeDocumentModal":17,"./NativeModuleHost":19,"./News":20,"./Notifications":21,"./UpdateAvailableBanner":24,"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],3:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = CompanyStructureCards;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var PAGE_SIZE = 6;
var TYPE_STYLE = {
  Company: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.BankOutlined, null),
    color: _theme.MUI.primary
  },
  'Head Office': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.HomeOutlined, null),
    color: '#0288d1'
  },
  'Regional Office': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClusterOutlined, null),
    color: '#7b1fa2'
  },
  Department: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#2e7d32'
  },
  Unit: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#ed6c02'
  },
  'Sub Unit': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#ed6c02'
  }
};

var typeStyle = function typeStyle(t) {
  return TYPE_STYLE[t] || {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#607d8b'
  };
};

function CompanyStructureCards(_ref) {
  var shellConfig = _ref.shellConfig;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      nodes = _useState2[0],
      setNodes = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      err = _useState4[0],
      setErr = _useState4[1];

  var _useState5 = (0, _react.useState)(''),
      _useState6 = _slicedToArray(_useState5, 2),
      search = _useState6[0],
      setSearch = _useState6[1];

  var _useState7 = (0, _react.useState)(1),
      _useState8 = _slicedToArray(_useState7, 2),
      page = _useState8[0],
      setPage = _useState8[1];

  var adapterRef = (0, _react.useRef)(null);

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var load = (0, _react.useCallback)(function () {
    fetch("".concat(shellConfig.restApiBase, "appshell/org-structure"), {
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      return setNodes(d && d.nodes || []);
    })["catch"](function () {
      return setErr(true);
    });
  }, [shellConfig]);
  (0, _react.useEffect)(function () {
    load();
  }, [load]); // Use the legacy adapter (instantiated by the module's init) for Add/Edit/Delete
  // and hook its post-save reload into our refresh by injecting a fake table ref.

  (0, _react.useEffect)(function () {
    var list = window.modJsList || {};
    var m = list.tabCompanyStructure;
    if (!m) return;
    adapterRef.current = m;
    m.tableContainer = {
      current: {
        reload: function reload() {
          return load();
        },
        setCurrentElement: function setCurrentElement() {},
        setLoading: function setLoading() {},
        setFilterData: function setFilterData() {}
      }
    }; // Load the form's remote-source select options (Parent Structure, Country,
    // Time Zone, Leads). Normally done inside the adapter's get(); the cards tab
    // never calls get(), so do it here — otherwise selects show ids / "No data".

    try {
      if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
        m.masterDataReader.updateAllMasterData();
      }
    } catch (e) {
      /* ignore */
    }
  }, [nodes === null, load]); // eslint-disable-line react-hooks/exhaustive-deps

  var adapter = function adapter() {
    return adapterRef.current || (window.modJsList || {}).tabCompanyStructure;
  };

  var can = function can(a) {
    var m = adapter();
    return m && m.hasAccess && m.hasAccess(a);
  };

  var addNew = function addNew() {
    var m = adapter();
    if (m) m.renderForm();
  };

  var edit = function edit(id) {
    var m = adapter();
    if (m) m.edit(id);
  }; // The adapter's deleteRow() relies on the legacy Bootstrap #deleteModel that
  // does not exist in the native shell mount, so confirm with antd and delete
  // via cleanDelete() (a plain $.post to service.php, no Bootstrap/loader), then
  // refresh the cards ourselves.


  var del = function del(id) {
    var m = adapter();
    if (!m) return;
    var node = (nodes || []).find(function (n) {
      return String(n.id) === String(id);
    });

    _antd.Modal.confirm({
      title: 'Delete structure',
      content: node ? "Are you sure you want to delete \u201C".concat(node.title, "\u201D?") : 'Are you sure you want to delete this company structure?',
      okText: 'Delete',
      okType: 'danger',
      onOk: function onOk() {
        return new Promise(function (resolve) {
          try {
            m.cleanDelete(id, function (httpStatus, status) {
              if (httpStatus === 200 && status === 'SUCCESS') {
                _antd.message.success('Structure deleted');

                load();
              } else {
                _antd.message.error('Could not delete this structure. It may be in use.', 5);
              }

              resolve();
            });
          } catch (e) {
            _antd.message.error('Could not delete this structure', 5);

            resolve();
          }
        });
      }
    });
  };

  var view = function view(id) {
    var m = adapter();
    if (m && m.showDetailsModal) m.showDetailsModal(id);
  };

  var copy = function copy(id) {
    var m = adapter();
    if (m && m.copyRow) m.copyRow(id);
  };

  var filtered = (0, _react.useMemo)(function () {
    var all = nodes || [];
    var q = search.trim().toLowerCase();
    if (!q) return all;
    return all.filter(function (n) {
      return "".concat(n.title, " ").concat(n.type, " ").concat(n.country, " ").concat(n.parentTitle || '').toLowerCase().includes(q);
    });
  }, [nodes, search]);
  var paged = (0, _react.useMemo)(function () {
    return filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);
  }, [filtered, page]);
  (0, _react.useEffect)(function () {
    setPage(1);
  }, [search]);
  if (err) return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "Could not load company structure"
  });
  if (!nodes) return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      padding: 60
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
    size: "large"
  }));

  var metaItem = function metaItem(icon, text) {
    return text ? /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        display: 'inline-flex',
        alignItems: 'center',
        gap: 5,
        maxWidth: 280,
        overflow: 'hidden'
      }
    }, icon, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, text)) : null;
  };

  return /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
    id: "CompanyStructureForm",
    style: {
      display: 'none'
    }
  }), /*#__PURE__*/_react["default"].createElement("div", {
    id: "CompanyStructureFilterForm",
    style: {
      display: 'none'
    }
  }), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: 12,
      marginBottom: 16,
      flexWrap: 'wrap'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", null, can('save') && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "primary",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.PlusOutlined, null),
    onClick: addNew
  }, "Add Structure")), /*#__PURE__*/_react["default"].createElement(_antd.Input.Search, {
    allowClear: true,
    placeholder: "Search structures\u2026",
    style: {
      maxWidth: 280
    },
    onChange: function onChange(e) {
      return setSearch(e.target.value);
    }
  })), filtered.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "No matching company structures"
  }) : /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10
    }
  }, paged.map(function (n) {
    var st = typeStyle(n.type);
    return /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      key: n.id,
      hoverable: true,
      onClick: function onClick() {
        return view(n.id);
      },
      style: {
        borderRadius: 10,
        boxShadow: _theme.MUI_SHADOW,
        cursor: 'pointer'
      },
      styles: {
        body: {
          padding: '12px 16px'
        }
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 14
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: 40,
        height: 40,
        borderRadius: 10,
        flex: '0 0 auto',
        background: "".concat(st.color, "18"),
        color: st.color,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 18
      }
    }, st.icon), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        minWidth: 0,
        flex: 1
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 8,
        flexWrap: 'wrap'
      }
    }, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        fontWeight: 600,
        fontSize: 14
      }
    }, n.title), /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
      style: {
        borderRadius: 6,
        margin: 0
      }
    }, n.type || 'Unit'), n.headcount > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
      color: "blue",
      style: {
        borderRadius: 6,
        margin: 0
      }
    }, /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null), ' ', n.headcount)), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 16,
        marginTop: 4,
        color: token.colorTextSecondary,
        fontSize: 12.5,
        flexWrap: 'wrap'
      }
    }, metaItem( /*#__PURE__*/_react["default"].createElement(_icons.PartitionOutlined, null), n.parentTitle ? "Reports to ".concat(n.parentTitle) : 'Top level'), metaItem( /*#__PURE__*/_react["default"].createElement(_icons.EnvironmentOutlined, null), n.address), metaItem( /*#__PURE__*/_react["default"].createElement(_icons.GlobalOutlined, null), n.country), metaItem( /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null), n.timezone))), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        gap: 10,
        flex: '0 0 auto'
      },
      onClick: function onClick(e) {
        return e.stopPropagation();
      }
    }, can('save') && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Edit"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.EditOutlined, {
        style: {
          color: '#2e7d32'
        }
      }),
      onClick: function onClick() {
        return edit(n.id);
      }
    })), can('save') && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Copy"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CopyOutlined, {
        style: {
          color: '#546e7a'
        }
      }),
      onClick: function onClick() {
        return copy(n.id);
      }
    })), can('delete') && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Delete"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DeleteOutlined, {
        style: {
          color: '#d32f2f'
        }
      }),
      onClick: function onClick() {
        return del(n.id);
      }
    })))));
  })), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'flex-end',
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Pagination, {
    current: page,
    pageSize: PAGE_SIZE,
    total: filtered.length,
    onChange: setPage,
    showSizeChanger: false,
    showTotal: function showTotal(t) {
      return "".concat(t, " structures");
    }
  }))));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],4:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = Dashboard;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _g2plot = require("@antv/g2plot");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text;
var PALETTE = ['#346CB0', '#5AD8A6', '#5B8FF9', '#F6BD16', '#E8684A', '#9270CA', '#6DC8EC', '#FF99C3'];
var PLOTS = {
  pie: _g2plot.Pie,
  donut: _g2plot.Donut,
  column: _g2plot.Column,
  area: _g2plot.Area,
  bar: _g2plot.Bar
}; // Thin React wrapper around g2plot 1.x (the version already bundled as vendorAntv).
// Dark mode is handled by injecting light text/axis colours into each chart's
// config (see darkChart* below) rather than g2plot's built-in 'dark' theme,
// which paints an opaque grey background that does not match the card. The
// donut's centre label is HTML (see the .ring-guide-* CSS in spa-shell.php).

function Chart(_ref) {
  var type = _ref.type,
      config = _ref.config,
      _ref$height = _ref.height,
      height = _ref$height === void 0 ? 230 : _ref$height;
  var ref = (0, _react.useRef)(null);
  (0, _react.useEffect)(function () {
    if (!ref.current) return undefined;
    var Ctor = PLOTS[type];
    if (!Ctor) return undefined;
    var plot;

    try {
      plot = new Ctor(ref.current, config);
      plot.render();
    } catch (e) {
      /* ignore render errors */
    }

    return function () {
      try {
        if (plot) plot.destroy();
      } catch (e) {
        /* */
      }
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [type, JSON.stringify(config), height]);
  return /*#__PURE__*/_react["default"].createElement("div", {
    ref: ref,
    style: {
      height: height
    }
  });
}

function initials(name) {
  var p = (name || '').trim().split(/\s+/);
  return (p[0] && p[0][0] || '' + (p[1] && p[1][0] || '')).toUpperCase() + (p[1] && p[1][0] || '').toUpperCase();
}

function colorFor(name) {
  var h = 0;

  for (var i = 0; i < (name || '').length; i += 1) {
    h = h * 31 + name.charCodeAt(i) >>> 0;
  }

  return PALETTE[h % PALETTE.length];
}

function fmtDate(d) {
  if (!d) return '';
  var dt = new Date("".concat(d, "T00:00:00"));
  if (Number.isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric'
  });
}

function Kpi(_ref2) {
  var icon = _ref2.icon,
      label = _ref2.label,
      value = _ref2.value,
      color = _ref2.color,
      sub = _ref2.sub,
      labelColor = _ref2.labelColor,
      subColor = _ref2.subColor,
      onClick = _ref2.onClick;
  return /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    bordered: false,
    hoverable: !!onClick,
    onClick: onClick,
    style: {
      borderRadius: 14,
      boxShadow: _theme.MUI_SHADOW,
      cursor: onClick ? 'pointer' : 'default'
    },
    bodyStyle: {
      padding: 18
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 14
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: 46,
      height: 46,
      borderRadius: 12,
      flex: '0 0 auto',
      background: "".concat(color, "1f"),
      color: color,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontSize: 20
    }
  }, icon), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 26,
      fontWeight: 700,
      lineHeight: 1.1
    }
  }, value), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      color: labelColor,
      fontSize: 13,
      whiteSpace: 'nowrap'
    }
  }, label))), sub ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginTop: 10,
      fontSize: 12,
      color: subColor
    }
  }, sub) : null);
} // Small "upgrade to IceHrmPro" call-to-action shown in the KPI row. Clicking the
// card opens the marketplace's IceHrmPro comparison tab; the Buy Now button links
// straight to the purchase page.


var ICEHRM_PRO_PURCHASE_URL = 'https://icehrm.com/purchase-icehrmpro';

function UpgradeBox(_ref3) {
  var onCompare = _ref3.onCompare;
  return /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    bordered: false,
    hoverable: !!onCompare,
    onClick: onCompare,
    style: {
      borderRadius: 14,
      boxShadow: _theme.MUI_SHADOW,
      cursor: onCompare ? 'pointer' : 'default',
      background: 'linear-gradient(135deg, #7B61FF 0%, #9270CA 100%)',
      color: '#fff',
      height: '100%'
    },
    bodyStyle: {
      padding: 18
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 14
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: 46,
      height: 46,
      borderRadius: 12,
      flex: '0 0 auto',
      background: 'rgba(255,255,255,0.2)',
      color: '#fff',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontSize: 20
    }
  }, /*#__PURE__*/_react["default"].createElement(_icons.RocketOutlined, null)), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 15,
      fontWeight: 700,
      lineHeight: 1.2
    }
  }, "Upgrade to IceHrmPro"), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 12,
      opacity: 0.9,
      whiteSpace: 'nowrap'
    }
  }, "Unlock all premium extensions"))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginTop: 12,
      display: 'flex',
      gap: 8,
      alignItems: 'center'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    size: "small",
    href: ICEHRM_PRO_PURCHASE_URL,
    target: "_blank",
    rel: "noopener noreferrer",
    onClick: function onClick(e) {
      return e.stopPropagation();
    },
    style: {
      background: '#fff',
      borderColor: '#fff',
      color: '#7B61FF',
      fontWeight: 600
    }
  }, "Buy Now"), onCompare ? /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      fontSize: 12,
      opacity: 0.9,
      textDecoration: 'underline'
    }
  }, "Compare editions") : null));
}

function SectionCard(_ref4) {
  var title = _ref4.title,
      extra = _ref4.extra,
      children = _ref4.children,
      height = _ref4.height;
  return /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    title: /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        fontWeight: 600
      }
    }, title),
    extra: extra,
    bordered: false,
    style: {
      borderRadius: 14,
      boxShadow: _theme.MUI_SHADOW,
      height: height || '100%'
    },
    bodyStyle: {
      padding: 16
    }
  }, children);
}

function PeopleList(_ref5) {
  var data = _ref5.data,
      renderMeta = _ref5.renderMeta,
      emptyText = _ref5.emptyText;

  if (!data || data.length === 0) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
      description: emptyText || 'Nothing here'
    });
  }

  return /*#__PURE__*/_react["default"].createElement(_antd.List, {
    dataSource: data,
    split: false,
    renderItem: function renderItem(item) {
      return /*#__PURE__*/_react["default"].createElement(_antd.List.Item, {
        style: {
          padding: '8px 0'
        }
      }, /*#__PURE__*/_react["default"].createElement(_antd.List.Item.Meta, {
        avatar: /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
          style: {
            backgroundColor: colorFor(item.name),
            verticalAlign: 'middle'
          }
        }, initials(item.name)),
        title: /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            fontSize: 14
          }
        }, item.name),
        description: renderMeta(item)
      }));
    }
  });
} // Shared scaffold for the legacy-parity dashboard banners (payment reminder,
// demo-data prompt, trial upgrade ad): gradient card, round icon, title +
// message on the left, action buttons on the right.


function DashBanner(_ref6) {
  var gradient = _ref6.gradient,
      shadow = _ref6.shadow,
      border = _ref6.border,
      icon = _ref6.icon,
      title = _ref6.title,
      message = _ref6.message,
      actions = _ref6.actions;
  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: gradient,
      borderRadius: 12,
      padding: '20px 24px',
      marginBottom: 18,
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: 16,
      flexWrap: 'wrap',
      boxShadow: shadow,
      border: border || 'none'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 16
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: 'rgba(255,255,255,0.2)',
      borderRadius: '50%',
      width: 48,
      height: 48,
      flex: '0 0 auto',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#fff',
      fontSize: 20
    }
  }, icon), /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      color: '#fff',
      fontWeight: 600,
      fontSize: 16,
      marginBottom: 4
    }
  }, title), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      color: 'rgba(255,255,255,0.9)',
      fontSize: 14
    }
  }, message))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 10,
      alignItems: 'center'
    }
  }, actions));
}

function bannerButton(_ref7) {
  var label = _ref7.label,
      icon = _ref7.icon,
      color = _ref7.color,
      _onClick = _ref7.onClick,
      ghost = _ref7.ghost;
  return /*#__PURE__*/_react["default"].createElement("a", {
    key: label,
    onClick: function onClick(e) {
      e.preventDefault();

      _onClick();
    },
    style: ghost ? {
      background: 'rgba(255,255,255,0.2)',
      color: '#fff',
      padding: '10px 16px',
      borderRadius: 8,
      textDecoration: 'none',
      fontSize: 14,
      fontWeight: 500,
      whiteSpace: 'nowrap',
      border: '1px solid rgba(255,255,255,0.3)'
    } : {
      background: '#fff',
      color: color,
      padding: '10px 24px',
      borderRadius: 8,
      textDecoration: 'none',
      fontSize: 14,
      fontWeight: 600,
      boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      whiteSpace: 'nowrap'
    }
  }, icon, icon ? ' ' : null, label);
} // Unpaid-invoice banner — same conditions and copy as the legacy admin
// dashboard (core/admin/dashboard/index.php): orange reminder for a single
// unpaid invoice, red service-restricted notice for two or more.


function PaymentBanner(_ref8) {
  var billing = _ref8.billing,
      onNavigate = _ref8.onNavigate;
  var count = billing && billing.unpaidInvoices || 0;
  if (count < 1) return null;
  var single = count === 1;
  var total = Number(billing.unpaidTotal || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  return /*#__PURE__*/_react["default"].createElement(DashBanner, {
    gradient: single ? 'linear-gradient(135deg, #fa8c16 0%, #d46b08 100%)' : 'linear-gradient(135deg, #f5222d 0%, #cf1322 100%)',
    shadow: single ? '0 4px 15px rgba(250, 140, 22, 0.3)' : '0 4px 15px rgba(245, 34, 45, 0.3)',
    icon: single ? /*#__PURE__*/_react["default"].createElement(_icons.WarningOutlined, null) : /*#__PURE__*/_react["default"].createElement(_icons.ExclamationCircleOutlined, null),
    title: single ? 'Payment Reminder' : 'Service Restricted - Payment Required',
    message: single ? "You have an unpaid invoice of ".concat(total, " USD. Please complete your payment to continue enjoying uninterrupted service.") : /*#__PURE__*/_react["default"].createElement("span", null, "Your account has an overdue balance of ".concat(total, " USD. Manager access has been temporarily restricted."), /*#__PURE__*/_react["default"].createElement("br", null), "Please complete payment and re-login to restore full access."),
    actions: bannerButton({
      label: 'Pay Now',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CreditCardOutlined, null),
      color: single ? '#d46b08' : '#cf1322',
      onClick: function onClick() {
        if (onNavigate) onNavigate('admin', 'billing');
      }
    })
  });
} // Fresh-install sample data prompt — same conditions as the legacy banner in
// core/header.php (data.demoPrompt is computed server-side). Dismiss hides it
// for this render only, like the legacy display:none link.


function DemoBanner(_ref9) {
  var show = _ref9.show,
      onNavigate = _ref9.onNavigate;

  var _useState = (0, _react.useState)(false),
      _useState2 = _slicedToArray(_useState, 2),
      dismissed = _useState2[0],
      setDismissed = _useState2[1];

  if (!show || dismissed) return null;
  return /*#__PURE__*/_react["default"].createElement(DashBanner, {
    gradient: "linear-gradient(135deg, #52c41a 0%, #389e0d 100%)",
    shadow: "0 4px 15px rgba(82, 196, 26, 0.3)",
    border: "2px solid rgba(255,255,255,0.3)",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.RocketOutlined, null),
    title: "Welcome to IceHrm! Want to see how it works?",
    message: /*#__PURE__*/_react["default"].createElement("span", null, "Add sample employees, projects, attendance, and more to explore all features.", /*#__PURE__*/_react["default"].createElement("br", null), /*#__PURE__*/_react["default"].createElement("strong", null, "You can clear all sample data with one click"), ' ', "when you're ready to go live."),
    actions: [bannerButton({
      label: 'Manage Sample Data',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.PlusCircleOutlined, null),
      color: '#389e0d',
      onClick: function onClick() {
        if (onNavigate) onNavigate('extension', 'demo-mode|admin');
      }
    }), bannerButton({
      label: 'Dismiss',
      ghost: true,
      onClick: function onClick() {
        return setDismissed(true);
      }
    })]
  });
} // Trial upgrade ad — same gate as the legacy admin dashboard banner
// (show_upgrade_ad session flag; data.upgradeAd carries remaining trial days).


function UpgradeBanner(_ref10) {
  var upgradeAd = _ref10.upgradeAd,
      onNavigate = _ref10.onNavigate;
  if (!upgradeAd) return null;
  var days = upgradeAd.days;
  return /*#__PURE__*/_react["default"].createElement(DashBanner, {
    gradient: "linear-gradient(135deg, #722ed1 0%, #531dab 100%)",
    shadow: "0 4px 15px rgba(114, 46, 209, 0.3)",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.StarOutlined, null),
    title: "Unlock the Full Potential of IceHrm",
    message: /*#__PURE__*/_react["default"].createElement("span", null, days !== null && days !== undefined ? /*#__PURE__*/_react["default"].createElement("span", null, "You have", ' ', /*#__PURE__*/_react["default"].createElement("strong", null, days, ' ', "days"), ' ', "left in your free trial.", ' ') : null, "Upgrade to IceHrm Cloud for unlimited employees, priority support, and premium features."),
    actions: [bannerButton({
      label: 'Upgrade Now',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ArrowUpOutlined, null),
      color: '#531dab',
      onClick: function onClick() {
        if (onNavigate) onNavigate('admin', 'billing');
      }
    }), bannerButton({
      label: 'See Plans',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.TagOutlined, null),
      ghost: true,
      onClick: function onClick() {
        window.open('https://icehrm.com/cloud-hosting-charges', '_blank');
      }
    })]
  });
}

function Dashboard(_ref11) {
  var config = _ref11.config,
      onNavigate = _ref11.onNavigate;

  var _useState3 = (0, _react.useState)(null),
      _useState4 = _slicedToArray(_useState3, 2),
      data = _useState4[0],
      setData = _useState4[1];

  var _useState5 = (0, _react.useState)(true),
      _useState6 = _slicedToArray(_useState5, 2),
      loading = _useState6[0],
      setLoading = _useState6[1];

  var _useState7 = (0, _react.useState)(null),
      _useState8 = _slicedToArray(_useState7, 2),
      error = _useState8[0],
      setError = _useState8[1];

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var isDark = token.colorBgContainer === _theme.MUI_DARK.paper; // Light text/axis colours injected into g2plot configs in dark mode (the
  // canvas text otherwise defaults to dark grey and is invisible on the card).

  var D_TXT = 'rgba(255,255,255,0.85)';
  var D_AXIS = 'rgba(255,255,255,0.45)';
  var D_LINE = 'rgba(255,255,255,0.25)';
  var D_GRID = 'rgba(255,255,255,0.12)';
  var darkLegend = isDark ? {
    text: {
      style: {
        fill: D_TXT
      }
    }
  } : {};
  var darkAxis = isDark ? {
    label: {
      style: {
        fill: D_AXIS
      }
    },
    line: {
      style: {
        stroke: D_LINE
      }
    },
    grid: {
      line: {
        style: {
          stroke: D_GRID
        }
      }
    }
  } : {};
  var darkLabel = isDark ? {
    style: {
      fill: D_TXT
    }
  } : {};
  (0, _react.useEffect)(function () {
    var alive = true;
    fetch("".concat(config.restApiBase, "appshell/dashboard"), {
      headers: {
        Authorization: "Bearer ".concat(config.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (alive) {
        setData(d);
        setLoading(false);
      }
    })["catch"](function (e) {
      if (alive) {
        setError(e.message);
        setLoading(false);
      }
    });
    return function () {
      alive = false;
    };
  }, [config]);
  var greeting = (0, _react.useMemo)(function () {
    var h = new Date().getHours();
    return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }, []);
  var greetName = (0, _react.useMemo)(function () {
    var n = data && data.greetingName || '';

    if (n.indexOf('@') !== -1) {
      return n.split('@')[0].replace(/[._]+/g, ' ').replace(/\b\w/g, function (c) {
        return c.toUpperCase();
      });
    }

    return n;
  }, [data]);

  if (loading) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'center',
        padding: 80
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
  }

  if (error || !data) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 32
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "Could not load dashboard".concat(error ? ": ".concat(error) : '')
    }));
  }

  var k = data.kpis || {};
  var kpis = [{
    label: 'Employees',
    value: k.totalEmployees,
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#346CB0',
    nav: ['admin', 'employees']
  }, {
    label: 'Departments',
    value: k.departments,
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#5AD8A6',
    nav: ['admin', 'company_structure']
  }];

  if (data.leave) {
    kpis.push({
      label: 'Pending Approvals',
      value: data.leave.pendingRequests,
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
      color: '#E8684A',
      nav: ['admin', 'leaves', 'tabEmployeeLeave']
    });
  }

  if (data.expenses) {
    kpis.push({
      label: 'Open Expenses',
      value: data.expenses.pendingCount,
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
      color: '#5B8FF9',
      nav: ['extension', 'expenses|admin', 'tabEmployeeExpense']
    });
  }

  var donutCfg = function donutCfg(rows) {
    return {
      data: rows || [],
      angleField: 'value',
      colorField: 'type',
      radius: 0.9,
      padding: 'auto',
      color: PALETTE,
      legend: _objectSpread({
        visible: true,
        position: 'bottom-center'
      }, darkLegend),
      label: {
        visible: false
      },
      statistic: {
        totalLabel: 'Total'
      }
    };
  };

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 24,
      maxWidth: 1500,
      margin: '0 auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(DemoBanner, {
    show: data.demoPrompt,
    onNavigate: onNavigate
  }), /*#__PURE__*/_react["default"].createElement(PaymentBanner, {
    billing: data.billing,
    onNavigate: onNavigate
  }), /*#__PURE__*/_react["default"].createElement(UpgradeBanner, {
    upgradeAd: data.upgradeAd,
    onNavigate: onNavigate
  }), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 18
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 22,
      fontWeight: 700
    }
  }, greeting, ",", ' ', greetName, ' ', "\uD83D\uDC4B"), /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary"
  }, "Here\u2019s what\u2019s happening across your organisation today.")), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16]
  }, kpis.map(function (kpi) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Col, {
      xs: 12,
      sm: 8,
      md: 6,
      xl: kpis.length > 6 ? 4 : 6,
      key: kpi.label
    }, /*#__PURE__*/_react["default"].createElement(Kpi, _extends({}, kpi, {
      labelColor: token.colorTextSecondary,
      subColor: token.colorTextTertiary,
      onClick: kpi.nav && onNavigate ? function () {
        // kpi.nav[2] optionally deep-links to a specific tab of the target module.
        if (kpi.nav[2]) {
          try {
            window.__iceShellStartTab = kpi.nav[2];
          } catch (e) {
            /* */
          }
        }

        onNavigate(kpi.nav[0], kpi.nav[1]);
      } : undefined
    })));
  }), /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 12,
    sm: 8,
    md: 6,
    xl: kpis.length > 6 ? 4 : 6
  }, /*#__PURE__*/_react["default"].createElement(UpgradeBox, {
    onCompare: onNavigate ? function () {
      // Deep-link into the marketplace's IceHrmPro comparison tab.
      try {
        window.__iceShellStartTab = 'tabIceHrmPro';
      } catch (e) {
        /* */
      }

      onNavigate('extension', 'marketplace|admin');
    } : undefined
  }))), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: 16
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: "Headcount growth",
    extra: /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
      color: "blue"
    }, /*#__PURE__*/_react["default"].createElement(_icons.RiseOutlined, null), " cumulative")
  }, /*#__PURE__*/_react["default"].createElement(Chart, {
    type: "area",
    height: 260,
    config: {
      data: data.headcountTrend || [],
      xField: 'year',
      yField: 'value',
      smooth: true,
      padding: 'auto',
      color: '#346CB0',
      areaStyle: {
        fill: "l(270) 0:".concat(token.colorBgContainer, " 1:#346CB0")
      },
      xAxis: _objectSpread({
        visible: true
      }, darkAxis),
      yAxis: _objectSpread({
        visible: true,
        min: 0
      }, darkAxis),
      point: {
        visible: false
      }
    }
  }))), /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: 8
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: "Gender diversity"
  }, /*#__PURE__*/_react["default"].createElement(Chart, {
    type: "donut",
    height: 260,
    config: donutCfg(data.genderDist)
  })))), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: 14
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: "Headcount by department"
  }, /*#__PURE__*/_react["default"].createElement(Chart, {
    type: "column",
    height: 250,
    config: {
      data: data.headcountByDept || [],
      xField: 'name',
      yField: 'value',
      padding: 'auto',
      color: '#5B8FF9',
      columnSize: 38,
      label: _objectSpread({
        visible: true,
        position: 'top'
      }, darkLabel),
      xAxis: _objectSpread({
        visible: true
      }, darkAxis),
      yAxis: _objectSpread({
        visible: true,
        min: 0
      }, darkAxis)
    }
  }))), /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: 10
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: "Employment type"
  }, /*#__PURE__*/_react["default"].createElement(Chart, {
    type: "donut",
    height: 250,
    config: donutCfg(data.employmentTypeDist)
  })))), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    md: 12,
    xl: 8
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: /*#__PURE__*/_react["default"].createElement("span", null, /*#__PURE__*/_react["default"].createElement(_icons.UserAddOutlined, null), " Recent hires")
  }, /*#__PURE__*/_react["default"].createElement(PeopleList, {
    data: data.recentHires,
    emptyText: "No recent hires",
    renderMeta: function renderMeta(it) {
      return /*#__PURE__*/_react["default"].createElement(Text, {
        type: "secondary",
        style: {
          fontSize: 12
        }
      }, it.title || 'Employee', ' · joined ', fmtDate(it.date));
    }
  }))), data.leave ? /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    md: 12,
    xl: 8
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: /*#__PURE__*/_react["default"].createElement("span", null, /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null), " Pending leave approvals"),
    extra: onNavigate ? /*#__PURE__*/_react["default"].createElement("a", {
      onClick: function onClick() {
        try {
          window.__iceShellStartTab = 'tabEmployeeLeave';
        } catch (e) {
          /* */
        }

        onNavigate('admin', 'leaves');
      }
    }, "View all") : null
  }, /*#__PURE__*/_react["default"].createElement(PeopleList, {
    data: data.leave.pendingList,
    emptyText: "No pending requests",
    renderMeta: function renderMeta(it) {
      return /*#__PURE__*/_react["default"].createElement(Text, {
        type: "secondary",
        style: {
          fontSize: 12
        }
      }, it.type || 'Leave', ' · ', fmtDate(it.start), it.end && it.end !== it.start ? " \u2013 ".concat(fmtDate(it.end)) : '');
    }
  }))) : null, data.celebrations && data.celebrations.length > 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    md: 12,
    xl: 8
  }, /*#__PURE__*/_react["default"].createElement(SectionCard, {
    title: /*#__PURE__*/_react["default"].createElement("span", null, /*#__PURE__*/_react["default"].createElement(_icons.GiftOutlined, null), " Celebrations")
  }, /*#__PURE__*/_react["default"].createElement(PeopleList, {
    data: data.celebrations,
    renderMeta: function renderMeta(it) {
      return /*#__PURE__*/_react["default"].createElement(Text, {
        type: "secondary",
        style: {
          fontSize: 12
        }
      }, it.type === 'birthday' ? /*#__PURE__*/_react["default"].createElement("span", null, /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null), " Birthday") : /*#__PURE__*/_react["default"].createElement("span", null, /*#__PURE__*/_react["default"].createElement(_icons.GiftOutlined, null), " ", it.years, "-year anniversary"), ' · ', fmtDate(it.date));
    }
  }))) : null));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","@antv/g2plot":"@antv/g2plot","antd":"antd","react":"react"}],5:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = EmployeeDashboard;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text;
var PALETTE = ['#346CB0', '#5AD8A6', '#5B8FF9', '#F6BD16', '#E8684A', '#9270CA', '#6DC8EC', '#FF99C3'];

function initials(name) {
  var p = (name || '').trim().split(/\s+/);
  return ((p[0] && p[0][0] || '') + (p[1] && p[1][0] || '')).toUpperCase() || '?';
}

function colorFor(name) {
  var h = 0;

  for (var i = 0; i < (name || '').length; i += 1) {
    h = h * 31 + name.charCodeAt(i) >>> 0;
  }

  return PALETTE[h % PALETTE.length];
}

function fmtDate(d) {
  if (!d) return '';
  var dt = new Date("".concat(d, "T00:00:00"));
  if (Number.isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric'
  });
}

function daysLabel(n) {
  if (n === 0) return 'Today';
  if (n === 1) return 'Tomorrow';
  return "in ".concat(n, " days");
} // Map a Task priority to a timeline dot icon (mirrors the legacy My To-Do List).


function todoDot(priority) {
  if (priority >= 1000) return /*#__PURE__*/_react["default"].createElement(_icons.AlertOutlined, {
    style: {
      color: '#d32f2f',
      fontSize: 16
    }
  });
  if (priority >= 100) return /*#__PURE__*/_react["default"].createElement(_icons.FireOutlined, {
    style: {
      color: '#e8684a',
      fontSize: 16
    }
  });
  if (priority >= 50) return /*#__PURE__*/_react["default"].createElement(_icons.WarningOutlined, {
    style: {
      color: '#ed6c02',
      fontSize: 16
    }
  });
  if (priority >= 20) return /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, {
    style: {
      color: '#1677ff',
      fontSize: 16
    }
  });
  return /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, {
    style: {
      color: '#2e7d32',
      fontSize: 16
    }
  });
} // Pull g/n out of a legacy task link so we can navigate within the SPA.


function routeFromLink(link) {
  try {
    var qs = link.indexOf('?') >= 0 ? link.split('?')[1] : link;
    var sp = new URLSearchParams(qs);
    var g = sp.get('g');
    var n = sp.get('n');
    return g && n ? {
      g: g,
      n: n
    } : null;
  } catch (e) {
    return null;
  }
}

function EmployeeDashboard(_ref) {
  var config = _ref.config,
      onNavigate = _ref.onNavigate,
      onOpenDocument = _ref.onOpenDocument;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];

  var _useState3 = (0, _react.useState)(true),
      _useState4 = _slicedToArray(_useState3, 2),
      loading = _useState4[0],
      setLoading = _useState4[1];

  var _useState5 = (0, _react.useState)(null),
      _useState6 = _slicedToArray(_useState5, 2),
      error = _useState6[0],
      setError = _useState6[1];

  var _useState7 = (0, _react.useState)(false),
      _useState8 = _slicedToArray(_useState7, 2),
      showAllTodo = _useState8[0],
      setShowAllTodo = _useState8[1];

  (0, _react.useEffect)(function () {
    var alive = true;
    setLoading(true);
    fetch("".concat(config.restApiBase, "appshell/employee-dashboard"), {
      headers: {
        Authorization: "Bearer ".concat(config.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (alive) {
        setData(d);
        setLoading(false);
      }
    })["catch"](function (e) {
      if (alive) {
        setError(e.message);
        setLoading(false);
      }
    });
    return function () {
      alive = false;
    };
  }, [config]);
  var greeting = (0, _react.useMemo)(function () {
    var h = new Date().getHours();
    return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }, []);
  var greetName = (0, _react.useMemo)(function () {
    var n = data && data.greetingName || '';

    if (n.indexOf('@') !== -1) {
      return n.split('@')[0].replace(/[._]+/g, ' ').replace(/\b\w/g, function (c) {
        return c.toUpperCase();
      });
    }

    return n;
  }, [data]);

  if (loading) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'center',
        padding: 80
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
  }

  if (error || !data) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 32
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "Could not load dashboard".concat(error ? ": ".concat(error) : '')
    }));
  }

  var att = data.attendance || {};
  var todo = data.todo || [];
  var cels = data.celebrations || [];
  var directReports = data.directReports || [];
  var teams = data.teams || []; // --- stat cards ----------------------------------------------------------

  var stats = [];

  if (att && att.hoursToday !== undefined) {
    stats.push({
      label: 'Hours today',
      value: "".concat((att.hoursToday || 0).toFixed(1), "h"),
      sub: att.punchedIn ? 'Clocked in' : att.punchedOutToday ? 'Clocked out' : 'Not clocked in',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
      color: '#346CB0',
      nav: ['modules', 'attendance']
    });
  }

  stats.push({
    label: 'Action items',
    value: todo.length,
    sub: 'On your to-do list',
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckSquareOutlined, null),
    color: '#5AD8A6'
  });

  if (data.leave) {
    stats.push({
      label: 'My pending leave',
      value: data.leave.pending || 0,
      sub: 'Awaiting approval',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CoffeeOutlined, null),
      color: '#F6BD16',
      nav: ['modules', 'leaves']
    });
  }

  if (data.isManager && data.teamStats) {
    stats.push({
      label: 'Direct reports',
      value: data.teamStats.reports || 0,
      sub: 'In your team',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
      color: '#9270CA',
      nav: ['admin', 'employees']
    });
    stats.push({
      label: 'Team on leave',
      value: data.teamStats.onLeaveToday || 0,
      sub: 'Today',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
      color: '#E8684A',
      nav: ['modules', 'leaves']
    });
  }

  var cardHead = function cardHead(icon, title, extra) {
    return {
      title: /*#__PURE__*/_react["default"].createElement("span", {
        style: {
          display: 'inline-flex',
          alignItems: 'center',
          gap: 8
        }
      }, icon, title),
      extra: extra
    };
  };

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 24,
      maxWidth: 1500,
      margin: '0 auto'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 18
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      fontSize: 22,
      fontWeight: 700
    }
  }, greeting, ",", ' ', greetName, ' ', "\uD83D\uDC4B"), /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary"
  }, "Here\u2019s your day at a glance.")), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16]
  }, stats.map(function (s) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Col, {
      xs: 12,
      sm: 8,
      md: data.isManager ? 6 : 8,
      xl: data.isManager ? 4 : 6,
      key: s.label
    }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      hoverable: true,
      onClick: function onClick() {
        return s.nav && onNavigate && onNavigate(s.nav[0], s.nav[1]);
      },
      style: {
        borderRadius: 12,
        boxShadow: _theme.MUI_SHADOW
      },
      styles: {
        body: {
          padding: 16
        }
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 12
      }
    }, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        width: 42,
        height: 42,
        borderRadius: 11,
        flex: '0 0 auto',
        fontSize: 19,
        background: "".concat(s.color, "22"),
        color: s.color,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }
    }, s.icon), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        minWidth: 0
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 22,
        fontWeight: 700,
        lineHeight: 1.1
      }
    }, s.value), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 12,
        color: token.colorTextSecondary
      }
    }, s.label))), s.sub && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 11.5,
        color: token.colorTextTertiary,
        marginTop: 8
      }
    }, s.sub)));
  })), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: data.isManager ? 12 : 14
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, _extends({}, cardHead( /*#__PURE__*/_react["default"].createElement(_icons.CheckSquareOutlined, {
    style: {
      color: '#5B8FF9'
    }
  }), 'My To-Do List'), {
    style: {
      borderRadius: 12,
      boxShadow: _theme.MUI_SHADOW
    }
  }), todo.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
    description: "You're all caught up \uD83C\uDF89"
  }) : /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Timeline, {
    items: (showAllTodo ? todo : todo.slice(0, 4)).map(function (t) {
      var route = t.link ? routeFromLink(t.link) : null;
      return {
        dot: todoDot(t.priority),
        children: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontWeight: 500
          }
        }, t.text), t.details && /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontSize: 12,
            color: token.colorTextTertiary,
            marginTop: 2
          }
        }, t.details), t.link && t.action && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
          type: "link",
          size: "small",
          style: {
            paddingLeft: 0
          },
          onClick: function onClick() {
            if (route && onNavigate) onNavigate(route.g, route.n);else window.location.href = t.link;
          }
        }, t.action))
      };
    })
  }), todo.length > 4 && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "primary",
    onClick: function onClick() {
      return setShowAllTodo(function (v) {
        return !v;
      });
    }
  }, showAllTodo ? 'Show less' : "View all ".concat(todo.length, " tasks"))))), /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24,
    lg: data.isManager ? 12 : 10
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, _extends({}, cardHead( /*#__PURE__*/_react["default"].createElement(_icons.GiftOutlined, {
    style: {
      color: '#E8684A'
    }
  }), 'Upcoming celebrations'), {
    style: {
      borderRadius: 12,
      boxShadow: _theme.MUI_SHADOW
    }
  }), cels.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
    description: "Nothing coming up"
  }) : /*#__PURE__*/_react["default"].createElement(_antd.List, {
    dataSource: cels,
    renderItem: function renderItem(c) {
      return /*#__PURE__*/_react["default"].createElement(_antd.List.Item, null, /*#__PURE__*/_react["default"].createElement(_antd.List.Item.Meta, {
        avatar: /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
          style: {
            background: colorFor(c.name)
          }
        }, initials(c.name)),
        title: /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            fontWeight: 600
          }
        }, c.name),
        description: c.type === 'birthday' ? "\uD83C\uDF82 Birthday \xB7 ".concat(fmtDate(c.date)) : "\uD83C\uDF89 ".concat(c.years, "yr anniversary \xB7 ").concat(fmtDate(c.date))
      }), /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
        color: c.days === 0 ? 'red' : 'default'
      }, daysLabel(c.days)));
    }
  })))), data.isManager && /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, _extends({}, cardHead( /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, {
    style: {
      color: '#9270CA'
    }
  }), 'Direct Reports', /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "link",
    size: "small",
    onClick: function onClick() {
      return onNavigate && onNavigate('admin', 'employees');
    }
  }, "View employees", /*#__PURE__*/_react["default"].createElement(_icons.RightOutlined, null))), {
    style: {
      borderRadius: 12,
      boxShadow: _theme.MUI_SHADOW
    }
  }), directReports.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
    description: "No direct reports"
  }) : /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [12, 12]
  }, directReports.map(function (m) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Col, {
      xs: 12,
      sm: 8,
      md: 6,
      xl: 4,
      key: m.id
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 10,
        padding: '8px 10px',
        border: "1px solid ".concat(token.colorBorderSecondary),
        borderRadius: 10
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
      style: {
        background: colorFor(m.name),
        flex: '0 0 auto'
      }
    }, initials(m.name)), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        minWidth: 0
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontWeight: 600,
        fontSize: 13,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, m.name), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 11,
        color: token.colorTextTertiary,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, m.title || '—'))));
  }))))), teams.length > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [16, 16],
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Col, {
    xs: 24
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, _extends({}, cardHead( /*#__PURE__*/_react["default"].createElement(_icons.UsergroupAddOutlined, {
    style: {
      color: '#0288d1'
    }
  }), 'My Teams', /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "link",
    size: "small",
    onClick: function onClick() {
      return onNavigate && onNavigate('extension', 'team|user');
    }
  }, "View teams", /*#__PURE__*/_react["default"].createElement(_icons.RightOutlined, null))), {
    style: {
      borderRadius: 12,
      boxShadow: _theme.MUI_SHADOW
    }
  }), /*#__PURE__*/_react["default"].createElement(_antd.Row, {
    gutter: [12, 12]
  }, teams.map(function (t) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Col, {
      xs: 24,
      sm: 12,
      md: 8,
      xl: 6,
      key: t.id
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 10,
        padding: '10px 12px',
        border: "1px solid ".concat(token.colorBorderSecondary),
        borderRadius: 10
      }
    }, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        width: 36,
        height: 36,
        borderRadius: 9,
        flex: '0 0 auto',
        background: (t.color || '#0288d1') + '22',
        color: t.color || '#0288d1',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }
    }, /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null)), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        minWidth: 0,
        flex: 1
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontWeight: 600,
        fontSize: 13,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, t.name), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 11,
        color: token.colorTextTertiary
      }
    }, "".concat(t.members, " member").concat(t.members === 1 ? '' : 's'))), t.role && t.role !== 'Member' && /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
      color: "blue"
    }, t.role)));
  }))))));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],6:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = ExpenseDialog;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text,
    Title = _antd.Typography.Title;
var STATUS_COLOR = {
  Pending: 'gold',
  Approved: 'green',
  Rejected: 'red',
  Paid: 'blue',
  Cancelled: 'default'
}; // Right-hand, vertically scrollable status-change log. Entries arrive newest-first
// from the backend (expense/{id}/logs), so the latest change is at the top.

function LogSidebar(_ref) {
  var logs = _ref.logs,
      loading = _ref.loading,
      token = _ref.token;
  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: 260,
      flexShrink: 0,
      borderLeft: "1px solid ".concat(token.colorBorderSecondary),
      paddingLeft: 16,
      display: 'flex',
      flexDirection: 'column',
      minHeight: 0
    }
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    strong: true,
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_icons.HistoryOutlined, {
    style: {
      marginRight: 6
    }
  }), "Status History"), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      overflowY: 'auto',
      flex: 1,
      maxHeight: 460,
      paddingRight: 4,
      paddingTop: 6
    }
  }, loading ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      textAlign: 'center',
      padding: 24
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
    size: "small"
  })) : !logs || logs.length === 0 ? /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary",
    style: {
      fontSize: 13
    }
  }, "No status changes yet.") : /*#__PURE__*/_react["default"].createElement(_antd.Timeline, {
    items: logs.map(function (l) {
      return {
        children: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontWeight: 600,
            fontSize: 13
          }
        }, "".concat(l.status_from || '', " \u2192 ").concat(l.status_to || '')), /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            color: token.colorTextTertiary,
            fontSize: 12
          }
        }, l.time), l.note && l.note.trim() ? /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontSize: 12,
            marginTop: 2
          }
        }, l.note.trim()) : null)
      };
    })
  })));
}
/**
 * Unified expense dialog with a status-history sidebar. Three modes:
 *  - 'view'     : read-only detail (admin/manager, and employees on non-editable rows)
 *  - 'edit'     : editable form + Save (owner / admin)
 *  - 'resubmit' : editable form + Re-submit -> sets a rejected expense back to Pending
 * A new, expense-only component — it does not touch the shared form/modal components.
 */


function ExpenseDialog(_ref2) {
  var open = _ref2.open,
      onClose = _ref2.onClose,
      rec = _ref2.rec,
      shellConfig = _ref2.shellConfig,
      _ref2$mode = _ref2.mode,
      mode = _ref2$mode === void 0 ? 'view' : _ref2$mode,
      onSaved = _ref2.onSaved,
      statusOptionsFor = _ref2.statusOptionsFor;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var isDark = token.colorBgContainer === _theme.MUI_DARK.paper;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      detail = _useState2[0],
      setDetail = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      loading = _useState4[0],
      setLoading = _useState4[1];

  var _useState5 = (0, _react.useState)(false),
      _useState6 = _slicedToArray(_useState5, 2),
      saving = _useState6[0],
      setSaving = _useState6[1];

  var _useState7 = (0, _react.useState)({}),
      _useState8 = _slicedToArray(_useState7, 2),
      values = _useState8[0],
      setValues = _useState8[1]; // Status-change (view dialog dropdown): the chosen target + its reason prompt.


  var _useState9 = (0, _react.useState)(null),
      _useState10 = _slicedToArray(_useState9, 2),
      statusTarget = _useState10[0],
      setStatusTarget = _useState10[1];

  var _useState11 = (0, _react.useState)(''),
      _useState12 = _slicedToArray(_useState11, 2),
      statusReason = _useState12[0],
      setStatusReason = _useState12[1];

  var _useState13 = (0, _react.useState)(false),
      _useState14 = _slicedToArray(_useState13, 2),
      statusSaving = _useState14[0],
      setStatusSaving = _useState14[1];

  var editable = mode === 'edit' || mode === 'resubmit';

  var loadDetail = function loadDetail(withSpinner) {
    if (withSpinner) {
      setLoading(true);
    }

    return fetch("".concat(shellConfig.restApiBase, "expense/").concat(rec.id, "/detail"), {
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (d && !d.error) {
        setDetail(d);
        setValues({
          category: d.category,
          expense_date: d.expense_date,
          payment_method: d.payment_method,
          payee: d.payee,
          currency: d.currency,
          amount: d.amount,
          transaction_no: d.transaction_no,
          notes: d.notes
        });
      } else {
        setDetail({});
      }
    })["catch"](function () {
      return setDetail({});
    })["finally"](function () {
      return setLoading(false);
    });
  };

  (0, _react.useEffect)(function () {
    if (!open || !rec) {
      setDetail(null);
      setValues({});
      return;
    }

    setDetail(null);
    setStatusTarget(null);
    setStatusReason('');
    loadDetail(true); // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, rec, shellConfig]);
  if (!rec) return null;
  var status = detail && detail.status || rec.status || '—';
  var attachments = detail && detail.attachments || [];
  var logs = detail && detail.logs || [];
  var options = detail && detail.options || {};
  var employeeName = detail && detail.employee_name || rec.employee || 'Employee';
  var heroBg = isDark ? 'rgba(25,118,210,0.14)' : 'rgba(25,118,210,0.06)';

  var setV = function setV(k, v) {
    return setValues(function (prev) {
      return _objectSpread({}, prev, _defineProperty({}, k, v));
    });
  };

  var doSave = function doSave() {
    var endpoint = mode === 'resubmit' ? 'resubmit' : 'update';
    setSaving(true);
    fetch("".concat(shellConfig.restApiBase, "expense/").concat(rec.id, "/").concat(endpoint), {
      method: 'POST',
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token),
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify(values)
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (d && d.error) {
        var msg = d.error[0] && d.error[0][0] ? d.error[0][0].message : 'Could not save';

        _antd.message.error(msg, 5);

        return;
      }

      _antd.message.success(mode === 'resubmit' ? 'Expense re-submitted' : 'Expense saved');

      if (onSaved) onSaved();
      onClose();
    })["catch"](function () {
      return _antd.message.error('Could not save', 5);
    })["finally"](function () {
      return setSaving(false);
    });
  }; // Status change from the view dialog (admin/manager). A reason is mandatory and
  // all rules are enforced by the backend; on success we refresh in place so the
  // new status + log entry show immediately.


  var submitStatusChange = function submitStatusChange() {
    if (!statusTarget || !statusReason.trim()) return;
    setStatusSaving(true);
    fetch("".concat(shellConfig.restApiBase, "expense/").concat(rec.id, "/status"), {
      method: 'POST',
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token),
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        status: statusTarget.value,
        reason: statusReason
      })
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (d && d.error) {
        var msg = d.error[0] && d.error[0][0] ? d.error[0][0].message : 'Could not change status';

        _antd.message.error(msg, 5);

        return;
      }

      _antd.message.success('Status updated');

      setStatusTarget(null);
      setStatusReason('');
      if (onSaved) onSaved();
      loadDetail(false);
    })["catch"](function () {
      return _antd.message.error('Could not change status', 5);
    })["finally"](function () {
      return setStatusSaving(false);
    });
  };

  var statusOpts = !editable && typeof statusOptionsFor === 'function' && detail && detail.status ? statusOptionsFor(detail.status) : [];
  var footer = editable ? [/*#__PURE__*/_react["default"].createElement(_antd.Button, {
    key: "cancel",
    onClick: onClose
  }, "Cancel"), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    key: "save",
    type: "primary",
    loading: saving,
    onClick: doSave
  }, mode === 'resubmit' ? 'Re-submit' : 'Save')] : [/*#__PURE__*/_react["default"].createElement(_antd.Button, {
    key: "close",
    onClick: onClose
  }, "Close"), statusOpts.length > 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Dropdown, {
    key: "status",
    trigger: ['click'],
    menu: {
      items: statusOpts.map(function (o) {
        return {
          key: String(o.value),
          label: o.label
        };
      }),
      onClick: function onClick(_ref3) {
        var key = _ref3.key;
        setStatusReason('');
        setStatusTarget(statusOpts.find(function (o) {
          return String(o.value) === String(key);
        }) || null);
      }
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "primary"
  }, "Change Status", /*#__PURE__*/_react["default"].createElement(_icons.DownOutlined, null))) : null];
  var currencyLabel = detail && detail.currency_name || rec.currency || '';
  return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: open,
    onCancel: onClose,
    title: null,
    width: 840,
    footer: footer,
    styles: {
      body: {
        paddingTop: 8
      }
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 20
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 14,
      marginBottom: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
    size: 52,
    src: rec.image || undefined,
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
    style: {
      flexShrink: 0
    }
  }), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      flex: 1,
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement(Title, {
    level: 5,
    style: {
      margin: 0
    },
    ellipsis: true
  }, employeeName), /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary",
    style: {
      fontSize: 13
    }
  }, detail && detail.category_name || rec.category || 'Expense')), /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
    color: STATUS_COLOR[status] || 'default',
    style: {
      fontWeight: 600,
      marginInlineEnd: 0
    }
  }, status)), loading ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      textAlign: 'center',
      padding: 40
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, null)) : editable ? /*#__PURE__*/_react["default"].createElement(_antd.Form, {
    layout: "vertical",
    size: "small"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Amount",
    style: {
      flex: 1
    },
    required: true
  }, /*#__PURE__*/_react["default"].createElement(_antd.InputNumber, {
    style: {
      width: '100%'
    },
    value: values.amount,
    onChange: function onChange(v) {
      return setV('amount', v);
    },
    stringMode: true
  })), /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Currency",
    style: {
      width: 140
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    showSearch: true,
    optionFilterProp: "label",
    value: values.currency,
    onChange: function onChange(v) {
      return setV('currency', v);
    },
    options: (options.currencies || []).map(function (o) {
      return {
        value: o.id,
        label: o.name
      };
    })
  }))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Category",
    style: {
      flex: 1
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    showSearch: true,
    optionFilterProp: "label",
    value: values.category,
    onChange: function onChange(v) {
      return setV('category', v);
    },
    options: (options.categories || []).map(function (o) {
      return {
        value: o.id,
        label: o.name
      };
    })
  })), /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Payment Method",
    style: {
      flex: 1
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    showSearch: true,
    optionFilterProp: "label",
    value: values.payment_method,
    onChange: function onChange(v) {
      return setV('payment_method', v);
    },
    options: (options.paymentMethods || []).map(function (o) {
      return {
        value: o.id,
        label: o.name
      };
    })
  }))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Expense Date",
    style: {
      flex: 1
    }
  }, /*#__PURE__*/_react["default"].createElement("input", {
    type: "date",
    value: values.expense_date || '',
    onChange: function onChange(e) {
      return setV('expense_date', e.target.value);
    },
    style: {
      width: '100%',
      height: 24,
      padding: '0 8px',
      borderRadius: 6,
      border: "1px solid ".concat(token.colorBorder),
      background: token.colorBgContainer,
      color: token.colorText,
      colorScheme: isDark ? 'dark' : 'light'
    }
  })), /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Payee / Merchant",
    style: {
      flex: 1
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Input, {
    value: values.payee || '',
    onChange: function onChange(e) {
      return setV('payee', e.target.value);
    }
  }))), /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Transaction / Ref No",
    style: {
      marginBottom: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Input, {
    value: values.transaction_no || '',
    onChange: function onChange(e) {
      return setV('transaction_no', e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement(_antd.Form.Item, {
    label: "Notes",
    style: {
      marginBottom: 4
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Input.TextArea, {
    rows: 2,
    value: values.notes || '',
    onChange: function onChange(e) {
      return setV('notes', e.target.value);
    }
  }))) : /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: heroBg,
      borderRadius: 12,
      padding: '16px 20px',
      marginBottom: 18,
      display: 'flex',
      alignItems: 'baseline',
      gap: 8
    }
  }, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      fontSize: 30,
      fontWeight: 700,
      color: token.colorText,
      lineHeight: 1
    }
  }, rec.amount != null ? rec.amount : detail && detail.amount || '—'), /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      fontSize: 15,
      fontWeight: 600,
      color: token.colorTextSecondary
    }
  }, currencyLabel)), /*#__PURE__*/_react["default"].createElement(_antd.Descriptions, {
    column: 1,
    size: "small",
    bordered: true,
    labelStyle: {
      width: 150,
      color: token.colorTextSecondary
    },
    items: [{
      key: 'date',
      label: 'Expense Date',
      children: detail && detail.expense_date || rec.expense_date || '—'
    }, {
      key: 'pm',
      label: 'Payment Method',
      children: detail && detail.payment_method_name || rec.payment_method || '—'
    }, {
      key: 'payee',
      label: 'Payee',
      children: detail && detail.payee || rec.payee || '—'
    }, {
      key: 'txn',
      label: 'Transaction No',
      children: detail && detail.transaction_no || '—'
    }, {
      key: 'submitted',
      label: 'Submitted',
      children: detail && detail.created || '—'
    }]
  }), detail && detail.notes ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    strong: true,
    style: {
      display: 'block',
      marginBottom: 6
    }
  }, "Notes"), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: token.colorFillQuaternary,
      borderRadius: 8,
      padding: '10px 12px',
      whiteSpace: 'pre-wrap',
      color: token.colorText
    }
  }, detail.notes)) : null), attachments.length > 0 && /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Divider, {
    style: {
      margin: '16px 0 12px'
    },
    orientation: "left",
    plain: true
  }, /*#__PURE__*/_react["default"].createElement(_icons.FileImageOutlined, {
    style: {
      marginRight: 6
    }
  }), "Receipts"), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      flexWrap: 'wrap',
      gap: 12
    }
  }, attachments.map(function (a) {
    return a.isImage ? /*#__PURE__*/_react["default"].createElement("div", {
      key: a.field,
      style: {
        textAlign: 'center'
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Image, {
      src: a.url,
      alt: a.label,
      width: 82,
      height: 82,
      style: {
        objectFit: 'cover',
        borderRadius: 8,
        border: "1px solid ".concat(token.colorBorderSecondary)
      }
    }), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 12,
        color: token.colorTextSecondary,
        marginTop: 4
      }
    }, a.label)) : /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      key: a.field,
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      href: a.url,
      target: "_blank",
      rel: "noreferrer"
    }, a.label);
  })))), /*#__PURE__*/_react["default"].createElement(LogSidebar, {
    logs: logs,
    loading: loading,
    token: token
  }))), /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: !!statusTarget,
    title: "Change Status",
    okText: "Update",
    confirmLoading: statusSaving,
    okButtonProps: {
      disabled: !statusReason.trim()
    },
    onOk: submitStatusChange,
    onCancel: function onCancel() {
      return setStatusTarget(null);
    },
    width: 440
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 10
    }
  }, "Set status to", ' ', /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
    color: STATUS_COLOR[statusTarget && statusTarget.value] || 'default',
    style: {
      fontWeight: 600
    }
  }, statusTarget && statusTarget.label)), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 6,
      color: token.colorTextSecondary
    }
  }, /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      color: token.colorError,
      marginRight: 4
    }
  }, "*"), "Reason"), /*#__PURE__*/_react["default"].createElement(_antd.Input.TextArea, {
    rows: 3,
    value: statusReason,
    onChange: function onChange(e) {
      return setStatusReason(e.target.value);
    },
    status: !statusReason.trim() ? 'error' : undefined,
    placeholder: "Reason for this status change\u2026"
  })));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],7:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = LeaveCalcChart;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _g2plot = require("@antv/g2plot");

var _LeaveCalcParser = _interopRequireDefault(require("./LeaveCalcParser"));

var _theme = require("./theme");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var Text = _antd.Typography.Text,
    Title = _antd.Typography.Title;

var fmt = function fmt(v) {
  var n = Number(v);
  if (!Number.isFinite(n)) return '0';
  return Number.isInteger(n) ? String(n) : String(Math.round(n * 1000) / 1000);
}; // Thin g2plot 1.x wrapper (mirrors the shell Dashboard chart wrapper). The
// bundled g2plot is 1.x, so grouping uses the dedicated GroupedColumn plot and
// the { visible: true } config style. Dark mode is handled by feeding theme
// colours into the axis/label config rather than g2plot's opaque 'dark' theme.


function Plot(_ref) {
  var Ctor = _ref.ctor,
      config = _ref.config,
      _ref$height = _ref.height,
      height = _ref$height === void 0 ? 260 : _ref$height;
  var ref = (0, _react.useRef)(null);
  (0, _react.useEffect)(function () {
    if (!ref.current) return undefined;
    var plot;

    try {
      plot = new Ctor(ref.current, config);
      plot.render();
    } catch (e) {
      /* ignore render errors */
    }

    return function () {
      try {
        if (plot) plot.destroy();
      } catch (e) {
        /* */
      }
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [Ctor, JSON.stringify(config), height]);
  return /*#__PURE__*/_react["default"].createElement("div", {
    ref: ref,
    style: {
      height: height
    }
  });
}
/**
 * LeaveCalcChart — renders a graphical view of how a leave type's entitlement
 * was calculated, parsed from the raw "How is this calculated?" log lines.
 *
 * For leave types that carry forward, it charts the year-over-year propagation
 * (carried in vs carried to next) plus a per-period breakdown table. For simple
 * types it charts the current-period derivation (allocated → joined-date →
 * accrual). The raw log is preserved in a collapsible panel.
 */


function LeaveCalcChart(_ref2) {
  var lines = _ref2.lines;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var model = (0, _react.useMemo)(function () {
    return new _LeaveCalcParser["default"](lines || []).parse();
  }, [lines]);
  var isDark = token.colorBgContainer === _theme.MUI_DARK.paper;
  var darkAxis = isDark ? {
    label: {
      style: {
        fill: 'rgba(255,255,255,0.45)'
      }
    },
    line: {
      style: {
        stroke: 'rgba(255,255,255,0.25)'
      }
    },
    grid: {
      line: {
        style: {
          stroke: 'rgba(255,255,255,0.12)'
        }
      }
    }
  } : {};
  var darkLegend = isDark ? {
    text: {
      style: {
        fill: 'rgba(255,255,255,0.85)'
      }
    }
  } : {};
  var darkLabel = isDark ? {
    style: {
      fill: 'rgba(255,255,255,0.85)'
    }
  } : {};
  var hasCarry = model.carryForward.length > 0; // --- Chart data ---------------------------------------------------------
  // Two series tell the propagation story: how much was allocated each period vs
  // how much actually carried forward to the next one (the rest expires / is used).

  var carryData = [];
  model.carryForward.forEach(function (p) {
    carryData.push({
      period: p.label,
      type: 'Allocated',
      value: p.allocated + p.pto
    });
    carryData.push({
      period: p.label,
      type: 'Carried to next',
      value: p.carriedToNext
    });
  });
  var derivationData = [];
  if (model.totalForPeriod != null) derivationData.push({
    step: 'Allocated',
    value: model.totalForPeriod
  });
  if (model.joinedDateAdjusted) derivationData.push({
    step: 'After join date',
    value: model.afterJoinedDate
  });
  if (model.accrualApplied) derivationData.push({
    step: 'After accrual',
    value: model.afterAccrue
  });
  var carryConfig = {
    data: carryData,
    xField: 'period',
    yField: 'value',
    groupField: 'type',
    color: ['#5B8FF9', '#5AD8A6'],
    legend: _objectSpread({
      visible: true,
      position: 'top-center'
    }, darkLegend),
    label: {
      visible: false
    },
    xAxis: _objectSpread({
      visible: true,
      title: {
        visible: true,
        text: 'Period'
      }
    }, darkAxis),
    yAxis: _objectSpread({
      visible: true,
      min: 0,
      title: {
        visible: true,
        text: 'Leaves'
      }
    }, darkAxis)
  };
  var derivationConfig = {
    data: derivationData,
    xField: 'step',
    yField: 'value',
    color: '#5B8FF9',
    columnSize: 48,
    label: _objectSpread({
      visible: true,
      position: 'top'
    }, darkLabel),
    xAxis: _objectSpread({
      visible: true
    }, darkAxis),
    yAxis: _objectSpread({
      visible: true,
      min: 0,
      title: {
        visible: true,
        text: 'Leaves'
      }
    }, darkAxis)
  }; // --- Per-period breakdown table ----------------------------------------

  var tableData = model.carryForward.map(function (p, i) {
    return _objectSpread({
      key: i
    }, p);
  });
  var columns = [{
    title: 'Period',
    dataIndex: 'label',
    key: 'label'
  }, {
    title: 'Carried in',
    dataIndex: 'carriedIn',
    key: 'carriedIn',
    render: fmt
  }, {
    title: 'Allocated',
    key: 'allocated',
    render: function render(_, r) {
      return fmt(r.allocated + r.pto);
    }
  }, {
    title: 'Taken',
    dataIndex: 'taken',
    key: 'taken',
    render: fmt
  }, {
    title: 'Valid till',
    dataIndex: 'validTill',
    key: 'validTill'
  }, {
    title: 'Carried to next',
    dataIndex: 'carriedToNext',
    key: 'carriedToNext',
    render: function render(v) {
      return /*#__PURE__*/_react["default"].createElement(Text, {
        strong: true
      }, fmt(v));
    }
  }];

  var rawPanel = /*#__PURE__*/_react["default"].createElement(_antd.Collapse, {
    ghost: true,
    items: [{
      key: 'raw',
      label: 'Show calculation log',
      children: /*#__PURE__*/_react["default"].createElement("ul", {
        style: {
          fontSize: 12.5,
          paddingLeft: 18,
          margin: 0,
          color: token.colorTextSecondary
        }
      }, (lines || []).map(function (line, i) {
        return (
          /*#__PURE__*/
          // eslint-disable-next-line react/no-array-index-key
          _react["default"].createElement("li", {
            key: i,
            style: {
              marginBottom: 3
            }
          }, String(line).replace(/^\(client=[^)]*\)\s*/, ''))
        );
      }))
    }]
  });

  if (!hasCarry && derivationData.length === 0) {
    return /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "No calculation details available"
    }), rawPanel);
  }

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Descriptions, {
    size: "small",
    column: 2,
    bordered: true
  }, /*#__PURE__*/_react["default"].createElement(_antd.Descriptions.Item, {
    label: "Leave type"
  }, model.leaveType || '—'), /*#__PURE__*/_react["default"].createElement(_antd.Descriptions.Item, {
    label: "Current period"
  }, model.period ? "".concat(model.period.from, " \u2192 ").concat(model.period.to) : '—'), /*#__PURE__*/_react["default"].createElement(_antd.Descriptions.Item, {
    label: "Allocated for period"
  }, fmt(model.totalForPeriod || 0)), /*#__PURE__*/_react["default"].createElement(_antd.Descriptions.Item, {
    label: "Available this period"
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    strong: true
  }, fmt(model.afterAccrue != null ? model.afterAccrue : model.totalForPeriod || 0)), model.accrualApplied ? /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
    color: "blue",
    style: {
      marginLeft: 8
    }
  }, "accrued") : null)), hasCarry ? /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement(Title, {
    level: 5,
    style: {
      marginBottom: 4
    }
  }, "Carry-forward propagation"), /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary",
    style: {
      fontSize: 12.5
    }
  }, "How leaves carried across ".concat(model.carryForward.length, " past period(s) into the current one.")), /*#__PURE__*/_react["default"].createElement(Plot, {
    ctor: _g2plot.GroupedColumn,
    config: carryConfig
  }), /*#__PURE__*/_react["default"].createElement(_antd.Table, {
    size: "small",
    columns: columns,
    dataSource: tableData,
    pagination: false,
    scroll: {
      y: 220
    },
    style: {
      marginTop: 8
    }
  })) : /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement(Title, {
    level: 5,
    style: {
      marginBottom: 4
    }
  }, "How this period was calculated"), /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary",
    style: {
      fontSize: 12.5
    }
  }, "Entitlement for the current period, step by step."), /*#__PURE__*/_react["default"].createElement(Plot, {
    ctor: _g2plot.Column,
    config: derivationConfig,
    height: 220
  })), rawPanel);
}

},{"./LeaveCalcParser":8,"./theme":26,"@antv/g2plot":"@antv/g2plot","antd":"antd","react":"react"}],8:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * LeaveCalcParser — turns the free-text "How is this calculated?" leave
 * entitlement log (an array of strings produced by LeavesActionManager's
 * LogManager::collectLogs) into a structured model that a chart can render.
 *
 * The log has two parts:
 *  1. A header describing the current period entitlement:
 *       Leave Type (Annual leave) [id (1)]
 *       Leave Period After Adjust : 2026-01-01 - 2026-12-31
 *       Total for leave for current period: 14
 *       Total after adjusted based on joined date: 14
 *       Total after leave accrue: 7.154
 *       Number of past leave periods: 0
 *  2. Zero or more carry-forward blocks, one per past period, each delimited by
 *     "<hr/>" and starting with "Calculating leave days carried forward from
 *     [Period FROM - TO]", then carried-in / allocated / taken / carried-to-next.
 *
 * Lines may be prefixed with "(client=<name>) " and the first leave type in a
 * batch is preceded by "Calculating Leave Entitlement" + the employee name;
 * both are ignored here.
 */
var stripPrefix = function stripPrefix(line) {
  return String(line == null ? '' : line).replace(/^\(client=[^)]*\)\s*/, '').trim();
};

var toNum = function toNum(s) {
  var n = Number(s);
  return Number.isFinite(n) ? n : 0;
};

var LeaveCalcParser = /*#__PURE__*/function () {
  function LeaveCalcParser(lines) {
    _classCallCheck(this, LeaveCalcParser);

    this.lines = Array.isArray(lines) ? lines.map(stripPrefix) : [];
  }
  /**
   * @returns {{
   *   leaveType: string, leaveTypeId: (number|null),
   *   period: ({from: string, to: string}|null),
   *   totalForPeriod: (number|null), afterJoinedDate: (number|null),
   *   afterAccrue: (number|null), accrualApplied: boolean,
   *   joinedDateAdjusted: boolean, pastPeriodCount: number,
   *   carryForward: Array<{
   *     label: string, from: string, to: string,
   *     carriedIn: number, allocated: number, pto: number,
   *     taken: number, deducted: number, validTill: string, carriedToNext: number,
   *   }>,
   * }}
   */


  _createClass(LeaveCalcParser, [{
    key: "parse",
    value: function parse() {
      var model = {
        leaveType: '',
        leaveTypeId: null,
        period: null,
        totalForPeriod: null,
        afterJoinedDate: null,
        afterAccrue: null,
        accrualApplied: false,
        joinedDateAdjusted: false,
        pastPeriodCount: 0,
        carryForward: []
      };
      var current = null;

      var flush = function flush() {
        if (current) {
          model.carryForward.push(current);
          current = null;
        }
      };

      this.lines.forEach(function (line) {
        if (!line || line === '<hr/>') return;
        var m;

        if (m = line.match(/^Leave Type \((.+)\) \[id \((\d+)\)\]$/)) {
          if (!model.leaveType) {
            model.leaveType = m[1];
            model.leaveTypeId = Number(m[2]);
          }

          return;
        }

        if (m = line.match(/^Leave Period After Adjust\s*:\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})/)) {
          model.period = {
            from: m[1],
            to: m[2]
          };
          return;
        }

        if (m = line.match(/^Total for leave for current period:\s*([\d.]+)/)) {
          model.totalForPeriod = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Total after adjusted based on joined date:\s*([\d.]+)/)) {
          model.afterJoinedDate = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Total after leave accrue:\s*([\d.]+)/)) {
          model.afterAccrue = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Number of past leave periods:\s*(\d+)/)) {
          model.pastPeriodCount = Number(m[1]);
          return;
        }

        if (m = line.match(/^Calculating leave days carried forward from \[Period\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})\]/)) {
          flush();
          current = {
            from: m[1],
            to: m[2],
            label: m[1].slice(0, 4),
            carriedIn: 0,
            allocated: 0,
            pto: 0,
            taken: 0,
            deducted: 0,
            validTill: '',
            carriedToNext: 0
          };
          return;
        }

        if (!current) return; // remaining patterns only apply inside a carry block

        if (m = line.match(/^Number of leaves carried from previous period:\s*([\d.-]+)/)) {
          current.carriedIn = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Number of allocated \[.*\]:\s*leave\(([\d.-]+)\)\s*\+\s*PTO\(([\d.-]+)\)\s*=\s*([\d.-]+)/)) {
          current.allocated = toNum(m[1]);
          current.pto = toNum(m[2]);
          return;
        }

        if (m = line.match(/^Leave days from previous period is valid till:\s*(\d{4}-\d{2}-\d{2})/)) {
          current.validTill = m[1];
          return;
        }

        if (m = line.match(/^Total number of leave days taken between .*:\s*([\d.-]+)/)) {
          current.taken = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Number of leave deducted from carried forward leaves from previous period:\s*([\d.-]+)/)) {
          current.deducted = toNum(m[1]);
          return;
        }

        if (m = line.match(/^Number of leaves carried to next period:\s*([\d.-]+)/)) {
          current.carriedToNext = toNum(m[1]);
          return;
        }
      });
      flush();

      var near = function near(a, b) {
        return a != null && b != null && Math.abs(a - b) > 1e-9;
      };

      model.accrualApplied = near(model.afterAccrue, model.afterJoinedDate);
      model.joinedDateAdjusted = near(model.afterJoinedDate, model.totalForPeriod);
      return model;
    }
    /** Convenience: parse and return only the carry-forward periods. */

  }], [{
    key: "parse",
    value: function parse(lines) {
      return new LeaveCalcParser(lines).parse();
    }
  }]);

  return LeaveCalcParser;
}();

exports["default"] = LeaveCalcParser;

},{}],9:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = LeaveCalendar;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var pad = function pad(n) {
  return Number(n) < 10 ? "0".concat(n) : "".concat(n);
};
/**
 * Leave Calendar (modules::leavecal) — a month/year calendar showing the
 * employee's and their direct reports' leaves, plus holidays. Data comes from
 * the leave-calendar REST endpoint, fetched with the shell's auth token.
 */


function LeaveCalendar(_ref) {
  var shellConfig = _ref.shellConfig;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var _useState = (0, _react.useState)({}),
      _useState2 = _slicedToArray(_useState, 2),
      byDate = _useState2[0],
      setByDate = _useState2[1]; // 'YYYY-MM-DD' -> [{ employee, status }]


  var _useState3 = (0, _react.useState)([]),
      _useState4 = _slicedToArray(_useState3, 2),
      holidays = _useState4[0],
      setHolidays = _useState4[1];

  var _useState5 = (0, _react.useState)({}),
      _useState6 = _slicedToArray(_useState5, 2),
      byMonth = _useState6[0],
      setByMonth = _useState6[1]; // year -> { 'YYYY-MM': [{ employee, count }] }


  var apiGet = (0, _react.useCallback)(function (path) {
    var base = shellConfig && shellConfig.restApiBase || '';
    return fetch("".concat(base).concat(path), {
      headers: {
        Authorization: "Bearer ".concat(shellConfig && shellConfig.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    });
  }, [shellConfig]);
  var loadMonth = (0, _react.useCallback)(function (year, month) {
    apiGet("leave-calendar/month/".concat(year, "/").concat(pad(month))).then(function (d) {
      setByDate(function (prev) {
        return _objectSpread({}, prev, {}, d && d.leave || {});
      });
      setHolidays(d && d.holidays || []);
    })["catch"](function () {
      /* ignore */
    });
  }, [apiGet]);
  var loadYear = (0, _react.useCallback)(function (year) {
    apiGet("leave-calendar/year/".concat(year)).then(function (d) {
      return setByMonth(function (prev) {
        return _objectSpread({}, prev, {}, d || {});
      });
    })["catch"](function () {
      /* ignore */
    });
  }, [apiGet]);
  (0, _react.useEffect)(function () {
    var now = new Date();
    loadMonth(now.getFullYear(), now.getMonth() + 1);
  }, [loadMonth]);

  var onPanelChange = function onPanelChange(value, mode) {
    if (mode === 'month') loadMonth(value.year(), value.month() + 1);else loadYear(value.year());
  };

  var dateCell = function dateCell(value) {
    var key = "".concat(value.year(), "-").concat(pad(value.month() + 1), "-").concat(pad(value.date()));
    var list = byDate[key] || [];
    var holiday = holidays.find(function (h) {
      return h && h.dateh === key;
    });
    if (!holiday && !list.length) return null;
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        marginTop: 4
      }
    }, holiday ? /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
      message: holiday.name,
      type: "warning",
      showIcon: true,
      style: {
        marginBottom: 6,
        padding: '1px 8px'
      }
    }) : null, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        flexWrap: 'wrap',
        gap: 6
      }
    }, list.map(function (item, i) {
      return /*#__PURE__*/_react["default"].createElement(_antd.Tooltip // eslint-disable-next-line react/no-array-index-key
      , {
        key: "".concat(item.employee.id, "-").concat(i),
        title: "".concat(item.employee.name, " \xB7 ").concat(item.status),
        color: "#108ee9"
      }, /*#__PURE__*/_react["default"].createElement(_antd.Badge, {
        color: item.status === 'Approved' ? 'green' : 'orange',
        dot: true
      }, /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
        size: "small",
        src: item.employee.image
      }, item.employee.name ? item.employee.name.charAt(0) : '?')));
    })));
  };

  var monthCell = function monthCell(value) {
    var yd = byMonth[value.year()];
    var mk = "".concat(value.year(), "-").concat(pad(value.month() + 1));
    var list = yd && yd[mk] || [];
    if (!list.length) return null;
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        marginTop: 8,
        display: 'flex',
        flexWrap: 'wrap',
        gap: 8
      }
    }, list.map(function (item, i) {
      return /*#__PURE__*/_react["default"].createElement(_antd.Tooltip // eslint-disable-next-line react/no-array-index-key
      , {
        key: "".concat(item.employee.id, "-").concat(i),
        title: item.employee.name,
        color: "#108ee9"
      }, /*#__PURE__*/_react["default"].createElement(_antd.Badge, {
        color: "green",
        size: "small",
        count: item.count
      }, /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
        size: "small",
        src: item.employee.image
      }, item.employee.name ? item.employee.name.charAt(0) : '?')));
    }));
  };

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: token.colorBgContainer,
      borderRadius: 10,
      padding: 12
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Calendar, {
    cellRender: function cellRender(current, info) {
      return info.type === 'date' ? dateCell(current) : monthCell(current);
    },
    onPanelChange: onPanelChange
  }));
}

},{"antd":"antd","react":"react"}],10:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = LeaveEntitlement;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

var _LeaveCalcChart = _interopRequireDefault(require("./LeaveCalcChart"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text; // Semantic colours that read on both light and dark grounds. Kept out of the
// theme tokens on purpose: these encode leave state (remaining / pending /
// taken), which is separate from the app's blue accent.

var C = {
  available: '#2e9e50',
  availableSoft: 'rgba(46,158,80,0.14)',
  used: '#7c879a',
  pending: '#e08321',
  danger: '#d64545'
};

var num = function num(v) {
  var n = Number(v);
  if (Number.isNaN(n)) return '0';
  return Number.isInteger(n) ? String(n) : String(Math.round(n * 1000) / 1000);
};

var pct = function pct(part, denom) {
  return denom > 0 ? Math.max(0, Math.min(100, part / denom * 100)) : 0;
};
/**
 * Leave Entitlement — one summary card per leave type. The card answers, at a
 * glance, "how much do I have left and can I book it": a hero Available figure,
 * a proportion meter (taken / pending / available of the entitlement), quiet
 * supporting detail, and an Apply action prefilled to that leave type.
 *
 * Data comes from the bundled adapter's getEntitlement action; Apply opens the
 * main leave adapter's "Apply Leave" modal preselected to this type.
 */


function LeaveEntitlement(_ref) {
  var tabKey = _ref.tabKey;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      rows = _useState2[0],
      setRows = _useState2[1];

  var _useState3 = (0, _react.useState)(null),
      _useState4 = _slicedToArray(_useState3, 2),
      calc = _useState4[0],
      setCalc = _useState4[1]; // { name, lines: [] }


  (0, _react.useEffect)(function () {
    var cancelled = false;
    var tries = 0;

    var tick = function tick() {
      if (cancelled) return;
      var m = (window.modJsList || {})[tabKey];

      if (m && typeof m.fetchEntitlement === 'function') {
        m.fetchEntitlement().then(function (data) {
          if (!cancelled) setRows(Array.isArray(data) ? data : []);
        })["catch"](function () {
          if (!cancelled) setRows([]);
        });
        return;
      }

      tries += 1;

      if (tries > 100) {
        setRows([]);
        return;
      }

      setTimeout(tick, 100);
    };

    tick();
    return function () {
      cancelled = true;
    };
  }, [tabKey]); // Open the "Apply Leave" modal (owned by the All My Leaves adapter) with the
  // leave type preselected. renderForm() sets up its own portal-rendered modal,
  // so this works even though that tab isn't the active one.

  var applyFor = function applyFor(leaveTypeId) {
    var adapter = (window.modJsList || {}).tabMyLeaveAll || window.modJs;
    if (!adapter || typeof adapter.renderForm !== 'function') return;

    var open = function open() {
      return adapter.renderForm({
        leave_type: leaveTypeId
      });
    }; // The Apply form's Leave Type select reads its options from the adapter's
    // master data, which is only fetched when that tab's list loads. Opening
    // from here (that tab was never visited) needs it loaded first, otherwise
    // the select shows the raw id instead of the type name. Cached after first load.


    var reader = adapter.masterDataReader;

    if (reader && typeof reader.updateAllMasterData === 'function') {
      reader.updateAllMasterData().then(open, open);
    } else {
      open();
    }
  };

  if (rows === null) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'center',
        padding: 60
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
  }

  if (!rows.length) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "No leave entitlement for the current period"
    });
  }

  var chipStyle = function chipStyle(variant) {
    return {
      display: 'inline-flex',
      alignItems: 'center',
      gap: 6,
      fontSize: 12,
      fontWeight: 600,
      padding: '4px 10px',
      borderRadius: 999,
      background: variant === 'warn' ? 'rgba(224,131,33,0.14)' : token.colorFillQuaternary,
      color: variant === 'warn' ? C.pending : token.colorTextSecondary,
      border: variant === 'warn' ? '1px solid transparent' : "1px solid ".concat(token.colorBorderSecondary)
    };
  };

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))',
      gap: 18
    }
  }, rows.map(function (r) {
    var total = Number(r.totalLeaves) || 0;
    var approved = Number(r.approvedLeaves) || 0;
    var pending = Number(r.pendingLeaves) || 0;
    var available = Number(r.availableLeaves) || 0;
    var carried = Number(r.carriedForward) || 0;
    var adj = Number(r.paidTimeOff) || 0;
    var accrue = Number(r.tobeAccrued) || 0;
    var denom = Math.max(total, approved + pending + available, 0.0001);
    var usedW = pct(approved, denom);
    var pendW = pct(pending, denom);
    var availW = pct(available, denom);
    var isZero = available <= 0;
    var isLow = !isZero && total > 0 && (available <= 1 || available / total <= 0.15);
    var heroColor = isZero ? C.danger : isLow ? C.pending : C.available;
    return /*#__PURE__*/_react["default"].createElement("div", {
      key: r.id,
      style: {
        background: token.colorBgContainer,
        border: "1px solid ".concat(token.colorBorderSecondary),
        borderRadius: 14,
        boxShadow: _theme.MUI_SHADOW,
        padding: '20px 22px 18px',
        display: 'flex',
        flexDirection: 'column',
        gap: 16
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 13
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: 42,
        height: 42,
        borderRadius: 11,
        flex: '0 0 auto',
        display: 'grid',
        placeItems: 'center',
        background: 'rgba(25,118,210,0.10)',
        color: token.colorPrimary,
        fontSize: 20
      }
    }, /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null)), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        flex: 1,
        minWidth: 0
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 16.5,
        fontWeight: 650,
        letterSpacing: '-0.01em'
      }
    }, r.name), /*#__PURE__*/_react["default"].createElement(Text, {
      type: "secondary",
      style: {
        fontSize: 12.5
      }
    }, "Current period")), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        textAlign: 'right',
        flex: '0 0 auto'
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 30,
        fontWeight: 750,
        lineHeight: 1,
        color: heroColor,
        fontVariantNumeric: 'tabular-nums',
        letterSpacing: '-0.02em'
      }
    }, num(available), /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        fontSize: 14,
        fontWeight: 600,
        color: token.colorTextSecondary,
        marginLeft: 3
      }
    }, Math.abs(available) === 1 ? 'day' : 'days')), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 11.5,
        letterSpacing: '0.04em',
        textTransform: 'uppercase',
        color: token.colorTextTertiary,
        marginTop: 3,
        fontWeight: 600
      }
    }, "Available"))), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        flexDirection: 'column',
        gap: 9
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      role: "img",
      "aria-label": "Of ".concat(num(total), " entitled: ").concat(num(approved), " taken, ").concat(num(pending), " pending, ").concat(num(available), " available"),
      style: {
        height: 12,
        borderRadius: 999,
        background: token.colorFillTertiary,
        display: 'flex',
        overflow: 'hidden'
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: "".concat(usedW, "%"),
        background: C.used
      }
    }), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: "".concat(pendW, "%"),
        background: "repeating-linear-gradient(45deg, ".concat(C.pending, ", ").concat(C.pending, " 5px, rgba(255,255,255,0.45) 5px, rgba(255,255,255,0.45) 10px)")
      }
    }), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: "".concat(availW, "%"),
        background: C.available
      }
    })), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        flexWrap: 'wrap',
        gap: '4px 16px',
        fontSize: 12.5,
        color: token.colorTextSecondary,
        fontVariantNumeric: 'tabular-nums'
      }
    }, approved > 0 && /*#__PURE__*/_react["default"].createElement(LegendItem, {
      color: C.used,
      label: "Taken",
      value: approved,
      token: token
    }), pending > 0 && /*#__PURE__*/_react["default"].createElement(LegendItem, {
      color: C.pending,
      label: "Pending",
      value: pending,
      token: token
    }), /*#__PURE__*/_react["default"].createElement(LegendItem, {
      color: C.available,
      label: "Available",
      value: available,
      token: token
    }), /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        marginLeft: 'auto',
        color: token.colorTextTertiary
      }
    }, "of ", /*#__PURE__*/_react["default"].createElement("b", {
      style: {
        color: token.colorText,
        fontWeight: 650
      }
    }, num(total)), " entitled"))), (isLow || isZero || carried > 0 && r.carriedForwardLeaveExpireDate || accrue > 0 || adj !== 0) && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        flexWrap: 'wrap',
        gap: 7
      }
    }, isZero && /*#__PURE__*/_react["default"].createElement("span", {
      style: chipStyle('warn')
    }, /*#__PURE__*/_react["default"].createElement(_icons.WarningOutlined, null), "None left this period"), isLow && /*#__PURE__*/_react["default"].createElement("span", {
      style: chipStyle('warn')
    }, /*#__PURE__*/_react["default"].createElement(_icons.WarningOutlined, null), "Running low"), carried > 0 && r.carriedForwardLeaveExpireDate && /*#__PURE__*/_react["default"].createElement("span", {
      style: chipStyle('warn')
    }, /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null), "".concat(num(r.carriedForwardAvailable), " carried-forward expire ").concat(r.carriedForwardLeaveExpireDate)), accrue > 0 && /*#__PURE__*/_react["default"].createElement("span", {
      style: chipStyle()
    }, "+".concat(num(accrue), " still to accrue this period")), adj !== 0 && /*#__PURE__*/_react["default"].createElement("span", {
      style: chipStyle()
    }, "Adjustment ".concat(adj > 0 ? '+' : '').concat(num(adj)))), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: 12,
        marginTop: 2
      }
    }, Array.isArray(r.calculation) && r.calculation.length ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      type: "link",
      size: "small",
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      style: {
        padding: 0
      },
      onClick: function onClick() {
        return setCalc({
          name: r.name,
          lines: r.calculation
        });
      }
    }, "How is this calculated?") : /*#__PURE__*/_react["default"].createElement("span", null), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      type: "primary",
      icon: /*#__PURE__*/_react["default"].createElement(_icons.PlusOutlined, null),
      onClick: function onClick() {
        return applyFor(r.id);
      }
    }, "Apply")));
  }), /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: !!calc,
    title: calc ? "".concat(calc.name, " \u2014 how this is calculated") : '',
    footer: [/*#__PURE__*/_react["default"].createElement(_antd.Button, {
      key: "c",
      onClick: function onClick() {
        return setCalc(null);
      }
    }, "Close")],
    onCancel: function onCancel() {
      return setCalc(null);
    },
    width: 760
  }, calc ? /*#__PURE__*/_react["default"].createElement(_LeaveCalcChart["default"], {
    lines: calc.lines
  }) : null));
}

function LegendItem(_ref2) {
  var color = _ref2.color,
      label = _ref2.label,
      value = _ref2.value,
      token = _ref2.token;
  return /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      display: 'inline-flex',
      alignItems: 'center',
      gap: 6
    }
  }, /*#__PURE__*/_react["default"].createElement("i", {
    style: {
      width: 9,
      height: 9,
      borderRadius: 3,
      flex: '0 0 auto',
      background: color,
      display: 'inline-block'
    }
  }), label, " ", /*#__PURE__*/_react["default"].createElement("b", {
    style: {
      color: token.colorText,
      fontWeight: 650
    }
  }, num(value)));
}

},{"./LeaveCalcChart":7,"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],11:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = LicenseBlocked;

var _react = _interopRequireDefault(require("react"));

var _antd = require("antd");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function LicenseBlocked(_ref) {
  var license = _ref.license;
  var lic = license || {};
  var missing = !lic.has_license;
  var expiredOn = lic.expiry_date ? String(lic.expiry_date).split(' ')[0] : null;
  var title = missing ? 'No active IceHrmPro license' : 'Your IceHrmPro license has expired';
  var subTitle = missing ? 'This feature is part of IceHrmPro and needs an active subscription to use.' : "Your IceHrmPro license".concat(expiredOn ? " expired on ".concat(expiredOn) : ' has expired', ". Renew it to keep using this feature.");

  var goToMarketplace = function goToMarketplace() {
    if (typeof window !== 'undefined') {
      window.location.hash = "#".concat(encodeURIComponent('extension::marketplace|admin'));
    }
  };

  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 24
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Result, {
    status: "warning",
    title: title,
    subTitle: subTitle,
    extra: lic.is_admin ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      type: "primary",
      onClick: goToMarketplace
    }, "Go to Marketplace") : null
  }));
}

},{"antd":"antd","react":"react"}],12:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = LicenseRenewalBanner;

var _react = _interopRequireDefault(require("react"));

var _antd = require("antd");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function LicenseRenewalBanner(_ref) {
  var licenseRenewal = _ref.licenseRenewal;
  if (!licenseRenewal) return null;
  var daysLeft = licenseRenewal.daysLeft,
      expiryDate = licenseRenewal.expiryDate,
      isAdmin = licenseRenewal.isAdmin,
      renewUrl = licenseRenewal.renewUrl;
  var dateStr = expiryDate ? String(expiryDate).split(' ')[0] : null;
  var whenLabel = daysLeft <= 0 ? 'today' : daysLeft === 1 ? 'in 1 day' : "in ".concat(daysLeft, " days");
  return /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "warning",
    banner: true,
    showIcon: true,
    style: {
      padding: '18px 28px',
      alignItems: 'center',
      marginBottom: 16
    },
    message: /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        lineHeight: 1.35
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 17,
        fontWeight: 700,
        marginBottom: 3
      }
    }, "Your IceHrmPro license expires ", whenLabel), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 14,
        opacity: 0.9
      }
    }, dateStr ? "It expires on ".concat(dateStr, ". ") : '', "Renew now to keep access to your premium features without interruption.")),
    action: isAdmin && renewUrl ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "large",
      type: "primary",
      href: renewUrl,
      target: "_blank",
      rel: "noopener noreferrer",
      style: {
        fontWeight: 600
      }
    }, "Renew IceHrm Pro") : null
  });
}

},{"antd":"antd","react":"react"}],13:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = MobileApp;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text,
    Paragraph = _antd.Typography.Paragraph; // Native "Mobile App" tab for modules::employees — mirrors the legacy
// index.php panels (app-store badges, one-time login code, API access token).
// The login-code request reuses the legacy MobileAppAdapter (window.modJsList
// .tabMobileApp) so it hits the same backend custom action; we just swap its
// DOM-writing callbacks for React state.

function MobileApp() {
  var adapter = (window.modJsList || {}).tabMobileApp || null;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      code = _useState2[0],
      setCode = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      loading = _useState4[0],
      setLoading = _useState4[1];

  var requestCode = function requestCode() {
    if (!adapter) return;
    setLoading(true);

    adapter.loginCodeSuccessCallback = function (cb) {
      setLoading(false);
      var v = Array.isArray(cb) ? cb[0] : cb;
      var out = v && _typeof(v) === 'object' ? v.code || v.data || v.loginCode || JSON.stringify(v) : v;
      setCode(out != null ? String(out) : null);
    };

    adapter.loginCodeFailCallBack = function () {
      setLoading(false);

      _antd.message.error('Could not get a login code. Please try again later.', 5);
    };

    try {
      adapter.getOneTimeLoginCode();
    } catch (e) {
      setLoading(false);
    }
  };

  var cardStyle = {
    borderRadius: 12,
    boxShadow: _theme.MUI_SHADOW,
    marginBottom: 16
  };
  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: '100%'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    style: cardStyle,
    title: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, /*#__PURE__*/_react["default"].createElement(_icons.MobileOutlined, null), "Download Mobile App")
  }, /*#__PURE__*/_react["default"].createElement(Paragraph, {
    type: "secondary"
  }, "Access IceHrm on the go. Download our mobile app for iOS or Android."), /*#__PURE__*/_react["default"].createElement(_antd.Space, {
    wrap: true
  }, /*#__PURE__*/_react["default"].createElement("a", {
    href: "https://apps.apple.com/gb/app/icehrm/id1624346692",
    target: "_blank",
    rel: "noopener noreferrer"
  }, /*#__PURE__*/_react["default"].createElement("img", {
    src: "https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg",
    alt: "Download on the App Store",
    style: {
      height: 50
    }
  })), /*#__PURE__*/_react["default"].createElement("a", {
    href: "https://play.google.com/store/apps/details?id=com.icehrm.m3&hl=en",
    target: "_blank",
    rel: "noopener noreferrer"
  }, /*#__PURE__*/_react["default"].createElement("img", {
    src: "https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg",
    alt: "Get it on Google Play",
    style: {
      height: 50
    }
  })))), /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    style: cardStyle,
    title: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, /*#__PURE__*/_react["default"].createElement(_icons.LockOutlined, null), "Mobile Authentication Code")
  }, /*#__PURE__*/_react["default"].createElement(Paragraph, {
    type: "secondary"
  }, "Use this one-time code to securely log in to the mobile app."), code ? /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "success",
    showIcon: true,
    style: {
      marginBottom: 12
    },
    message: /*#__PURE__*/_react["default"].createElement(Text, {
      strong: true,
      copyable: true,
      style: {
        fontSize: 18,
        letterSpacing: 1
      }
    }, code)
  }) : null, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "primary",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.LockOutlined, null),
    loading: loading,
    onClick: requestCode
  }, "Request One-time Login Code")));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],14:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = ModuleSearch;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * GA-style global module search for the top bar. A searchable dropdown of every
 * module the user can reach (from the shell's route map), grouped by area.
 * Selecting one navigates there. Filtering matches the module label and its area
 * name, so "leave" or "time" both surface the right entries.
 */
function ModuleSearch(_ref) {
  var items = _ref.items,
      areas = _ref.areas,
      onSelect = _ref.onSelect,
      dark = _ref.dark;

  var _useState = (0, _react.useState)(undefined),
      _useState2 = _slicedToArray(_useState, 2),
      value = _useState2[0],
      setValue = _useState2[1];

  var areaLabel = (0, _react.useMemo)(function () {
    var m = {};
    (areas || []).forEach(function (a) {
      m[a.id] = a.label;
    });
    return m;
  }, [areas]);

  var sectionLabel = function sectionLabel(s) {
    return s === 'mine' ? 'Personal' : 'Manage';
  }; // Group options by area. Two modules can share a label within an area — the
  // admin (Manage) twin and the self-service (Personal) twin — so tag each option
  // with its section to disambiguate (e.g. "Task Lists" Manage vs Personal). The
  // search string includes label + area + section so any of them matches.


  var options = (0, _react.useMemo)(function () {
    var byArea = {};
    (items || []).forEach(function (it) {
      var a = it.area || 'more';
      if (!byArea[a]) byArea[a] = [];
      var aLabel = areaLabel[a] || a;
      var sec = sectionLabel(it.section);
      byArea[a].push({
        value: it.key,
        name: it.label,
        section: sec,
        // antd renders this for the option + selected value.
        label: /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: 12
          }
        }, /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
          }
        }, it.label), /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            flex: '0 0 auto',
            fontSize: 11,
            fontWeight: 500,
            padding: '0 7px',
            lineHeight: '17px',
            borderRadius: 9,
            color: it.section === 'mine' ? '#0958d9' : '#389e0d',
            background: it.section === 'mine' ? '#e6f0ff' : '#f0fbe6'
          }
        }, sec)),
        search: "".concat(it.label, " ").concat(aLabel, " ").concat(sec).toLowerCase()
      });
    });
    return Object.keys(byArea).sort(function (x, y) {
      return (areaLabel[x] || x).localeCompare(areaLabel[y] || y);
    }).map(function (a) {
      return {
        label: areaLabel[a] || a,
        options: byArea[a].sort(function (p, q) {
          return p.name.localeCompare(q.name);
        })
      };
    });
  }, [items, areaLabel]);
  return /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    showSearch: true,
    value: value,
    placeholder: "Search for a module\u2026",
    suffixIcon: /*#__PURE__*/_react["default"].createElement(_icons.SearchOutlined, {
      style: {
        fontSize: 15
      }
    }),
    options: options // Match against our combined label+area search string.
    ,
    filterOption: function filterOption(input, option) {
      if (!option || option.options) return false; // skip group headers

      return (option.search || '').includes((input || '').toLowerCase());
    },
    onChange: function onChange(key) {
      if (key && onSelect) onSelect(key);
      setValue(undefined);
    },
    onSelect: function onSelect() {
      return setValue(undefined);
    },
    allowClear: true,
    style: {
      width: '100%'
    },
    popupMatchSelectWidth: false,
    listHeight: 420,
    variant: "filled",
    "aria-label": "Search modules"
  });
}

},{"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],15:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = NativeAdapterView;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * Mounts a module tab whose ADAPTER draws its own view — e.g. the settings
 * module, whose initTable() renders the bespoke SettingsPage (grouped inline
 * controls) instead of a table. The shell provides the Table/Form/FilterForm
 * containers (via setContainers) and triggers the adapter's get(), exactly as
 * the legacy footer would; everything else is the module's own code.
 */
function NativeAdapterView(_ref) {
  var tabKey = _ref.tabKey;
  var tableRef = (0, _react.useRef)(null);
  var formRef = (0, _react.useRef)(null);
  var filterRef = (0, _react.useRef)(null);

  var _useState = (0, _react.useState)(false),
      _useState2 = _slicedToArray(_useState, 2),
      ready = _useState2[0],
      setReady = _useState2[1];

  (0, _react.useEffect)(function () {
    var cancelled = false;
    var tries = 0;

    var tick = function tick() {
      if (cancelled) return;
      var m = (window.modJsList || {})[tabKey];

      if (m && tableRef.current) {
        if (typeof m.setContainers === 'function') {
          m.setContainers({
            Table: tableRef.current,
            Form: formRef.current,
            FilterForm: filterRef.current
          });
        } // The instance persists across remounts — force a re-render into the
        // current containers.


        m.tableInitialized = false;
        m.formInitialized = false;
        window.modJs = m;

        try {
          if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
            m.masterDataReader.updateAllMasterData();
          }
        } catch (e) {
          /* ignore */
        }

        try {
          m.get([]);
        } catch (e) {
          /* ignore */
        }

        setReady(true);
        return;
      }

      tries += 1;
      if (tries <= 100) setTimeout(tick, 100);
    };

    tick();
    return function () {
      cancelled = true;
    };
  }, [tabKey]);
  return /*#__PURE__*/_react["default"].createElement("div", null, !ready && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      padding: 40
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
    size: "large"
  })), /*#__PURE__*/_react["default"].createElement("div", {
    ref: tableRef
  }), /*#__PURE__*/_react["default"].createElement("div", {
    ref: formRef
  }), /*#__PURE__*/_react["default"].createElement("div", {
    ref: filterRef
  }));
}

},{"antd":"antd","react":"react"}],16:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = NativeCardList;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

var _ExpenseDialog = _interopRequireDefault(require("./ExpenseDialog"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _createForOfIteratorHelper(o) { if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (o = _unsupportedIterableToArray(o))) { var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e2) { throw _e2; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var it, normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e3) { didErr = true; err = _e3; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

// Icon name (from declarative extension card config) -> component.
var ICON_BY_NAME = {
  file: /*#__PURE__*/_react["default"].createElement(_icons.FileOutlined, null),
  form: /*#__PURE__*/_react["default"].createElement(_icons.FormOutlined, null),
  "export": /*#__PURE__*/_react["default"].createElement(_icons.ExportOutlined, null),
  monitor: /*#__PURE__*/_react["default"].createElement(_icons.MonitorOutlined, null)
};

var iconByName = function iconByName(name) {
  return ICON_BY_NAME[name] || /*#__PURE__*/_react["default"].createElement(_icons.FileOutlined, null);
}; // Lighten a hex colour toward white by `amt` (0..1) so the saturated action-icon
// hues (tuned for light mode) stay legible on the dark sidebar/cards.


function lighten(hex, amt) {
  var m = /^#?([0-9a-fA-F]{6})$/.exec(hex || '');
  if (!m) return hex;
  var n = parseInt(m[1], 16);
  var r = n >> 16 & 255;
  var g = n >> 8 & 255;
  var b = n & 255;
  r = Math.round(r + (255 - r) * amt);
  g = Math.round(g + (255 - g) * amt);
  b = Math.round(b + (255 - b) * amt);
  return "rgb(".concat(r, ", ").concat(g, ", ").concat(b, ")");
}

var PAGE_SIZE = 8; // Lazy, idempotent loader for an extension bundle that exposes a native
// document-mount function (e.g. the editor's window.mountEditorDocument). A
// per-session cache-bust keeps it fresh across hard reloads without re-fetching
// the (large) bundle on every modal open.

var NATIVE_CB = String(Date.now());
var nativeBundles = {};

function loadNativeBundle(url) {
  if (nativeBundles[url]) return nativeBundles[url];
  var p = new Promise(function (resolve, reject) {
    var s = document.createElement('script');
    s.src = "".concat(url).concat(url.indexOf('?') >= 0 ? '&' : '?', "cb=").concat(NATIVE_CB);
    s.async = false;

    s.onload = function () {
      return resolve();
    };

    s.onerror = function () {
      s.remove();
      reject(new Error("Failed to load ".concat(url)));
    };

    document.head.appendChild(s);
  }); // Evict on failure so the next open retries instead of replaying the rejection.

  nativeBundles[url] = p["catch"](function (e) {
    delete nativeBundles[url];
    throw e;
  });
  return nativeBundles[url];
} // Injected into a same-origin document-editor iframe to hide the legacy page
// chrome (top bar + sidebar) so only the editor content shows.


var EMBED_CSS = "\n  header.header { display: none !important; }\n  aside.left-side, .sidebar-offcanvas, .skeletonSideMenu { display: none !important; }\n  .right-side { margin-left: 0 !important; left: 0 !important; }\n  .wrapper, body, html { padding-top: 0 !important; margin-top: 0 !important; background: #fff !important; }\n  body { min-width: 0 !important; }\n  #DemoModeNotice, #IceHrmConnectionNotice { display: none !important; }\n"; // A representative icon + accent colour per entity (falls back to a generic one).

var ENTITY_STYLE = {
  JobTitle: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.IdcardOutlined, null),
    color: '#1976d2'
  },
  PayGrade: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  EmploymentStatus: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProfileOutlined, null),
    color: '#7b1fa2'
  },
  Skill: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#1976d2'
  },
  Education: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#0288d1'
  },
  Certification: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#7b1fa2'
  },
  Language: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#ed6c02'
  },
  Project: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProjectOutlined, null),
    color: '#1976d2'
  },
  EmployeeProject: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#2e7d32'
  },
  Client: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.BankOutlined, null),
    color: '#7b1fa2'
  },
  CustomField: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    color: '#1976d2'
  },
  Audit: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AuditOutlined, null),
    color: '#546e7a'
  },
  EmailLogEntry: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.MailOutlined, null),
    color: '#0288d1'
  },
  Employee: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
    color: '#1976d2'
  },
  EmployeeCareer: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#0288d1'
  },
  EmployeeSkill: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#1976d2'
  },
  EmployeeEducation: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#0288d1'
  },
  EmployeeCertification: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#7b1fa2'
  },
  EmployeeLanguage: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#ed6c02'
  },
  EmployeeDependent: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#2e7d32'
  },
  EmergencyContact: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ContactsOutlined, null),
    color: '#d32f2f'
  },
  TerminatedEmployee: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserDeleteOutlined, null),
    color: '#ed6c02'
  },
  ArchivedEmployee: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
    color: '#546e7a'
  },
  EmployeeDataHistory: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProfileOutlined, null),
    color: '#0288d1'
  },
  MyAttendance: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    color: '#0288d1'
  },
  OvertimeCategory: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FieldTimeOutlined, null),
    color: '#ed6c02'
  },
  MyOvertime: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    color: '#ed6c02'
  },
  EmployeeOvertime: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    color: '#ed6c02'
  },
  EmployeeOvertimeApproval: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    color: '#7b1fa2'
  },
  User: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
    color: '#1976d2'
  },
  UserRole: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#7b1fa2'
  },
  UserInvitation: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.MailOutlined, null),
    color: '#0288d1'
  },
  LeaveType: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TagsOutlined, null),
    color: '#ed6c02'
  },
  LeavePeriod: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ScheduleOutlined, null),
    color: '#2e7d32'
  },
  WorkDay: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CarryOutOutlined, null),
    color: '#0288d1'
  },
  HoliDay: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.GiftOutlined, null),
    color: '#7b1fa2'
  },
  LeaveStartingBalance: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SlidersOutlined, null),
    color: '#0288d1'
  },
  LeaveGroup: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#1976d2'
  },
  EmployeeLeave: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#ed6c02'
  },
  // User leaves (modules::leaves)
  MyLeave: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
    color: '#1976d2'
  },
  MyLeaveApproved: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
    color: '#2e7d32'
  },
  SubLeave: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#ed6c02'
  },
  LeaveApproval: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#7b1fa2'
  },
  // Expenses extension
  ExpensesCategory: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TagsOutlined, null),
    color: '#7b1fa2'
  },
  ExpensesPaymentMethod: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  EmployeeExpense: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#1976d2'
  },
  MyExpense: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#1976d2'
  },
  SubExpense: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#ed6c02'
  },
  ExpenseApproval: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#7b1fa2'
  },
  // Job positions (extension::jobpositions|admin)
  Job: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AuditOutlined, null),
    color: '#1976d2'
  },
  // Candidates (extension::candidates|admin)
  Candidate: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UsergroupAddOutlined, null),
    color: '#7b1fa2'
  },
  // Recruitment setup (extension::jobsetup|admin)
  EmployementType: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.IdcardOutlined, null),
    color: '#1976d2'
  },
  ExperienceLevel: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SlidersOutlined, null),
    color: '#ed6c02'
  },
  JobFunction: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#7b1fa2'
  },
  EducationLevel: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#2e7d32'
  },
  Benifit: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.GiftOutlined, null),
    color: '#d81b60'
  },
  // System (admin::settings / admin::modules / admin::permissions)
  Setting: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null),
    color: '#546e7a'
  },
  Module: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    color: '#1976d2'
  },
  Permission: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.KeyOutlined, null),
    color: '#7b1fa2'
  },
  // Metadata (admin::metadata) — master data lookups.
  Country: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.GlobalOutlined, null),
    color: '#1976d2'
  },
  Province: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#0288d1'
  },
  CurrencyType: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  Nationality: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.IdcardOutlined, null),
    color: '#7b1fa2'
  },
  Ethnicity: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#ed6c02'
  },
  ImmigrationStatus: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#d81b60'
  },
  // Documents (admin::documents / modules::documents)
  CompanyDocument: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FileTextOutlined, null),
    color: '#1976d2'
  },
  Document: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TagsOutlined, null),
    color: '#7b1fa2'
  },
  EmployeeDocument: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FileOutlined, null),
    color: '#0288d1'
  },
  PayslipDocument: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  MyDocument: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FileOutlined, null),
    color: '#0288d1'
  },
  MyCompanyDocument: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FileTextOutlined, null),
    color: '#1976d2'
  },
  MyPayslip: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  // Training (modules::training)
  TrainingSessionWithCourse: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#1976d2'
  },
  EmployeeTrainingSession: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#2e7d32'
  },
  SubEmployeeTraining: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#ed6c02'
  },
  CoordinatedTrainingSession: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ScheduleOutlined, null),
    color: '#7b1fa2'
  },
  // Performance (admin::performance / modules::performance)
  PerformanceReview: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#7b1fa2'
  },
  ReviewFeedback: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FormOutlined, null),
    color: '#0288d1'
  },
  ReviewTemplate: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProfileOutlined, null),
    color: '#ed6c02'
  },
  EmployeeGoal: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
    color: '#2e7d32'
  },
  MyPerformanceReview: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#1976d2'
  },
  MyReviewFeedback: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FormOutlined, null),
    color: '#ed6c02'
  },
  // Salary (admin::salary)
  SalaryComponentType: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TagsOutlined, null),
    color: '#7b1fa2'
  },
  SalaryComponent: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    color: '#0288d1'
  },
  EmployeeSalary: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#2e7d32'
  },
  // Loans (admin::loans / modules::loans)
  CompanyLoan: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.BankOutlined, null),
    color: '#2e7d32'
  },
  EmployeeCompanyLoan: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#1976d2'
  },
  MyLoan: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DollarOutlined, null),
    color: '#1976d2'
  },
  // Travel (admin::travel)
  TravelProject: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProjectOutlined, null),
    color: '#0288d1'
  },
  EmployeeTravelRecord: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SendOutlined, null),
    color: '#1976d2'
  },
  MyTravel: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SendOutlined, null),
    color: '#1976d2'
  },
  SubTravel: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SendOutlined, null),
    color: '#ed6c02'
  },
  TravelApproval: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SendOutlined, null),
    color: '#7b1fa2'
  }
};

var entityStyle = function entityStyle(e) {
  return ENTITY_STYLE[e] || {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    color: '#607d8b'
  };
}; // Icon + colour for a custom-field Object Type value (the type column).


var OBJECT_TYPE_STYLE = {
  Employee: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
    color: '#1976d2'
  },
  CompanyStructure: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#0288d1'
  },
  Project: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ProjectOutlined, null),
    color: '#7b1fa2'
  },
  Client: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.BankOutlined, null),
    color: '#2e7d32'
  },
  JobTitle: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.IdcardOutlined, null),
    color: '#ed6c02'
  },
  Qualification: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReadOutlined, null),
    color: '#1976d2'
  },
  Recruitment: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.SolutionOutlined, null),
    color: '#7b1fa2'
  },
  Document: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FileTextOutlined, null),
    color: '#546e7a'
  },
  Leave: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
    color: '#ed6c02'
  }
};

var objectTypeStyle = function objectTypeStyle(t) {
  return OBJECT_TYPE_STYLE[t] || {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    color: '#607d8b'
  };
}; // Per-entity card overrides: which column is the title, what to hide from the
// meta line, which fields to show as a Tag, dynamic icons, and whether to hide
// the dedicated View button (row click still views).
// Shared status -> tag colour map for performance review cards (admin + employee
// lists), so a review's status reads the same everywhere.


var REVIEW_STATUS_COLORS = {
  Pending: 'gold',
  Submitted: 'green',
  Completed: 'blue',
  Rejected: 'red'
};
var ENTITY_CONFIG = {
  // AttendanceModal renders its own antd Modal (controlled by `element`), so the
  // card list must NOT also wrap it in a Modal (that showed two modals).
  // Show the employee's profile photo as the card avatar, name as the title.
  Attendance: {
    childSelfModal: true,
    avatarField: 'image',
    titleField: 'employee',
    hideMeta: ['image']
  },
  AttendanceStatus: {
    avatarField: 'image',
    titleField: 'employee',
    hideMeta: ['image'],
    hideActions: true // read-only status view — no row actions / Add New

  },
  // The employee's own attendance (modules::attendance) — one card per punch day.
  // Title by clock-in time; the detail view is AttendanceModal (self-modal).
  MyAttendance: {
    childSelfModal: true,
    titleField: 'in_time',
    hideMeta: ['image']
  },
  // Overtime — the employee's own requests (modules::overtime own tab) have no
  // employee column, so title by category. The admin / subordinate / approval
  // adapters carry the employee photo + name (like AttendanceStatus).
  MyOvertime: {
    titleField: 'category'
  },
  EmployeeOvertime: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['image']
  },
  EmployeeOvertimeApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['image']
  },
  JobTitle: {
    titleField: 'name'
  },
  Language: {
    titleField: 'description'
  },
  CustomField: {
    title: function title(r) {
      return "".concat(r.type || '', " \u2192 ").concat(r.name || '');
    },
    hideMeta: ['name', 'type', 'display'],
    iconField: 'type'
  },
  Audit: {
    title: function title(r) {
      return r.details;
    },
    tagFields: ['time'],
    tagIcon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    hideMeta: ['details', 'time'],
    hideViewButton: true
  },
  EmailLogEntry: {
    titleField: 'subject',
    hideMeta: ['subject']
  },
  Project: {
    hideViewButton: true
  },
  EmployeeProject: {
    hideViewButton: true
  },
  Client: {
    hideViewButton: true
  },
  Employee: {
    title: function title(r) {
      return "".concat(r.first_name || '', " ").concat(r.last_name || '').trim() || r.employee_id;
    },
    avatarField: 'image',
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [{
      key: 'switch',
      tip: 'Switch to profile',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.LoginOutlined, null),
      color: '#ed6c02',
      method: 'setAdminProfile',
      show: function show(m) {
        return !!m.allowSwitchToEmployeeProfile;
      }
    }, {
      key: 'resign',
      tip: 'Initiate resignation',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.UserDeleteOutlined, null),
      color: '#d32f2f',
      method: 'terminateEmployee',
      show: function show(m) {
        return m.hasAccess('delete') && m.showDelete !== false;
      }
    }]
  },
  TerminatedEmployee: {
    title: function title(r) {
      return "".concat(r.first_name || '', " ").concat(r.last_name || '').trim() || r.employee_id;
    },
    avatarField: 'image',
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [{
      key: 'activate',
      tip: 'Activate',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
      color: '#2e7d32',
      method: 'activateEmployee'
    }]
  },
  ArchivedEmployee: {
    title: function title(r) {
      return "".concat(r.first_name || '', " ").concat(r.last_name || '').trim() || r.employee_id;
    },
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [{
      key: 'download',
      tip: 'Download',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#0288d1',
      method: 'download'
    }]
  },
  EmployeeDataHistory: {
    titleField: 'employee',
    tagFields: ['created'],
    tagIcon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
    hideMeta: ['created']
  },
  // Leave groups (admin::leaves) — no view; the "Add Employees" action opens a
  // dialog to view the group and manage its members.
  LeaveGroup: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    // Clicking the card opens the manage-members dialog; the icon action does too.
    cardClickAction: 'manageGroupEmployees',
    extraActions: [{
      key: 'members',
      tip: 'Manage members',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.UsergroupAddOutlined, null),
      color: '#1677ff',
      method: 'manageGroupEmployees',
      first: true
    }]
  },
  // Leave rules (admin::leaves → Leave Rule). Title by leave type name, plus the
  // employee name when the rule targets a specific employee (most rules apply to
  // everyone and have no employee). No dedicated View button — clicking the card
  // opens the rule (row-click still views).
  LeaveRule: {
    title: function title(r, get) {
      var type = get('leave_type') || 'Leave Rule';
      return r.employee ? "".concat(type, " \u2014 ").concat(get('employee')) : type;
    },
    hideViewButton: true,
    hideMeta: ['id', 'leave_type', 'employee']
  },
  // Employee Leave List (admin::leaves → Employee Leave List). Mirrors the
  // legacy three actions: Leave Days (details + logs), Leave Status (approve
  // workflow) and Cancel Leave (delete, shown last).
  EmployeeLeave: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    deleteLast: true,
    deleteTip: 'Cancel Leave',
    deleteIcon: /*#__PURE__*/_react["default"].createElement(_icons.CloseCircleOutlined, null),
    deleteConfirm: 'Are you sure you want to cancel this leave? This cannot be undone.',
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null),
    extraActions: [{
      key: 'leavedays',
      tip: 'Leave Details',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      color: '#2e7d32',
      method: 'getLeaveDaysReadonly',
      first: true
    }]
  },
  // --- User leaves (modules::leaves) ------------------------------------
  // My own leave requests (All My Leaves + Pending): Leave Details, and Cancel
  // (delete) shown only while the request is still Pending.
  MyLeave: {
    titleField: 'leave_type',
    hideMeta: ['id'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    // Clicking the card opens the leave details dialog.
    cardClickAction: 'getLeaveDaysReadonly',
    extraActions: [{
      key: 'leavedays',
      tip: 'Leave Details',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      color: '#2e7d32',
      method: 'getLeaveDaysReadonly',
      first: true
    }, {
      key: 'cancel',
      tip: 'Cancel Leave',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CloseCircleOutlined, null),
      color: '#d32f2f',
      method: 'cancelMyLeave',
      show: function show(m, rec) {
        return rec && rec.status === 'Pending';
      }
    }]
  },
  // My approved leaves: Leave Details + request cancellation (cancelLeave).
  MyLeaveApproved: {
    titleField: 'leave_type',
    hideMeta: ['id'],
    hideEditButton: true,
    disableView: true,
    extraActions: [{
      key: 'leavedays',
      tip: 'Leave Details',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      color: '#2e7d32',
      method: 'getLeaveDaysReadonly',
      first: true
    }, {
      key: 'cancel',
      tip: 'Request Cancellation',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CloseCircleOutlined, null),
      color: '#d32f2f',
      method: 'cancelLeave'
    }]
  },
  // Direct reports' leaves (approve/reject): Leave Details + Change Status (cog,
  // driven by the adapter's getStatusOptionsData approve workflow).
  SubLeave: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null),
    statusAction: 'changeLeaveStatus',
    extraActions: [{
      key: 'leavedays',
      tip: 'Leave Details',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      color: '#2e7d32',
      method: 'getLeaveDaysReadonly',
      first: true
    }]
  },
  // Multi-level approval queue (same shape/actions as direct reports).
  LeaveApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null),
    statusAction: 'changeLeaveStatus',
    extraActions: [{
      key: 'leavedays',
      tip: 'Leave Details',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.InfoCircleOutlined, null),
      color: '#2e7d32',
      method: 'getLeaveDaysReadonly',
      first: true
    }]
  },
  // Employee Expenses (extension::expenses|admin) — approve workflow on the
  // expense record; show the employee photo + name.
  EmployeeExpense: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    // Bespoke expense dialog (ExpenseDialog) with a status-history sidebar,
    // instead of the generic legacy view/edit modals. Admins/managers view here;
    // admins may also edit (owner/admin only, enforced by the backend).
    expenseDialog: true,
    // Admins/managers can change the status from within the view dialog.
    dialogStatusChange: true,
    // A reason is mandatory when changing an expense's status.
    requireStatusReason: true,
    // Managers cannot change the status of a Paid expense (admins still can);
    // backend enforces this in Expenses\Admin\Controller::changeStatus.
    managerLockedStatuses: ['Paid'],
    // Managers cannot set an expense TO Paid (admins still can); backend enforces
    // this in Expenses\Admin\Controller::changeStatus.
    managerBlockedTargetStatuses: ['Paid'],
    deleteLast: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null)
  },
  // --- User expenses (extension::expenses|user) -------------------------
  // My own expenses: edit/delete, and request cancellation (for approved).
  MyExpense: {
    titleField: 'category',
    hideMeta: ['id'],
    // Bespoke expense dialog (ExpenseDialog) with a status-history sidebar for
    // view / edit / re-submit — no legacy view/edit modal.
    expenseDialog: true,
    // Quick "filter by status" dropdown in the toolbar.
    statusFilter: ['Pending', 'Approved', 'Rejected', 'Paid'],
    // Employees may delete their own expense while it is Pending or Rejected
    // (the backend enforces this via EmployeeExpense::getUserOnlyMeAccess).
    deleteWhen: function deleteWhen(rec) {
      return rec.status === 'Pending' || rec.status === 'Rejected';
    },
    // Employees may only edit their own expenses while still Pending.
    editWhen: function editWhen(rec) {
      return rec.status === 'Pending';
    },
    // A rejected expense can be re-submitted (status -> Pending) by its owner.
    resubmitAction: 'resubmit',
    resubmitWhen: function resubmitWhen(rec) {
      return rec.status === 'Rejected';
    },
    // Expenses do not support employee-initiated cancellation of approved rows.
    hideCancelButton: true
  },
  // Direct reports' expenses (and the multi-level approval queue): approve
  // workflow with the employee photo + name.
  SubExpense: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    deleteLast: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null)
  },
  ExpenseApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    deleteLast: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null)
  },
  // Candidates (extension::candidates|admin) — the eye opens the rich
  // CandidateProfile via getTableChildComponents (same mechanism as the
  // employees module). All hiring-stage tabs share this config.
  Candidate: {
    title: function title(r) {
      return "".concat(r.first_name || '', " ").concat(r.last_name || '').trim() || r.email;
    },
    hideMeta: ['first_name', 'last_name', 'id'],
    tagFields: ['hiringStage']
  },
  // Job positions (extension::jobpositions|admin) — the rest of the card config
  // comes inline from the extension's meta.json native block; extraActions hold
  // React elements so they live here. Copy Job Link mirrors the legacy Job Link
  // action (public apply?ref=<code> URL).
  Job: {
    extraActions: [{
      key: 'joblink',
      tip: 'Copy Job Link',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.LinkOutlined, null),
      color: '#ed6c02',
      method: 'copyJobLink',
      first: true
    }, {
      key: 'openjob',
      tip: 'Open Job Page',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.MonitorOutlined, null),
      color: '#1565c0',
      method: 'openJobPage',
      first: true
    }]
  },
  // System (admin::settings) — settings rows are edit-only.
  Setting: {
    titleField: 'name',
    hideMeta: ['id', 'meta', 'category', 'setting_order'],
    hideCopyButton: true,
    disableView: true
  },
  Module: {
    titleField: 'label',
    tagFields: ['status'],
    hideMeta: ['id', 'update_path', 'mod_order'],
    hideCopyButton: true,
    disableView: true
  },
  Permission: {
    title: function title(r) {
      return "".concat(r.user_level || '', " \u2014 ").concat(r.permission || '');
    },
    hideMeta: ['id', 'user_level', 'permission'],
    hideCopyButton: true,
    disableView: true
  },
  // Metadata (admin::metadata) — simple lookups.
  Country: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  Province: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  CurrencyType: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  Nationality: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  Ethnicity: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  ImmigrationStatus: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  // Documents (admin::documents)
  CompanyDocument: {
    titleField: 'name',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true
  },
  Document: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  EmployeeDocument: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [{
      key: 'download',
      tip: 'Download Document',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#2e7d32',
      method: 'downloadAttachment',
      first: true,
      show: function show(m, rec) {
        return !!(rec && rec.attachment);
      }
    }]
  },
  PayslipDocument: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [{
      key: 'download',
      tip: 'Download Document',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#2e7d32',
      method: 'downloadAttachment',
      first: true,
      show: function show(m, rec) {
        return !!(rec && rec.attachment);
      }
    }]
  },
  // Documents (modules::documents) — read-only lists with downloads.
  MyDocument: {
    titleField: 'document',
    tagFields: ['status'],
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [{
      key: 'download',
      tip: 'Download Document',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#2e7d32',
      method: 'downloadAttachment',
      first: true,
      show: function show(m, rec) {
        return !!(rec && rec.attachment);
      }
    }]
  },
  MyCompanyDocument: {
    titleField: 'name',
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    hideEditButton: true,
    disableView: true,
    extraActions: [{
      key: 'download',
      tip: 'Download Document',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#2e7d32',
      method: 'downloadAttachment',
      first: true,
      show: function show(m, rec) {
        return !!(rec && rec.attachment);
      }
    }]
  },
  MyPayslip: {
    titleField: 'document',
    tagFields: ['status'],
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    hideEditButton: true,
    disableView: true,
    extraActions: [{
      key: 'download',
      tip: 'Download Payslip',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
      color: '#2e7d32',
      method: 'downloadAttachment',
      first: true,
      show: function show(m, rec) {
        return !!(rec && rec.attachment);
      }
    }]
  },
  // Training (modules::training)
  TrainingSessionWithCourse: {
    titleField: 'name',
    hideMeta: ['id'],
    hideCopyButton: true,
    hideEditButton: true,
    extraActions: [{
      key: 'signup',
      tip: 'Sign Up',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.LoginOutlined, null),
      color: '#2e7d32',
      method: 'signUp',
      first: true
    }]
  },
  EmployeeTrainingSession: {
    titleField: 'trainingSession',
    tagFields: ['status'],
    hideMeta: ['id', 'courseId'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [{
      key: 'completed',
      tip: 'Mark Completed',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
      color: '#2e7d32',
      method: 'completed',
      first: true,
      show: function show(m, rec) {
        return rec && rec.status === 'Scheduled';
      }
    }]
  },
  SubEmployeeTraining: {
    titleField: 'employee',
    tagFields: ['status'],
    hideMeta: ['id', 'courseId'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [{
      key: 'approve',
      tip: 'Approve Completed Status',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
      color: '#2e7d32',
      method: 'completed',
      first: true,
      show: function show(m, rec) {
        return rec && rec.status === 'Attended';
      }
    }]
  },
  CoordinatedTrainingSession: {
    titleField: 'name',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true
  },
  // Performance (admin::performance / modules::performance). The eye opens the
  // rich review/feedback views via the adapters' viewElement flows.
  PerformanceReview: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    tagColors: REVIEW_STATUS_COLORS,
    hideMeta: ['id', 'image', 'review_pdf'],
    hideCopyButton: true,
    extraActions: [{
      key: 'createPdf',
      tip: 'Create PDF',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.FilePdfOutlined, null),
      color: '#c62828',
      method: 'createReviewPdf',
      // Anyone with access can generate the first PDF; once one exists only an
      // admin may regenerate it (the backend enforces this too).
      show: function show(m, rec) {
        return rec && rec.status === 'Completed' && (isEmpty(rec.review_pdf) || m.isAdminUser && m.isAdminUser());
      }
    }, {
      key: 'viewPdf',
      tip: 'View PDF',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.FilePdfOutlined, null),
      color: '#2e7d32',
      method: 'viewReviewPdf',
      show: function show(m, rec) {
        return rec && !isEmpty(rec.review_pdf);
      }
    }]
  },
  ReviewFeedback: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image'],
    hideCopyButton: true
  },
  ReviewTemplate: {
    titleField: 'name',
    hideMeta: ['id'],
    hideCopyButton: true
  },
  EmployeeGoal: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    disableView: true,
    progressFields: ['manager_rating', 'employee_rating']
  },
  // User performance (modules::performance)
  MyPerformanceReview: {
    // 'form' arrives as the review template name (resolved server-side via the
    // adapter's source mapping); the period dates render formatted through the
    // adapter's column render functions in the meta line.
    title: function title(r) {
      return r.form || 'Performance Review';
    },
    tagFields: ['status'],
    tagColors: REVIEW_STATUS_COLORS,
    hideMeta: ['id', 'form', 'review_pdf'],
    hideCopyButton: true,
    hideEditButton: true,
    // Employees can view (not create) the review PDF once a manager generates
    // it — the PDF only exists on Completed reviews.
    extraActions: [{
      key: 'viewPdf',
      tip: 'View PDF',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.FilePdfOutlined, null),
      color: '#2e7d32',
      method: 'viewReviewPdf',
      show: function show(m, rec) {
        return rec && !isEmpty(rec.review_pdf);
      }
    }]
  },
  MyReviewFeedback: {
    titleField: 'review',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    hideEditButton: true,
    extraActions: [{
      key: 'give',
      tip: 'Give Feedback',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.FormOutlined, null),
      color: '#2e7d32',
      method: 'showConfigView',
      first: true,
      show: function show(m, rec) {
        return rec && rec.status !== 'Submitted';
      }
    }, {
      key: 'submit',
      tip: 'Submit Feedback',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ExportOutlined, null),
      color: '#ed6c02',
      method: 'submitFeedback',
      first: true,
      show: function show(m, rec) {
        return rec && rec.status === 'Pending';
      }
    }]
  },
  // Salary (admin::salary)
  SalaryComponentType: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary'
  },
  SalaryComponent: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary'
  },
  EmployeeSalary: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary'
  },
  // Loans (admin::loans)
  CompanyLoan: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  EmployeeCompanyLoan: {
    titleField: 'employee',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true
  },
  // My loans (modules::loans) — read-only; the eye opens the record details.
  MyLoan: {
    titleField: 'loan',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideEditButton: true,
    hideCopyButton: true
  },
  // Travel (admin::travel)
  TravelProject: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true
  },
  // Travel Requests — the View button opens a rich custom modal
  // (viewElement -> TravelRequestView) with status change; keep it enabled.
  EmployeeTravelRecord: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true
  },
  // --- User travel (modules::travel) ------------------------------------
  // My own travel requests: apply/edit/delete + request cancellation.
  MyTravel: {
    titleField: 'travel_to',
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true
  },
  // Direct reports' (and the multi-level approval queue) travel requests:
  // the View modal handles the status change.
  SubTravel: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true
  },
  TravelApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    hideEditButton: true,
    statusIcon: /*#__PURE__*/_react["default"].createElement(_icons.SettingOutlined, null)
  },
  // Users module (admin::users).
  User: {
    titleField: 'username',
    avatarField: 'image',
    hideMeta: ['image', 'id'],
    extraActions: [{
      key: 'password',
      tip: 'Change Password',
      icon: /*#__PURE__*/_react["default"].createElement(_icons.KeyOutlined, null),
      color: '#1565c0',
      method: 'showPasswordChangeForm',
      show: function show(m) {
        return !!(m.hasAccess && m.hasAccess('save'));
      }
    }]
  },
  UserRole: {
    hideMeta: ['id']
  },
  UserInvitation: {
    titleField: 'username',
    tagFields: ['invitation_status_text'],
    hideMeta: ['id']
  }
}; // Render a column cell for a record, tolerating legacy render() functions.

function cellValue(col, record) {
  var raw = record[col.dataIndex];

  if (typeof col.render === 'function') {
    try {
      var out = col.render(raw, record);
      if (out === null || out === undefined || out === '') return raw;
      return out;
    } catch (e) {
      return raw;
    }
  }

  return raw;
}

function isEmpty(v) {
  return v === null || v === undefined || v === '' || v === '-';
}
/**
 * Generic horizontal card list for natively-mounted legacy admin modules.
 * Drives off the module's legacy adapter (window.modJsList[tabKey]): it fetches
 * pages through the adapter's IceDataPipe, renders each row as a compact card
 * built from getTableColumns(), and exposes the adapter's actions (edit / view /
 * delete / copy / document) as buttons — gated by the same flags the legacy
 * table uses. Add/Edit/View/Filter modals render through the adapter (themed via
 * ReactModalAdapterBase.shellThemeWrap). Delete uses an antd confirm + the
 * adapter's cleanDelete (the legacy Bootstrap confirm modal is absent here).
 * Matches the Company Structure card design and is dark/light-mode aware.
 */


function NativeCardList(_ref) {
  var shellConfig = _ref.shellConfig,
      tabKey = _ref.tabKey,
      entity = _ref.entity,
      cardConfig = _ref.cardConfig;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token; // Action-icon colours: the hues are tuned for light mode; in dark mode lighten
  // them so they stay legible on the dark cards.


  var isDark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';

  var ac = function ac(hex) {
    return isDark ? lighten(hex, 0.4) : hex;
  };

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];

  var _useState3 = (0, _react.useState)(0),
      _useState4 = _slicedToArray(_useState3, 2),
      total = _useState4[0],
      setTotal = _useState4[1];

  var _useState5 = (0, _react.useState)(1),
      _useState6 = _slicedToArray(_useState5, 2),
      page = _useState6[0],
      setPage = _useState6[1]; // Page size is fixed at PAGE_SIZE unless the module opts into the size
  // selector via adapter.setShowPageSizeChanger(true) (e.g. admin Attendance).


  var _useState7 = (0, _react.useState)(PAGE_SIZE),
      _useState8 = _slicedToArray(_useState7, 2),
      pageSize = _useState8[0],
      setPageSize = _useState8[1];

  var _useState9 = (0, _react.useState)(''),
      _useState10 = _slicedToArray(_useState9, 2),
      search = _useState10[0],
      setSearch = _useState10[1];

  var _useState11 = (0, _react.useState)(''),
      _useState12 = _slicedToArray(_useState11, 2),
      statusFilterVal = _useState12[0],
      setStatusFilterVal = _useState12[1];

  var _useState13 = (0, _react.useState)(false),
      _useState14 = _slicedToArray(_useState13, 2),
      err = _useState14[0],
      setErr = _useState14[1]; // Some adapters (Employee) "view" by setting a current element that the legacy
  // table renders as a child component (e.g. the employee profile) instead of a
  // modal — capture it and render that child in our own modal.


  var _useState15 = (0, _react.useState)(null),
      _useState16 = _slicedToArray(_useState15, 2),
      currentElement = _useState16[0],
      _setCurrentElement = _useState16[1]; // A document URL (e.g. a task-list editor page) opened in an embedded modal.


  var _useState17 = (0, _react.useState)(null),
      _useState18 = _slicedToArray(_useState17, 2),
      editorUrl = _useState18[0],
      setEditorUrl = _useState18[1]; // A document opened as a NATIVE in-shell component (no iframe) — the URL drives
  // an extension's mount fn (e.g. the editor) into nativeElRef below.


  var _useState19 = (0, _react.useState)(null),
      _useState20 = _slicedToArray(_useState19, 2),
      nativeDoc = _useState20[0],
      setNativeDoc = _useState20[1];

  var nativeElRef = (0, _react.useRef)(null); // Approve-workflow state: the record whose status is being changed, and the
  // approval-log list being viewed (both null when closed).

  var _useState21 = (0, _react.useState)(null),
      _useState22 = _slicedToArray(_useState21, 2),
      statusRec = _useState22[0],
      setStatusRec = _useState22[1];

  var _useState23 = (0, _react.useState)(null),
      _useState24 = _slicedToArray(_useState23, 2),
      statusValue = _useState24[0],
      setStatusValue = _useState24[1];

  var _useState25 = (0, _react.useState)(''),
      _useState26 = _slicedToArray(_useState25, 2),
      statusReason = _useState26[0],
      setStatusReason = _useState26[1];

  var _useState27 = (0, _react.useState)(false),
      _useState28 = _slicedToArray(_useState27, 2),
      statusSaving = _useState28[0],
      setStatusSaving = _useState28[1];

  var _useState29 = (0, _react.useState)(null),
      _useState30 = _slicedToArray(_useState29, 2),
      logsState = _useState30[0],
      setLogsState = _useState30[1]; // { loading, rows } | null


  var _useState31 = (0, _react.useState)(null),
      _useState32 = _slicedToArray(_useState31, 2),
      expenseDialog = _useState32[0],
      setExpenseDialog = _useState32[1]; // { rec, mode } | null


  var _useState33 = (0, _react.useState)(0),
      _useState34 = _slicedToArray(_useState33, 2),
      force = _useState34[1];

  var bump = function bump() {
    return force(function (n) {
      return n + 1;
    });
  }; // Bulk-delete selection (enabled per entity via cfg.bulkDelete).


  var _useState35 = (0, _react.useState)([]),
      _useState36 = _slicedToArray(_useState35, 2),
      selectedIds = _useState36[0],
      setSelectedIds = _useState36[1];

  var _useState37 = (0, _react.useState)(false),
      _useState38 = _slicedToArray(_useState37, 2),
      bulkDeleting = _useState38[0],
      setBulkDeleting = _useState38[1];

  var clearSelection = function clearSelection() {
    return setSelectedIds([]);
  }; // The module's adapter instances are stable for the life of the mounted module,
  // but window.modJsList is a SHARED global that another overlay can temporarily
  // borrow — e.g. the native document editor's initEditorUser replaces it while
  // its modal is open. So capture this tab's adapter instance once it's wired and
  // prefer the captured instance for render/load; only fall back to the live
  // global during initial discovery. This keeps the list rendering correctly even
  // while an editor modal has borrowed the globals.


  var boundRef = (0, _react.useRef)(null);

  var adapter = function adapter() {
    return boundRef.current || (window.modJsList || {})[tabKey];
  };

  var load = (0, _react.useCallback)(function () {
    var toPage = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
    var toSearch = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
    var toLimit = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    var m = adapter();

    if (!m || !m.dataPipe) {
      setErr(true);
      return;
    }

    var p = toPage != null ? toPage : page;
    var s = toSearch != null ? toSearch : search;
    var lim = toLimit != null ? toLimit : pageSize;
    setErr(false);
    m.dataPipe.get({
      page: p,
      limit: lim,
      search: s
    }).then(function (d) {
      setItems(d && d.items || []);
      setTotal(d && d.total || 0);
      bump();
    })["catch"](function () {
      return setErr(true);
    });
  }, [tabKey, page, search, pageSize]); // eslint-disable-line react-hooks/exhaustive-deps
  // Wire the adapter to the shell: mount containers for its modals, a fake table
  // container so reload()/loading hooks resolve to us, master data for selects.
  // The module's init() (which creates the adapters) runs asynchronously after
  // the bundles load, so the adapter may not exist when this first mounts — poll
  // briefly until it is ready instead of erroring out.

  (0, _react.useEffect)(function () {
    var cancelled = false;
    var tries = 0; // Detached mount points for the adapter's modals (the antd Modal inside
    // portals to <body> regardless; these just need to be real elements that
    // exist independently of this component's conditional render).

    var formEl = document.createElement('div');
    var filterEl = document.createElement('div');
    document.body.appendChild(formEl);
    document.body.appendChild(filterEl);

    var wireUp = function wireUp(m) {
      boundRef.current = m;
      window.modJs = m;

      if (typeof m.setContainers === 'function') {
        m.setContainers({
          Form: formEl,
          FilterForm: filterEl
        });
      } // Force the adapter to (re)render its form/filter modals into the current
      // containers on the next open (the instance persists across remounts).


      m.formInitialized = false;
      m.tableContainer = {
        current: {
          reload: function reload() {
            return load();
          },
          setCurrentElement: function setCurrentElement(el) {
            return _setCurrentElement(el || null);
          },
          setLoading: function setLoading() {},
          setFilterData: function setFilterData() {
            bump();
          }
        }
      };

      try {
        if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
          m.masterDataReader.updateAllMasterData();
        }

        if (typeof m.initFieldMasterData === 'function') m.initFieldMasterData();
      } catch (e) {
        /* ignore */
      }

      setPage(1);
      load(1, '');
    };

    var tick = function tick() {
      if (cancelled) return;
      var m = adapter();

      if (m && m.dataPipe) {
        wireUp(m);
        return;
      }

      tries += 1;

      if (tries > 100) {
        setErr(true);
        return;
      } // ~10s


      setTimeout(tick, 100);
    };

    tick();
    return function () {
      cancelled = true;

      try {
        var _m = adapter();

        if (_m && typeof _m.setContainers === 'function') _m.setContainers(null);
        if (formEl.parentNode) formEl.parentNode.removeChild(formEl);
        if (filterEl.parentNode) filterEl.parentNode.removeChild(filterEl);
      } catch (e) {
        /* ignore */
      }

      boundRef.current = null;
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tabKey]);
  var m = adapter(); // The "group=name" reference for customAction routing (the legacy `mod`
  // param). Prefer the adapter's own ref (approve/log adapters know whether
  // they hit admin= or modules=); otherwise derive from the SPA module scope:
  // core admin -> admin=<name>, core user -> modules=<name>, extensions
  // (registry name "<ext>|<subType>") -> admin=<ext> / modules=<ext>.

  var moduleRef = function moduleRef() {
    if (!m) return 'admin=';
    if (typeof m.getActionModuleRef === 'function') return m.getActionModuleRef();

    if (m.spaModuleGroup === 'extension' && typeof m.spaModuleName === 'string' && m.spaModuleName.indexOf('|') >= 0) {
      var _m$spaModuleName$spli = m.spaModuleName.split('|'),
          _m$spaModuleName$spli2 = _slicedToArray(_m$spaModuleName$spli, 2),
          ext = _m$spaModuleName$spli2[0],
          sub = _m$spaModuleName$spli2[1];

      return "".concat(sub === 'user' ? 'modules' : 'admin', "=").concat(m.modulePathName || ext);
    }

    if (m.spaModuleGroup && m.spaModuleName) return "".concat(m.spaModuleGroup, "=").concat(m.spaModuleName);
    return "admin=".concat(m.modulePathName);
  };

  var columns = (0, _react.useMemo)(function () {
    if (!m || typeof m.getTableColumns !== 'function') return [];
    return (m.getTableColumns() || []).map(function (c) {
      return _objectSpread({}, c, {
        label: m.gt ? m.gt(c.title) : c.title
      });
    });
  }, [m, items]); // eslint-disable-line react-hooks/exhaustive-deps
  // Hardcoded per-entity config for core modules, optionally overridden by a
  // declarative config an extension supplies via its meta.json `native` block.

  var cfg = _objectSpread({}, ENTITY_CONFIG[entity] || {}, {}, cardConfig || {});

  var docAction = cfg.documentAction || null; // --- capabilities -------------------------------------------------------

  var can = function can(a) {
    return !!(m && m.hasAccess && m.hasAccess(a));
  }; // cfg.disableView turns off viewing entirely (no eye button AND no row-click
  // view) — e.g. task lists, which are opened via their document action instead.
  // A card-config method invoked when the card body is clicked (e.g. leave
  // groups open a manage-members dialog). Falls back to the default view.


  var cardClick = cfg.cardClickAction && m && typeof m[cfg.cardClickAction] === 'function' ? function (rec) {
    return m[cfg.cardClickAction](rec.id, rec);
  } : null;
  var hasView = !cfg.disableView && (!!cfg.expenseDialog || !!(m && (typeof m.showProjectDetails === 'function' || can('element') && m.showViewButton && m.showViewButton())));
  var showViewBtn = hasView && !cfg.hideViewButton;
  var canEdit = !!(m && can('save') && m.showEdit !== false) && !cfg.hideEditButton;
  var canCopy = !!(m && can('save') && m.showAddNew !== false) && !cfg.hideCopyButton;
  var canDelete = !!(m && can('delete') && m.showDelete !== false); // Per-record delete gate: some entities only allow deleting rows in a certain
  // state (e.g. employees may delete only their own Pending expenses). A card
  // config `deleteWhen(rec) => bool` refines the list-level canDelete row by row;
  // the backend still enforces the same rule per record.

  var rowDeletable = function rowDeletable(rec) {
    return canDelete && (typeof cfg.deleteWhen === 'function' ? !!cfg.deleteWhen(rec) : true);
  }; // Same per-record refinement for editing (e.g. employees may edit only their
  // own Pending expenses).


  var rowEditable = function rowEditable(rec) {
    return typeof cfg.editWhen === 'function' ? !!cfg.editWhen(rec) : true;
  };

  var bulkEnabled = !!cfg.bulkDelete && canDelete && typeof (m && m.cleanDelete) === 'function';
  var canAdd = !!(m && can('save') && (m.getShowAddNew ? m.getShowAddNew() : m.showAddNew !== false));
  var hasFilters = !!(m && m.getFilters && m.getFilters());

  var doView = function doView(id) {
    if (!m) return;
    if (typeof m.showProjectDetails === 'function') m.showProjectDetails(id);else if (typeof m.viewElement === 'function') m.viewElement(id);
  }; // Entities with a bespoke dialog (cfg.expenseDialog) open that instead of the
  // legacy generic view/edit modals, in the appropriate mode.


  var openView = function openView(rec) {
    if (cfg.expenseDialog) {
      setExpenseDialog({
        rec: rec,
        mode: 'view'
      });
      return;
    }

    doView(rec.id);
  };

  var openEdit = function openEdit(rec) {
    if (cfg.expenseDialog) {
      setExpenseDialog({
        rec: rec,
        mode: 'edit'
      });
      return;
    }

    if (m) m.edit(rec.id);
  };

  var doEdit = function doEdit(id) {
    if (m) m.edit(id);
  };

  var doCopy = function doCopy(id) {
    if (m && m.copyRow) m.copyRow(id);
  };

  var doAdd = function doAdd() {
    if (m) m.renderForm();
  }; // Fetch a backup from the REST API (using the shell's bearer token) and download it as
  // JSON. Driven by cfg.exportEndpoint (see the salary entities in ENTITY_CONFIG).


  var _useState39 = (0, _react.useState)(false),
      _useState40 = _slicedToArray(_useState39, 2),
      exporting = _useState40[0],
      setExporting = _useState40[1];

  var doExport = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var resp, backup, blob, url, a, stamp;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              if (cfg.exportEndpoint) {
                _context.next = 2;
                break;
              }

              return _context.abrupt("return");

            case 2:
              setExporting(true);
              _context.prev = 3;
              _context.next = 6;
              return fetch("".concat(shellConfig.restApiBase).concat(cfg.exportEndpoint), {
                headers: {
                  Authorization: "Bearer ".concat(shellConfig.token)
                },
                credentials: 'same-origin'
              });

            case 6:
              resp = _context.sent;
              _context.next = 9;
              return resp.json();

            case 9:
              backup = _context.sent;
              blob = new Blob([JSON.stringify(backup, null, 2)], {
                type: 'application/json'
              });
              url = URL.createObjectURL(blob);
              a = document.createElement('a');
              stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
              a.href = url;
              a.download = "".concat(cfg.exportFilePrefix || 'backup', "-").concat(stamp, ".json");
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              URL.revokeObjectURL(url);

              _antd.message.success('Backup downloaded');

              _context.next = 26;
              break;

            case 23:
              _context.prev = 23;
              _context.t0 = _context["catch"](3);

              _antd.message.error('Export failed');

            case 26:
              _context.prev = 26;
              setExporting(false);
              return _context.finish(26);

            case 29:
            case "end":
              return _context.stop();
          }
        }
      }, _callee, null, [[3, 23, 26, 29]]);
    }));

    return function doExport() {
      return _ref2.apply(this, arguments);
    };
  }(); // Restore a backup by POSTing an uploaded JSON file to cfg.importEndpoint.


  var _useState41 = (0, _react.useState)(false),
      _useState42 = _slicedToArray(_useState41, 2),
      importing = _useState42[0],
      setImporting = _useState42[1];

  var importInputRef = (0, _react.useRef)(null);

  var doImportFile = /*#__PURE__*/function () {
    var _ref3 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(event) {
      var file, text, backup, resp, data, _data$error, _data$error$, _data$error$$;

      return regeneratorRuntime.wrap(function _callee2$(_context2) {
        while (1) {
          switch (_context2.prev = _context2.next) {
            case 0:
              file = event.target.files && event.target.files[0];
              event.target.value = '';

              if (!(!file || !cfg.importEndpoint)) {
                _context2.next = 4;
                break;
              }

              return _context2.abrupt("return");

            case 4:
              setImporting(true);
              _context2.prev = 5;
              _context2.next = 8;
              return file.text();

            case 8:
              text = _context2.sent;
              backup = JSON.parse(text);
              _context2.next = 12;
              return fetch("".concat(shellConfig.restApiBase).concat(cfg.importEndpoint), {
                method: 'POST',
                headers: {
                  Authorization: "Bearer ".concat(shellConfig.token),
                  'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(backup)
              });

            case 12:
              resp = _context2.sent;
              _context2.next = 15;
              return resp.json();

            case 15:
              data = _context2.sent;

              if (data && data.error) {
                _antd.message.error(((_data$error = data.error) === null || _data$error === void 0 ? void 0 : (_data$error$ = _data$error[0]) === null || _data$error$ === void 0 ? void 0 : (_data$error$$ = _data$error$[0]) === null || _data$error$$ === void 0 ? void 0 : _data$error$$.message) || 'Import failed', 5);
              } else {
                _antd.message.success('Data restored — refreshing…'); // A salary import restores multiple tables (types/components/salaries); reload so
                // every tab reflects it, not just the currently visible card list.


                setTimeout(function () {
                  return window.location.reload();
                }, 700);
              }

              _context2.next = 22;
              break;

            case 19:
              _context2.prev = 19;
              _context2.t0 = _context2["catch"](5);

              _antd.message.error('Import failed: invalid file or server error', 5);

            case 22:
              _context2.prev = 22;
              setImporting(false);
              return _context2.finish(22);

            case 25:
            case "end":
              return _context2.stop();
          }
        }
      }, _callee2, null, [[5, 19, 22, 25]]);
    }));

    return function doImportFile(_x) {
      return _ref3.apply(this, arguments);
    };
  }(); // "Generate from industry" toolbar action (cfg.generateFromIndustry). Reusable:
  // fetches a list of industries from one endpoint and posts the chosen one to a
  // generate endpoint (e.g. Job Titles). Any module can opt in via its card config.


  var genCfg = cfg.generateFromIndustry || null;

  var _useState43 = (0, _react.useState)(false),
      _useState44 = _slicedToArray(_useState43, 2),
      genOpen = _useState44[0],
      setGenOpen = _useState44[1];

  var _useState45 = (0, _react.useState)([]),
      _useState46 = _slicedToArray(_useState45, 2),
      genIndustries = _useState46[0],
      setGenIndustries = _useState46[1];

  var _useState47 = (0, _react.useState)(null),
      _useState48 = _slicedToArray(_useState47, 2),
      genIndustry = _useState48[0],
      setGenIndustry = _useState48[1];

  var _useState49 = (0, _react.useState)(false),
      _useState50 = _slicedToArray(_useState49, 2),
      genLoading = _useState50[0],
      setGenLoading = _useState50[1];

  var _useState51 = (0, _react.useState)(false),
      _useState52 = _slicedToArray(_useState51, 2),
      generating = _useState52[0],
      setGenerating = _useState52[1];

  var _useState53 = (0, _react.useState)(false),
      _useState54 = _slicedToArray(_useState53, 2),
      genDelete = _useState54[0],
      setGenDelete = _useState54[1];

  var openGenerate = function openGenerate() {
    if (!genCfg) return;
    setGenIndustry(null);
    setGenDelete(false);
    setGenOpen(true);
    setGenLoading(true);
    fetch("".concat(shellConfig.restApiBase).concat(genCfg.industriesEndpoint), {
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      setGenIndustries(Array.isArray(d) ? d : []);
    })["catch"](function () {
      return _antd.message.error('Could not load industries', 5);
    })["finally"](function () {
      return setGenLoading(false);
    });
  };

  var doGenerate = function doGenerate() {
    if (!genCfg || !genIndustry) {
      _antd.message.error('Please select an industry.', 4);

      return;
    }

    setGenerating(true);
    fetch("".concat(shellConfig.restApiBase).concat(genCfg.generateEndpoint), {
      method: 'POST',
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token),
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        industry: genIndustry,
        deleteExisting: genDelete
      })
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (d && d.error) {
        var _d$error, _d$error$, _d$error$$;

        _antd.message.error(((_d$error = d.error) === null || _d$error === void 0 ? void 0 : (_d$error$ = _d$error[0]) === null || _d$error$ === void 0 ? void 0 : (_d$error$$ = _d$error$[0]) === null || _d$error$$ === void 0 ? void 0 : _d$error$$.message) || 'Generation failed', 5);

        return;
      }

      var label = genCfg.itemsLabel || 'items';
      var parts = ["Added ".concat(d.created, " ").concat(label)];
      if (d.deleted) parts.push("deleted ".concat(d.deleted));
      if (d.kept_assigned) parts.push("kept ".concat(d.kept_assigned, " in use"));
      if (d.skipped) parts.push("".concat(d.skipped, " already existed"));

      _antd.message.success("".concat(parts.join(', '), "."));

      setGenOpen(false);
      load();
    })["catch"](function () {
      return _antd.message.error('Generation failed', 5);
    })["finally"](function () {
      return setGenerating(false);
    });
  };

  var docUrl = function docUrl(rec) {
    var link = rec.document_link;
    if (!link) return null;
    var base = (window.baseUrl || '').replace('service.php', '');
    return /^https?:\/\//.test(link) ? link : "".concat(base).concat(link);
  };

  var doDocument = function doDocument(rec) {
    var url = docUrl(rec);
    if (!url) return; // Native in-shell mount (no iframe, theme-aware), an embedded iframe modal,
    // or a new tab — per the entity's documentAction config.

    if (docAction && docAction.openIn === 'native') {
      setNativeDoc(url);
      return;
    }

    if (docAction && docAction.openIn === 'iframe-modal') {
      setEditorUrl(url);
      return;
    }

    window.open(url, '_blank', 'noopener');
  };

  var doDelete = function doDelete(rec) {
    if (!m) return; // Use the same title the card shows (respects the entity's titleField /
    // title config) — falling back to a generic message if it isn't a plain
    // string (e.g. a rendered cell), so we never surface a raw URL/field value.

    var t = titleOf(rec);
    var label = typeof t === 'string' && t.trim() ? t : null;

    _antd.Modal.confirm({
      title: cfg.deleteTip || 'Delete',
      content: cfg.deleteConfirm || (label ? "Are you sure you want to delete \u201C".concat(label, "\u201D?") : 'Are you sure you want to delete this item?'),
      okText: cfg.deleteTip || 'Delete',
      okType: 'danger',
      onOk: function onOk() {
        return new Promise(function (resolve) {
          try {
            m.cleanDelete(rec.id, function (httpStatus, status, data) {
              if (httpStatus === 200 && status === 'SUCCESS') {
                _antd.message.success('Deleted');

                load();
              } else {
                // Prefer the server's reason (e.g. "…assigned to N employees").
                var serverMsg = data && typeof data.data === 'string' ? data.data : null;

                _antd.message.error(serverMsg || 'Could not delete. It may be in use.', 5);
              }

              resolve();
            });
          } catch (e) {
            _antd.message.error('Could not delete', 5);

            resolve();
          }
        });
      }
    });
  }; // Delete one id via the adapter, resolved to true/false (never rejects).


  var deleteOne = function deleteOne(id) {
    return new Promise(function (resolve) {
      try {
        m.cleanDelete(id, function (httpStatus, status) {
          return resolve(httpStatus === 200 && status === 'SUCCESS');
        });
      } catch (e) {
        resolve(false);
      }
    });
  };

  var doBulkDelete = function doBulkDelete() {
    if (!m || !selectedIds.length) return;

    var ids = _toConsumableArray(selectedIds);

    _antd.Modal.confirm({
      title: 'Delete selected',
      content: "Are you sure you want to delete ".concat(ids.length, " item").concat(ids.length === 1 ? '' : 's', "? This cannot be undone."),
      okText: "Delete ".concat(ids.length),
      okType: 'danger',
      onOk: function () {
        var _onOk = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
          var ok, _iterator, _step, id, failed;

          return regeneratorRuntime.wrap(function _callee3$(_context3) {
            while (1) {
              switch (_context3.prev = _context3.next) {
                case 0:
                  setBulkDeleting(true);
                  ok = 0; // Sequential to avoid hammering the endpoint; tolerant of individual failures.
                  // eslint-disable-next-line no-restricted-syntax

                  _iterator = _createForOfIteratorHelper(ids);
                  _context3.prev = 3;

                  _iterator.s();

                case 5:
                  if ((_step = _iterator.n()).done) {
                    _context3.next = 13;
                    break;
                  }

                  id = _step.value;
                  _context3.next = 9;
                  return deleteOne(id);

                case 9:
                  if (!_context3.sent) {
                    _context3.next = 11;
                    break;
                  }

                  ok += 1;

                case 11:
                  _context3.next = 5;
                  break;

                case 13:
                  _context3.next = 18;
                  break;

                case 15:
                  _context3.prev = 15;
                  _context3.t0 = _context3["catch"](3);

                  _iterator.e(_context3.t0);

                case 18:
                  _context3.prev = 18;

                  _iterator.f();

                  return _context3.finish(18);

                case 21:
                  setBulkDeleting(false);
                  failed = ids.length - ok;
                  if (ok) _antd.message.success("Deleted ".concat(ok, " item").concat(ok === 1 ? '' : 's'));
                  if (failed) _antd.message.error("".concat(failed, " could not be deleted (may be in use)"), 5);
                  clearSelection();
                  load();

                case 27:
                case "end":
                  return _context3.stop();
              }
            }
          }, _callee3, null, [[3, 15, 18, 21]]);
        }));

        function onOk() {
          return _onOk.apply(this, arguments);
        }

        return onOk;
      }()
    });
  }; // --- approve workflow (overtime/expenses/leave/…) -----------------------
  // Detected from the adapter, driven natively (the legacy bootstrap status modal
  // + log popup don't exist in the SPA). changeStatus/getLogs/cancelRequest run
  // through the adapter's customAction with injected native callbacks.


  var isApprove = !!(m && typeof m.getStatusOptionsData === 'function');
  var canCancel = !!(m && typeof m.cancelRequest === 'function');
  var hasLogs = !!(m && typeof m.getLogs === 'function'); // Target-status options available FROM a given current status, applying the
  // non-admin restrictions (a manager cannot change a Paid expense, nor set one
  // to Paid). Used by both the card status button and the dialog dropdown.

  var statusOptionsForStatus = function statusOptionsForStatus(status) {
    if (!isApprove) return [];
    var lvl = m && typeof m.getUser === 'function' && m.getUser() ? m.getUser().user_level : null;
    var isNonAdmin = !!lvl && lvl !== 'Admin';

    if (isNonAdmin && cfg.managerLockedStatuses && cfg.managerLockedStatuses.indexOf(status) >= 0) {
      return [];
    }

    var data = {};

    try {
      data = m.getStatusOptionsData(status) || {};
    } catch (e) {
      data = {};
    }

    var opts = Object.keys(data).map(function (k) {
      return {
        value: data[k],
        label: k
      };
    });

    if (isNonAdmin && cfg.managerBlockedTargetStatuses) {
      opts = opts.filter(function (o) {
        return cfg.managerBlockedTargetStatuses.indexOf(o.value) < 0;
      });
    }

    return opts;
  };

  var statusOptionsFor = function statusOptionsFor(rec) {
    return statusOptionsForStatus(rec.status);
  };

  var openStatus = function openStatus(rec) {
    var opts = statusOptionsFor(rec);
    setStatusRec(rec);
    setStatusValue(opts.length ? opts[0].value : null);
    setStatusReason('');
  };

  var submitStatus = function submitStatus() {
    if (!m || !statusRec || !statusValue) return;
    setStatusSaving(true);
    var cb = {
      callBackData: [],
      callBackSuccess: '__nativeStatusOk',
      callBackFail: '__nativeStatusFail'
    };

    m.__nativeStatusOk = function () {
      setStatusSaving(false);
      setStatusRec(null);

      _antd.message.success('Status updated');

      load();
    };

    m.__nativeStatusFail = function (d) {
      setStatusSaving(false);

      _antd.message.error(typeof d === 'string' ? d : 'Could not update status', 5);
    };

    var payload = JSON.stringify({
      id: statusRec.id,
      status: statusValue,
      reason: statusReason
    });

    try {
      // Some modules use a differently-named status action (e.g. leaves ->
      // changeLeaveStatus); the card config can override the default.
      m.customAction(cfg.statusAction || 'changeStatus', moduleRef(), payload, cb, true);
    } catch (e) {
      m.__nativeStatusFail('Could not update status');
    }
  }; // Re-submit a rejected record (e.g. an employee resending a rejected expense
  // back into the approval queue -> status becomes Pending). Driven by a custom
  // action named in cfg.resubmitAction; the backend re-checks ownership/status.


  var doResubmit = function doResubmit(rec) {
    if (!m || !cfg.resubmitAction) return; // With the expense dialog, re-submit opens the editable dialog (so the owner
    // can fix the expense before resending); the dialog itself does the resubmit.

    if (cfg.expenseDialog) {
      setExpenseDialog({
        rec: rec,
        mode: 'resubmit'
      });
      return;
    }

    _antd.Modal.confirm({
      title: 'Re-submit expense',
      content: 'Re-submit this rejected expense for approval? Its status will be set back to Pending.',
      okText: 'Re-submit',
      onOk: function onOk() {
        var cb = {
          callBackData: [],
          callBackSuccess: '__nativeResubmitOk',
          callBackFail: '__nativeResubmitFail'
        };

        m.__nativeResubmitOk = function () {
          _antd.message.success('Expense re-submitted');

          load();
        };

        m.__nativeResubmitFail = function (d) {
          _antd.message.error(typeof d === 'string' ? d : 'Could not re-submit', 5);
        };

        try {
          m.customAction(cfg.resubmitAction, moduleRef(), JSON.stringify({
            id: rec.id
          }), cb, true);
        } catch (e) {
          m.__nativeResubmitFail('Could not re-submit');
        }
      }
    });
  };

  var doCancel = function doCancel(rec) {
    if (!m || !m.cancelRequest) return;

    _antd.Modal.confirm({
      title: 'Cancel request',
      content: 'Request cancellation of this approved entry?',
      okText: 'Yes, cancel it',
      onOk: function onOk() {
        m.cancelSuccessCallBack = function () {
          _antd.message.success('Cancellation requested');

          load();
        };

        m.cancelFailCallBack = function (d) {
          return _antd.message.error(typeof d === 'string' ? d : 'Could not cancel', 5);
        };

        try {
          m.cancelRequest(rec.id);
        } catch (e) {
          _antd.message.error('Could not cancel', 5);
        }
      }
    });
  };

  var openLogs = function openLogs(rec) {
    if (!m || !m.getLogs) return;
    setLogsState({
      loading: true,
      rows: []
    });
    var cb = {
      callBackData: [],
      callBackSuccess: '__nativeLogsOk',
      callBackFail: '__nativeLogsFail'
    };

    m.__nativeLogsOk = function (d) {
      // Tolerate either an array or { data: [...] }.
      var rows = Array.isArray(d) ? d : d && Array.isArray(d.data) ? d.data : [];
      setLogsState({
        loading: false,
        rows: rows
      });
    };

    m.__nativeLogsFail = function () {
      return setLogsState({
        loading: false,
        rows: []
      });
    };

    try {
      m.customAction('getLogs', moduleRef(), JSON.stringify({
        id: rec.id
      }), cb);
    } catch (e) {
      setLogsState({
        loading: false,
        rows: []
      });
    }
  }; // --- filters ------------------------------------------------------------
  // getFilterString resolves ids via the adapter's field master data, which may
  // not be loaded yet (e.g. a filter handed over before this tab first opened) —
  // never let a display-string lookup crash the whole card list render.


  var filterString = '';

  try {
    filterString = m && hasFilters && m.filter && m.getFilterString ? m.getFilterString(m.filter) : '';
  } catch (e) {
    filterString = '';
  }

  var openFilters = function openFilters() {
    if (m && m.showFilters) m.showFilters();
  };

  var clearFilters = function clearFilters() {
    if (m && m.resetFilters) m.resetFilters();
  };

  var onSearch = function onSearch(val) {
    clearSelection();
    setSearch(val);
    setPage(1);
    load(1, val);
  }; // Quick status filter (e.g. expenses). Applies the value as the adapter's
  // server-side filter ({status}) and reloads; clearing restores the tab's
  // original filter so the list shows every status again.


  var onStatusFilter = function onStatusFilter(val) {
    if (!m) return;
    m.setFilter(val ? {
      status: val
    } : m.origFilter != null ? m.origFilter : '');
    setStatusFilterVal(val || '');
    clearSelection();
    setPage(1);
    load(1);
  };

  var onPage = function onPage(p, ps) {
    clearSelection(); // antd fires onChange for both page and page-size changes. When the size
    // changes, jump back to page 1 and refetch with the new limit.

    if (ps && ps !== pageSize) {
      setPageSize(ps);
      setPage(1);
      load(1, null, ps);
    } else {
      setPage(p);
      load(p, null);
    }
  }; // Selection helpers (bulk delete).


  var isSelected = function isSelected(id) {
    return selectedIds.includes(id);
  };

  var toggleSelect = function toggleSelect(id) {
    return setSelectedIds(function (prev) {
      return prev.includes(id) ? prev.filter(function (x) {
        return x !== id;
      }) : [].concat(_toConsumableArray(prev), [id]);
    });
  };

  var pageIds = (items || []).map(function (r) {
    return r.id;
  });
  var allOnPageSelected = pageIds.length > 0 && pageIds.every(function (id) {
    return selectedIds.includes(id);
  });

  var toggleSelectAll = function toggleSelectAll() {
    return setSelectedIds(function (prev) {
      return allOnPageSelected ? prev.filter(function (id) {
        return !pageIds.includes(id);
      }) : Array.from(new Set([].concat(_toConsumableArray(prev), _toConsumableArray(pageIds))));
    });
  };

  var st = entityStyle(entity); // Title / meta / tag / icon resolution from the per-entity config.
  // When no titleField is configured, prefer the conventional IceHRM display
  // columns (`name` / `title`) before falling back to the first column — the
  // first column may be an avatar/image (e.g. Task Lists), which must never be
  // used as the title or in the delete confirmation.

  var hasCol = function hasCol(k) {
    return columns.some(function (c) {
      return c.dataIndex === k;
    });
  };

  var conventionalTitle = ['name', 'title'].find(hasCol);
  var titleFieldName = cfg.titleField || conventionalTitle || columns[0] && columns[0].dataIndex;
  var hideSet = new Set(cfg.hideMeta || []); // Don't repeat the title field down in the meta line (unless a custom title()
  // function is used, in which case the underlying columns are still meta).

  if (!cfg.title && titleFieldName) hideSet.add(titleFieldName);
  (cfg.tagFields || []).forEach(function (f) {
    return hideSet.add(f);
  });
  var metaColumns = columns.filter(function (c) {
    return !hideSet.has(c.dataIndex);
  });
  var tagColumns = (cfg.tagFields || []).map(function (f) {
    return columns.find(function (c) {
      return c.dataIndex === f;
    });
  }).filter(Boolean); // Resolve a column to its displayed (name-resolved) value for a record, so a
  // custom title() can show a foreign key's name (e.g. leave_type -> "Annual
  // leave") instead of its raw id, exactly like the meta line does.

  var resolveField = function resolveField(rec, field) {
    var col = columns.find(function (c) {
      return c.dataIndex === field;
    });
    return col ? cellValue(col, rec) : rec[field];
  };

  var titleOf = function titleOf(rec) {
    var v;

    if (cfg.title) {
      v = cfg.title(rec, function (field) {
        return resolveField(rec, field);
      });
    } else if (titleFieldName) {
      var col = columns.find(function (c) {
        return c.dataIndex === titleFieldName;
      }) || {
        dataIndex: titleFieldName
      };
      v = cellValue(col, rec);
    } else {
      return "#".concat(rec.id);
    } // Custom view elements may carry a nested record here (e.g. the performance
    // review view passes an employee OBJECT) — rendering that as a React child
    // crashes (#31). Reduce it to a display name.


    if (v && _typeof(v) === 'object' && !_react["default"].isValidElement(v)) {
      var name = "".concat(v.first_name || '', " ").concat(v.last_name || '').trim();
      return name || v.name || "#".concat(rec.id);
    }

    return v;
  };

  var iconOf = function iconOf(rec) {
    return cfg.iconField ? objectTypeStyle(rec[cfg.iconField]) : st;
  }; // Native document mount: when a row's documentAction is openIn:'native', load
  // the extension's bundle (declared in the card config) and call its mount fn
  // into the modal container, passing the shell session + colour mode.
  //
  // The mounted bundle (e.g. the editor's initEditorUser) overwrites the shared
  // window.modJs/modJsList globals that THIS card list also drives off. The host
  // owns the snapshot + restore here — taken synchronously before the bundle can
  // clobber it, restored synchronously after unmount — so the list always comes
  // back. We restore here rather than in the editor because the editor re-mounts
  // on in-document navigation and can't know the original host value.


  (0, _react.useEffect)(function () {
    if (!nativeDoc || !docAction || docAction.openIn !== 'native') return undefined;
    var el = nativeElRef.current;
    if (!el) return undefined; // Snapshot the host's module globals BEFORE the bundle's init clobbers them.

    var savedModJsList = window.modJsList;
    var savedModJs = window.modJs;
    var cancelled = false;
    var mountName = docAction.mountFn;
    var unmountName = mountName ? mountName.replace('mount', 'unmount') : null;
    var extBase = (window.BASE_URL || '').replace('/web/', '/extensions/');
    var bundleUrl = docAction.bundle ? "".concat(extBase).concat(docAction.bundle) : null;

    var run = function run() {
      var fn = mountName && window[mountName];

      if (typeof fn === 'function') {
        fn(el, {
          documentUrl: nativeDoc,
          restApiBase: shellConfig.restApiBase,
          token: shellConfig.token,
          colorMode: window.__shellColorMode || 'light',
          onClose: function onClose() {
            return setNativeDoc(null);
          }
        });
      }
    };

    (bundleUrl ? loadNativeBundle(bundleUrl) : Promise.resolve()).then(function () {
      if (!cancelled) run();
    })["catch"](function () {
      /* ignore */
    });
    return function () {
      cancelled = true;
      var ufn = unmountName && window[unmountName]; // Unmount the editor's React root synchronously (runs its teardown).

      if (typeof ufn === 'function') {
        try {
          ufn(el);
        } catch (e) {
          /* ignore */
        }
      } // Restore the host module globals, THEN refresh the list — order matters:
      // load() reads window.modJsList[tabKey], so it must run after the restore.


      window.modJsList = savedModJsList;
      window.modJs = savedModJs;
      load();
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [nativeDoc]);
  if (err) return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "Could not load this list"
  });

  if (items === null) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'center',
        padding: 60
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
  }

  var metaItem = function metaItem(label, value) {
    return isEmpty(value) ? null : /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        display: 'inline-flex',
        alignItems: 'center',
        gap: 5,
        maxWidth: 320,
        overflow: 'hidden'
      }
    }, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        color: token.colorTextTertiary
      }
    }, "".concat(label, ":")), /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, value));
  }; // Fields listed in cfg.progressFields render as a real progress bar (a legacy
  // render()'s full-width <Progress> collapses inside the inline meta span).


  var progressSet = new Set(cfg.progressFields || []);

  var metaValue = function metaValue(c, rec) {
    return progressSet.has(c.dataIndex) ? /*#__PURE__*/_react["default"].createElement(_antd.Progress, {
      percent: parseInt(rec[c.dataIndex], 10) || 0,
      size: "small",
      style: {
        width: 130,
        margin: 0
      }
    }) : cellValue(c, rec);
  };

  return /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: 12,
      marginBottom: 16,
      flexWrap: 'wrap'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      flexWrap: 'wrap'
    }
  }, canAdd && !cfg.hideActions && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "primary",
    icon: /*#__PURE__*/_react["default"].createElement(_icons.PlusOutlined, null),
    onClick: doAdd
  }, cfg.addLabel || m && typeof m.getAddNewLabel === 'function' && m.getAddNewLabel() || 'Add New'), m && typeof m.hasCustomTopButtons === 'function' && m.hasCustomTopButtons() && typeof m.getCustomTopButtons === 'function' && /*#__PURE__*/_react["default"].createElement("span", null, m.getCustomTopButtons()), cfg.exportEndpoint && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
    loading: exporting,
    onClick: doExport
  }, cfg.exportLabel || 'Export'), cfg.importEndpoint && /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("input", {
    ref: importInputRef,
    type: "file",
    accept: "application/json,.json",
    style: {
      display: 'none'
    },
    onChange: doImportFile
  }), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.UploadOutlined, null),
    loading: importing,
    onClick: function onClick() {
      return importInputRef.current && importInputRef.current.click();
    }
  }, cfg.importLabel || 'Import')), genCfg && !cfg.hideActions && canAdd && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.AppstoreOutlined, null),
    onClick: openGenerate
  }, genCfg.label || 'Generate'), hasFilters && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.FilterOutlined, null),
    onClick: openFilters
  }, "Filters"), filterString && /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
    color: "blue",
    closable: true,
    onClose: clearFilters,
    style: {
      lineHeight: '28px',
      borderRadius: 6
    }
  }, filterString), bulkEnabled && selectedIds.length > 0 && /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    danger: true,
    icon: /*#__PURE__*/_react["default"].createElement(_icons.DeleteOutlined, null),
    loading: bulkDeleting,
    onClick: doBulkDelete
  }, "Delete ".concat(selectedIds.length, " selected")), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "text",
    onClick: clearSelection
  }, "Clear"))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      gap: 8
    }
  }, cfg.statusFilter && /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    value: statusFilterVal || undefined,
    allowClear: true,
    placeholder: "All statuses",
    style: {
      minWidth: 150
    },
    onChange: function onChange(v) {
      return onStatusFilter(v);
    },
    options: cfg.statusFilter.map(function (s) {
      return {
        value: s,
        label: s
      };
    })
  }), /*#__PURE__*/_react["default"].createElement(_antd.Input.Search, {
    allowClear: true,
    placeholder: "Search\u2026",
    style: {
      maxWidth: 280
    },
    onSearch: onSearch
  }))), items.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "No records"
  }) : /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, bulkEnabled && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      margin: '0 4px 10px'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Checkbox, {
    checked: allOnPageSelected,
    indeterminate: !allOnPageSelected && pageIds.some(function (id) {
      return selectedIds.includes(id);
    }),
    onChange: toggleSelectAll
  }, allOnPageSelected ? 'Deselect all' : 'Select all'), selectedIds.length > 0 && /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      color: token.colorTextTertiary,
      fontSize: 12.5
    }
  }, "".concat(selectedIds.length, " selected"))), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 10
    }
  }, items.map(function (rec) {
    var title = titleOf(rec);
    var rst = iconOf(rec);
    return /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      key: rec.id,
      hoverable: !cfg.disableCardClick && (!!cardClick || hasView || !!docAction),
      onClick: cfg.disableCardClick ? undefined : cfg.cardClickView && hasView ? function () {
        return openView(rec);
      } : cardClick ? function () {
        return cardClick(rec);
      } : docAction ? function () {
        return doDocument(rec);
      } : hasView ? function () {
        return openView(rec);
      } : undefined,
      style: {
        borderRadius: 10,
        boxShadow: _theme.MUI_SHADOW,
        cursor: !cfg.disableCardClick && (cardClick || hasView || docAction) ? 'pointer' : 'default'
      },
      styles: {
        body: {
          padding: '12px 16px'
        }
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 14
      }
    }, bulkEnabled && /*#__PURE__*/_react["default"].createElement(_antd.Checkbox, {
      checked: isSelected(rec.id),
      onClick: function onClick(e) {
        return e.stopPropagation();
      },
      onChange: function onChange() {
        return toggleSelect(rec.id);
      },
      style: {
        flex: '0 0 auto'
      }
    }), cfg.avatarField && !isEmpty(rec[cfg.avatarField]) ? /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
      size: 40,
      src: rec[cfg.avatarField],
      style: {
        flex: '0 0 auto'
      }
    }) : /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        width: 40,
        height: 40,
        borderRadius: 10,
        flex: '0 0 auto',
        background: "".concat(rst.color, "18"),
        color: rst.color,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 18
      }
    }, rst.icon), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        minWidth: 0,
        flex: 1
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 8,
        minWidth: 0
      }
    }, /*#__PURE__*/_react["default"].createElement("span", {
      style: {
        fontWeight: 600,
        fontSize: 14,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      }
    }, isEmpty(title) ? "#".concat(rec.id) : title), tagColumns.map(function (c) {
      var v = cellValue(c, rec); // Per-value colours (e.g. status): cfg.tagColors maps a
      // value -> antd tag colour; falls back to blue.

      var tagColor = cfg.tagColors && cfg.tagColors[v] || 'blue';
      return isEmpty(v) ? null : /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
        key: c.dataIndex,
        color: tagColor,
        style: {
          borderRadius: 6,
          margin: 0,
          flex: '0 0 auto'
        }
      }, cfg.tagIcon, cfg.tagIcon ? ' ' : '', v);
    })), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: 16,
        marginTop: 4,
        color: token.colorTextSecondary,
        fontSize: 12.5,
        flexWrap: 'wrap'
      }
    }, metaColumns.map(function (c) {
      return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, {
        key: c.dataIndex
      }, metaItem(c.label, metaValue(c, rec)));
    }))), !cfg.hideActions && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        gap: 10,
        flex: '0 0 auto'
      },
      onClick: function onClick(e) {
        return e.stopPropagation();
      }
    }, (cfg.extraActions || []).filter(function (a) {
      return a.first;
    }).map(function (a) {
      return (typeof a.show === 'function' ? a.show(m, rec) : true) && typeof m[a.method] === 'function' ? /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
        key: a.key,
        title: a.tip
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: _react["default"].cloneElement(a.icon, {
          style: {
            color: ac(a.color)
          }
        }),
        onClick: function onClick() {
          return m[a.method](rec.id, rec);
        }
      })) : null;
    }), showViewBtn && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "View"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.EyeOutlined, {
        style: {
          color: ac('#1565c0')
        }
      }),
      onClick: function onClick() {
        return openView(rec);
      }
    })), canEdit && rowEditable(rec) && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Edit"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.EditOutlined, {
        style: {
          color: ac('#2e7d32')
        }
      }),
      onClick: function onClick() {
        return openEdit(rec);
      }
    })), !isEmpty(rec.document_link) && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: docAction && docAction.label || 'Document'
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: _react["default"].cloneElement(docAction ? iconByName(docAction.icon) : /*#__PURE__*/_react["default"].createElement(_icons.FileOutlined, null), {
        style: {
          color: ac('#0288d1')
        }
      }),
      onClick: function onClick() {
        return doDocument(rec);
      }
    })), canCopy && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Copy"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CopyOutlined, {
        style: {
          color: ac('#546e7a')
        }
      }),
      onClick: function onClick() {
        return doCopy(rec.id);
      }
    })), rowDeletable(rec) && !cfg.deleteLast && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: cfg.deleteTip || 'Delete'
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: _react["default"].cloneElement(cfg.deleteIcon || /*#__PURE__*/_react["default"].createElement(_icons.DeleteOutlined, null), {
        style: {
          color: ac('#d32f2f')
        }
      }),
      onClick: function onClick() {
        return doDelete(rec);
      }
    })), isApprove && statusOptionsFor(rec).length > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Change Status"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: _react["default"].cloneElement(cfg.statusIcon || /*#__PURE__*/_react["default"].createElement(_icons.MonitorOutlined, null), {
        style: {
          color: ac('#1565c0')
        }
      }),
      onClick: function onClick() {
        return openStatus(rec);
      }
    })), canCancel && !cfg.hideCancelButton && rec.status === 'Approved' && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Cancel"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CloseCircleOutlined, {
        style: {
          color: ac('#d32f2f')
        }
      }),
      onClick: function onClick() {
        return doCancel(rec);
      }
    })), cfg.resubmitAction && typeof cfg.resubmitWhen === 'function' && cfg.resubmitWhen(rec) && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "Re-submit"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.RedoOutlined, {
        style: {
          color: ac('#1565c0')
        }
      }),
      onClick: function onClick() {
        return doResubmit(rec);
      }
    })), hasLogs && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: "View Logs"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.HistoryOutlined, {
        style: {
          color: ac('#546e7a')
        }
      }),
      onClick: function onClick() {
        return openLogs(rec);
      }
    })), (cfg.extraActions || []).filter(function (a) {
      return !a.first;
    }).map(function (a) {
      return (typeof a.show === 'function' ? a.show(m, rec) : true) && typeof m[a.method] === 'function' ? /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
        key: a.key,
        title: a.tip
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: _react["default"].cloneElement(a.icon, {
          style: {
            color: ac(a.color)
          }
        }),
        onClick: function onClick() {
          return m[a.method](rec.id, rec);
        }
      })) : null;
    }), rowDeletable(rec) && cfg.deleteLast && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
      title: cfg.deleteTip || 'Delete'
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      icon: _react["default"].cloneElement(cfg.deleteIcon || /*#__PURE__*/_react["default"].createElement(_icons.DeleteOutlined, null), {
        style: {
          color: ac('#d32f2f')
        }
      }),
      onClick: function onClick() {
        return doDelete(rec);
      }
    })))));
  })), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'flex-end',
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Pagination, {
    current: page,
    pageSize: pageSize,
    total: total,
    onChange: onPage,
    showSizeChanger: !!(m && m.showPageSizeChanger),
    pageSizeOptions: ['8', '16', '24', '50', '100'],
    showTotal: function showTotal(t) {
      return "".concat(t, " records");
    }
  }))), currentElement && m && typeof m.getTableChildComponents === 'function' && m.getTableChildComponents() && (cfg.childSelfModal // The child component renders its OWN modal (e.g. AttendanceModal,
  // controlled by the `element` prop, closed via setCurrentElement(null)).
  // Render it directly — wrapping it in our Modal would show two modals.
  ? _react["default"].cloneElement(m.getTableChildComponents(), {
    element: currentElement,
    adapter: m,
    loading: false
  }) : /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    width: 1040,
    style: {
      top: 24
    },
    footer: null,
    title: titleOf(currentElement),
    onCancel: function onCancel() {
      _setCurrentElement(null);

      try {
        if (m.hideElement) m.hideElement();
      } catch (e) {
        /* ignore */
      }
    },
    styles: {
      body: {
        maxHeight: '82vh',
        overflowY: 'auto'
      }
    }
  }, _react["default"].cloneElement(m.getTableChildComponents(), {
    element: currentElement,
    adapter: m,
    loading: false
  }))), nativeDoc && /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    width: "92%",
    style: {
      top: 16,
      maxWidth: 1280
    },
    footer: null,
    title: docAction && docAction.label || 'Document',
    onCancel: function onCancel() {
      return setNativeDoc(null);
    },
    styles: {
      body: {
        padding: 16,
        minHeight: '60vh',
        maxHeight: '86vh',
        overflowY: 'auto'
      }
    },
    destroyOnClose: true
  }, /*#__PURE__*/_react["default"].createElement("div", {
    ref: nativeElRef
  })), editorUrl && /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    width: "92%",
    style: {
      top: 16,
      maxWidth: 1280
    },
    footer: null,
    title: docAction && docAction.label || 'Document',
    onCancel: function onCancel() {
      setEditorUrl(null);
      load();
    },
    styles: {
      body: {
        padding: 0,
        height: '82vh'
      }
    },
    destroyOnClose: true
  }, /*#__PURE__*/_react["default"].createElement("iframe", {
    title: docAction && docAction.label || 'Document',
    src: editorUrl,
    style: {
      width: '100%',
      height: '100%',
      border: 0,
      display: 'block'
    },
    onLoad: function onLoad(e) {
      try {
        var doc = e.target.contentDocument;

        if (doc && doc.head) {
          var style = doc.createElement('style');
          style.textContent = EMBED_CSS;
          doc.head.appendChild(style);
        }
      } catch (err) {
        /* cross-origin — ignore */
      }
    }
  })), statusRec && /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    title: "Change Status",
    okText: "Update",
    confirmLoading: statusSaving,
    onOk: submitStatus,
    okButtonProps: {
      disabled: !statusValue || cfg.requireStatusReason && !statusReason.trim()
    },
    onCancel: function onCancel() {
      return setStatusRec(null);
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 12,
      paddingTop: 8
    }
  }, /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 6,
      color: token.colorTextSecondary
    }
  }, "New status"), /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    style: {
      width: '100%'
    },
    value: statusValue,
    onChange: setStatusValue,
    options: statusOptionsFor(statusRec)
  })), /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 6,
      color: token.colorTextSecondary
    }
  }, cfg.requireStatusReason && /*#__PURE__*/_react["default"].createElement("span", {
    style: {
      color: token.colorError,
      marginRight: 4
    }
  }, "*"), cfg.requireStatusReason ? 'Reason' : 'Reason (optional)'), /*#__PURE__*/_react["default"].createElement(_antd.Input.TextArea, {
    rows: 3,
    value: statusReason,
    onChange: function onChange(e) {
      return setStatusReason(e.target.value);
    },
    placeholder: "Add a note for this status change\u2026",
    status: cfg.requireStatusReason && !statusReason.trim() ? 'error' : undefined
  })))), logsState && /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    title: "Approval Log",
    footer: null,
    onCancel: function onCancel() {
      return setLogsState(null);
    }
  }, logsState.loading ? /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      padding: 32
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, null)) : logsState.rows.length ? /*#__PURE__*/_react["default"].createElement(_antd.Timeline, {
    items: logsState.rows.map(function (l) {
      return {
        children: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontWeight: 600
          }
        }, "".concat(l.status_from || '', " \u2192 ").concat(l.status_to || '')), /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            color: token.colorTextSecondary,
            fontSize: 12
          }
        }, l.time), l.note ? /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            marginTop: 2
          }
        }, l.note) : null)
      };
    })
  }) : /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "No approval history"
  })), cfg.expenseDialog && /*#__PURE__*/_react["default"].createElement(_ExpenseDialog["default"], {
    open: !!expenseDialog,
    rec: expenseDialog ? expenseDialog.rec : null,
    mode: expenseDialog ? expenseDialog.mode : 'view',
    onClose: function onClose() {
      return setExpenseDialog(null);
    },
    onSaved: function onSaved() {
      return load();
    },
    shellConfig: shellConfig,
    statusOptionsFor: cfg.dialogStatusChange ? statusOptionsForStatus : null
  }), genCfg && /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    title: genCfg.label || 'Generate',
    open: genOpen,
    onCancel: function onCancel() {
      return setGenOpen(false);
    },
    onOk: doGenerate,
    okText: genCfg.label || 'Generate',
    confirmLoading: generating,
    okButtonProps: {
      disabled: !genIndustry
    }
  }, /*#__PURE__*/_react["default"].createElement("p", {
    style: {
      marginTop: 0
    }
  }, "Select an industry to add a set of common ".concat(genCfg.itemsLabel || 'items', ". Existing entries are skipped.")), /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    style: {
      width: '100%'
    },
    placeholder: "Select an industry",
    loading: genLoading,
    value: genIndustry,
    onChange: setGenIndustry,
    showSearch: true,
    optionFilterProp: "label",
    options: genIndustries.map(function (i) {
      return {
        value: i.key,
        label: i.label
      };
    })
  }), genCfg.allowDeleteExisting && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginTop: 16
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Checkbox, {
    checked: genDelete,
    onChange: function onChange(e) {
      return setGenDelete(e.target.checked);
    }
  }, genCfg.deleteExistingLabel || 'Delete existing entries first'), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      color: token.colorTextSecondary,
      fontSize: 12,
      marginTop: 4,
      marginLeft: 24
    }
  }, "Only entries not currently in use are removed; those in use are kept."))));
}

},{"./ExpenseDialog":6,"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],17:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = NativeDocumentModal;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

// Lazy, idempotent loader for an extension bundle that exposes a native
// document-mount function (e.g. the editor's window.mountEditorDocument). Mirrors
// loadNativeBundle in NativeCardList — kept local so this modal has no coupling to
// the card list.
var DOC_CB = String(Date.now());
var docBundles = {};

function loadDocBundle(url) {
  if (docBundles[url]) return docBundles[url];
  var p = new Promise(function (resolve, reject) {
    var s = document.createElement('script');
    s.src = "".concat(url).concat(url.indexOf('?') >= 0 ? '&' : '?', "cb=").concat(DOC_CB);
    s.async = false;

    s.onload = function () {
      return resolve();
    };

    s.onerror = function () {
      s.remove();
      reject(new Error("Failed to load ".concat(url)));
    };

    document.head.appendChild(s);
  }); // Evict on failure so the next open retries instead of replaying the rejection.

  docBundles[url] = p["catch"](function (e) {
    delete docBundles[url];
    throw e;
  });
  return docBundles[url];
}
/**
 * Shell-level modal that mounts a native document editor for an arbitrary
 * document URL (a legacy `g=extension&n=editor|user&...&hash=…` link). Used when
 * something OUTSIDE a card list needs to open a document — e.g. clicking a task
 * notification. It loads the editor bundle and calls its mountFn, snapshotting +
 * restoring the shared window.modJs/modJsList globals the bundle clobbers (same
 * contract as NativeCardList's native documentAction).
 */
// Load a list of bundle URLs strictly in order (each waits for the previous), so
// dependency bundles register their modules before dependents run.


function loadDocBundlesInOrder(urls) {
  return urls.reduce(function (p, url) {
    return p.then(function () {
      return loadDocBundle(url);
    });
  }, Promise.resolve());
}

function NativeDocumentModal(_ref) {
  var documentUrl = _ref.documentUrl,
      bundle = _ref.bundle,
      mountFn = _ref.mountFn,
      title = _ref.title,
      deps = _ref.deps,
      shellConfig = _ref.shellConfig,
      onClose = _ref.onClose;
  var elRef = (0, _react.useRef)(null);
  (0, _react.useEffect)(function () {
    if (!documentUrl) return undefined;
    var el = elRef.current;
    if (!el) return undefined; // The editor bundle (EditorDocument / mountEditor) reads these globals. When
    // the modal opens from a context that hasn't mounted a native module yet (e.g.
    // the dashboard), they may be unset — seed them from the shell config so the
    // bundle URL + REST calls resolve correctly.

    if (shellConfig.baseUrl) window.BASE_URL = window.BASE_URL || shellConfig.baseUrl;
    if (shellConfig.clientBaseUrl) window.CLIENT_BASE_URL = window.CLIENT_BASE_URL || shellConfig.clientBaseUrl; // Legacy adapter code (AdapterBase) reads a bare global `baseUrl` (the
    // service.php endpoint) at construction. NativeModuleHost sets it per module;
    // seed it here for the standalone case.

    if (!window.baseUrl && shellConfig.clientBaseUrl) window.baseUrl = "".concat(shellConfig.clientBaseUrl, "service.php");
    var savedModJsList = window.modJsList;
    var savedModJs = window.modJs;
    var cancelled = false;
    var mountName = mountFn || 'mountEditorDocument';
    var unmountName = mountName.replace('mount', 'unmount');
    var webBase = window.BASE_URL || shellConfig.baseUrl || '';
    var extBase = webBase.replace('/web/', '/extensions/');
    var bundleUrl = bundle ? "".concat(extBase).concat(bundle) : null; // Vendor/dep bundles the extension bundle expects to already be registered
    // (npm externals like moment). NativeModuleHost loads these before any module;
    // when the modal opens standalone (from a notification) we must load them too.

    var depUrls = (deps || []).map(function (d) {
      return /^https?:\/\//.test(d) ? d : "".concat(webBase).concat(d);
    });

    var run = function run() {
      var fn = window[mountName];

      if (typeof fn === 'function') {
        fn(el, {
          documentUrl: documentUrl,
          restApiBase: shellConfig.restApiBase,
          token: shellConfig.token,
          colorMode: window.__shellColorMode || 'light',
          onClose: onClose
        });
      }
    };

    loadDocBundlesInOrder(depUrls).then(function () {
      return bundleUrl ? loadDocBundle(bundleUrl) : Promise.resolve();
    }).then(function () {
      if (!cancelled) run();
    })["catch"](function () {
      /* ignore */
    });
    return function () {
      cancelled = true;
      var ufn = window[unmountName];

      if (typeof ufn === 'function') {
        try {
          ufn(el);
        } catch (e) {
          /* ignore */
        }
      }

      window.modJsList = savedModJsList;
      window.modJs = savedModJs;
    };
  }, [documentUrl]); // eslint-disable-line react-hooks/exhaustive-deps

  if (!documentUrl) return null;
  return /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    open: true,
    width: "92%",
    style: {
      top: 16,
      maxWidth: 1280
    },
    footer: null,
    title: title || 'Document',
    onCancel: onClose,
    styles: {
      body: {
        padding: 16,
        minHeight: '60vh',
        maxHeight: '86vh',
        overflowY: 'auto'
      }
    },
    destroyOnClose: true
  }, /*#__PURE__*/_react["default"].createElement("div", {
    ref: elRef
  }));
}

},{"antd":"antd","react":"react"}],18:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = NativeExtensionView;

var _react = _interopRequireDefault(require("react"));

var _antd = require("antd");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * Mounts a bespoke extension's own React view component natively (no iframe).
 *
 * Many extensions (team, esign, directory, …) are already full React UIs whose
 * `module.js` does `ReactDOM.render(<TheView/>, #content)` — a SEPARATE React
 * root that wouldn't inherit the shell theme. Instead, the extension exposes its
 * view component on `window` (e.g. `window.TeamsAdminView`) and declares
 * `viewGlobal` in its meta.json native tab; we render it INSIDE the shell tree so
 * it inherits the active (light/dark) ConfigProvider for free.
 *
 * The extension's init() (run by NativeModuleHost before tabs render) has already
 * created window.modJs + its controller and wired the API client, so the view's
 * data fetching works exactly as in the legacy page.
 */
function NativeExtensionView(_ref) {
  var viewGlobal = _ref.viewGlobal,
      viewProps = _ref.viewProps;
  var Comp = viewGlobal && typeof window !== 'undefined' ? window[viewGlobal] : null;

  if (!Comp) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 32
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "View not available"
    }));
  } // Pass the props an extension's module.js typically passes its view. Defaults
  // cover the common ones; `viewProps` (from the meta tab) maps any extra prop
  // name -> window global, e.g. { adapter: 'modJs', controller: 'learnExtensionController' }.


  var modJs = typeof window !== 'undefined' ? window.modJs : null;
  var props = {
    apiClient: modJs && modJs.apiClient,
    ice: modJs,
    modJs: modJs
  };

  if (viewProps && _typeof(viewProps) === 'object') {
    Object.keys(viewProps).forEach(function (k) {
      var g = viewProps[k];
      props[k] = g === 'modJs' ? modJs : typeof window !== 'undefined' ? window[g] : undefined;
    });
  }

  return /*#__PURE__*/_react["default"].createElement(Comp, props);
}

},{"antd":"antd","react":"react"}],19:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = NativeModuleHost;

var _react = _interopRequireWildcard(require("react"));

var _reactDom = _interopRequireDefault(require("react-dom"));

var _antd = require("antd");

var _theme = require("./theme");

var _OrgChart = _interopRequireDefault(require("./OrgChart"));

var _CompanyStructureCards = _interopRequireDefault(require("./CompanyStructureCards"));

var _NativeCardList = _interopRequireDefault(require("./NativeCardList"));

var _NativeExtensionView = _interopRequireDefault(require("./NativeExtensionView"));

var _MobileApp = _interopRequireDefault(require("./MobileApp"));

var _ApiAccess = _interopRequireDefault(require("./ApiAccess"));

var _TimeSheets = _interopRequireDefault(require("./TimeSheets"));

var _LeaveEntitlement = _interopRequireDefault(require("./LeaveEntitlement"));

var _LeaveCalendar = _interopRequireDefault(require("./LeaveCalendar"));

var _NativeAdapterView = _interopRequireDefault(require("./NativeAdapterView"));

var _LicenseBlocked = _interopRequireDefault(require("./LicenseBlocked"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

// Tabs may declare a custom React component instead of mounting a legacy adapter.
var TAB_COMPONENTS = {
  OrgChart: _OrgChart["default"],
  CompanyStructureCards: _CompanyStructureCards["default"],
  NativeCardList: _NativeCardList["default"],
  NativeExtensionView: _NativeExtensionView["default"],
  MobileApp: _MobileApp["default"],
  ApiAccess: _ApiAccess["default"],
  TimeSheets: _TimeSheets["default"],
  LeaveEntitlement: _LeaveEntitlement["default"],
  LeaveCalendar: _LeaveCalendar["default"],
  NativeAdapterView: _NativeAdapterView["default"]
};
/**
 * Natively mounts a registered legacy/React module inside the shell — no iframe.
 * It fetches /appshell/module-context, loads the module's JS bundles once, calls
 * its global init() into shell-provided containers (the legacy `#<tab><suffix>`
 * div ids), applies the context the legacy footer.php injects, then drives each
 * tab's adapter (get / field master data) like the footer does. On unmount it
 * tears the adapters down. SPA migration Phase 3.
 */

var loadedScripts = {};

function loadScript(url) {
  if (loadedScripts[url]) return loadedScripts[url];
  var p = new Promise(function (resolve, reject) {
    var s = document.createElement('script');
    s.src = url;
    s.async = false;

    s.onload = function () {
      return resolve();
    };

    s.onerror = function () {
      s.remove();
      reject(new Error("Failed to load ".concat(url)));
    };

    document.head.appendChild(s);
  }); // Evict on failure — a cached rejection would otherwise brick the module for
  // the lifetime of the page (every navigation replays it until a full refresh).

  loadedScripts[url] = p["catch"](function (e) {
    delete loadedScripts[url];
    throw e;
  });
  return loadedScripts[url];
}

function loadScriptsSequential(urls) {
  return urls.reduce(function (prev, u) {
    return prev.then(function () {
      return loadScript(u);
    });
  }, Promise.resolve());
}

function NativeModuleHost(_ref) {
  var group = _ref.group,
      name = _ref.name,
      shellConfig = _ref.shellConfig;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      mc = _useState2[0],
      setMc = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      failed = _useState4[0],
      setFailed = _useState4[1];

  var _useState5 = (0, _react.useState)(true),
      _useState6 = _slicedToArray(_useState5, 2),
      booting = _useState6[0],
      setBooting = _useState6[1];

  var _useState7 = (0, _react.useState)(null),
      _useState8 = _slicedToArray(_useState7, 2),
      activeTab = _useState8[0],
      setActiveTab = _useState8[1];

  var _useState9 = (0, _react.useState)(false),
      _useState10 = _slicedToArray(_useState9, 2),
      licenseBlocked = _useState10[0],
      setLicenseBlocked = _useState10[1];

  var _useState11 = (0, _react.useState)(0),
      _useState12 = _slicedToArray(_useState11, 2),
      retryTick = _useState12[0],
      setRetryTick = _useState12[1];

  var started = (0, _react.useRef)({});

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var isDark = token.colorBgContainer === _theme.MUI_DARK.paper; // Legacy adapter tabs render in their own React root (wrapped in shellThemeWrap,
  // which captures the colour mode at render time), so they don't follow a live
  // theme toggle. Re-render them when the mode flips.

  var skipFirstThemeRun = (0, _react.useRef)(true);
  (0, _react.useEffect)(function () {
    if (skipFirstThemeRun.current) {
      skipFirstThemeRun.current = false;
      return;
    }

    if (!mc || !mc.config) return; // Ensure the global shellThemeWrap reads matches this render's mode — the
    // index.js updater runs in a parent effect (after this child effect).

    window.__shellColorMode = isDark ? 'dark' : 'light';
    var list = window.modJsList || {};
    (mc.config.tabs || []).forEach(function (t) {
      // Adapter-drawn component tabs (NativeAdapterView, e.g. settings) render
      // their own root too — re-render those on a flip as well.
      var adapterDrawn = t.component === 'NativeAdapterView';
      if (t.component && !adapterDrawn) return;
      var m = list[t.key];
      if (!m || typeof m.initTable !== 'function') return;
      var mounted = adapterDrawn ? !!(m.containerOverrides && m.containerOverrides.Table) : started.current[t.key];

      if (mounted) {
        try {
          m.tableInitialized = false;
          m.initTable();
        } catch (e) {
          /* ignore */
        }
      }
    }); // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isDark]); // 1) fetch the module context

  (0, _react.useEffect)(function () {
    var alive = true;
    setMc(null);
    setFailed(false);
    setBooting(true);
    setLicenseBlocked(false);
    started.current = {}; // try/catch because instrumented fetch (e.g. an injected monitoring agent's
    // wrapper) can throw synchronously — without it that exception escapes the
    // effect and the pane goes blank instead of showing the failure state.

    try {
      fetch("".concat(shellConfig.restApiBase, "appshell/module-context?group=").concat(encodeURIComponent(group), "&name=").concat(encodeURIComponent(name)), {
        headers: {
          Authorization: "Bearer ".concat(shellConfig.token)
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (d) {
        if (alive) {
          if (d && d.config) setMc(d);else setFailed(true);
        }
      })["catch"](function () {
        if (alive) setFailed(true);
      });
    } catch (e) {
      if (alive) setFailed(true);
    }

    return function () {
      alive = false;
    };
  }, [group, name, shellConfig, retryTick]);

  var startTab = function startTab(key) {
    // Component tabs (e.g. the org chart) are pure React — no legacy adapter to drive.
    var cfg = (mc && mc.config && mc.config.tabs ? mc.config.tabs : []).find(function (t) {
      return t.key === key;
    });
    if (cfg && cfg.component) return;
    var list = window.modJsList || {};
    var m = list[key];
    if (!m) return;
    window.modJs = m;
    if (started.current[key]) return;
    started.current[key] = true;

    try {
      m.get([]);

      if (!m.isV2) {
        if (m.initialFilter != null) m.initFieldMasterData(null, m.setFilterExternal);else m.initFieldMasterData();
      }
    } catch (e) {
      /* ignore */
    }
  }; // 2) once context is in and the tab divs are rendered: load scripts, init, wire


  (0, _react.useEffect)(function () {
    if (!mc || !mc.config) return undefined;
    var destroyed = false;
    var ctx = mc.context || {};
    window.CLIENT_BASE_URL = ctx.clientUrl;
    window.BASE_URL = ctx.webBaseUrl;
    window.baseUrl = ctx.baseUrl;
    var urls = (mc.config.scripts || []).map(function (s) {
      return /^https?:\/\//.test(s) || s.startsWith('//') ? s : (ctx.webBaseUrl || '') + s;
    });
    loadScriptsSequential(urls).then(function () {
      if (destroyed) return;
      var initFn = window[mc.config.initFn];

      if (typeof initFn !== 'function') {
        setFailed(true);
        return;
      }

      var isProModule = !!(window.iceProModules && window.iceProModules[mc.config.initFn]);
      var lic = mc.license;

      if (isProModule && lic && (!lic.has_license || lic.is_expired)) {
        setLicenseBlocked(true);
        setBooting(false);
        return;
      }

      initFn(mc.data || {});
      var list = window.modJsList || {};
      Object.keys(list).forEach(function (k) {
        var m = list[k];

        try {
          // Tag each adapter with the module it belongs to, so its data.php
          // requests declare scope explicitly (fixes the shared-session
          // modulePath data-scope bug — see docs/DATA_SCOPE_ISSUE.md).
          m.spaModuleGroup = group;
          m.spaModuleName = name;
          m.setTranslations(ctx.translations || {});
          m.setFieldTemplates(ctx.fieldTemplates || {});
          m.setTemplates(ctx.templates || {});
          m.setCustomTemplates(ctx.customTemplates || {});
          m.setUser(ctx.user || {});
          m.initSourceMappings();
          m.setBaseUrl(ctx.baseUrl);
          m.setClientUrl(ctx.clientUrl);
          m.setCurrentProfile(null);
          m.setInstanceId(ctx.instanceId || '');
          m.setApiUrl(ctx.restApiBase);
          m.setupApiClient(shellConfig.token);
        } catch (e) {
          /* ignore */
        }
      });
      setBooting(false); // Default to the first tab, unless a caller requested a specific start tab
      // (e.g. a dashboard tile deep-linking into a module's tab). The hint is
      // consumed once and only if it matches a tab this module actually has.

      var first = mc.config.tabs[0].key;

      try {
        var desired = window.__iceShellStartTab;

        if (desired && mc.config.tabs.some(function (t) {
          return t.key === desired;
        })) {
          first = desired;
        }

        window.__iceShellStartTab = null;
      } catch (e) {
        /* ignore */
      }

      setActiveTab(first);
      startTab(first); // Seam for views INSIDE a module (e.g. the employee profile's
      // Qualifications edit buttons) to switch this host's active tab —
      // the legacy global switchTab() clicks a legacy tab anchor that does
      // not exist in the SPA. Optionally applies a filter to the target
      // tab's adapter (e.g. {employee: id}) before it loads/reloads.

      window.iceShellSwitchModuleTab = function (key, filter) {
        var list = window.modJsList || {};
        var m = list[key];

        if (m && filter && typeof m.setFilter === 'function') {
          m.setFilter(filter);
          m.filtersAlreadySet = true;
        }

        setActiveTab(key);
        startTab(key); // Tab already mounted (e.g. a wired card list): refresh so the new
        // filter takes effect and the filter tag shows.

        try {
          if (m && m.tableContainer && m.tableContainer.current) {
            if (filter) m.tableContainer.current.setFilterData(filter);
            m.tableContainer.current.reload();
          }
        } catch (e) {
          /* ignore */
        }

        try {
          window.scrollTo({
            top: 0
          });
        } catch (e) {
          /* ignore */
        }
      };
    })["catch"](function () {
      if (!destroyed) setFailed(true);
    });
    return function () {
      destroyed = true;

      try {
        mc.config.tabs.forEach(function (t) {
          return (t.ids || []).forEach(function (id) {
            var el = document.getElementById(id);

            if (el) {
              try {
                _reactDom["default"].unmountComponentAtNode(el);
              } catch (e) {
                /* */
              }
            }
          });
        });
      } catch (e) {
        /* */
      }

      delete window.iceShellSwitchModuleTab;
      window.modJs = undefined;
      window.modJsList = undefined;
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mc]);

  if (failed) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 32,
        textAlign: 'center'
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
      description: "Could not load this module"
    }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      type: "primary",
      onClick: function onClick() {
        return setRetryTick(function (t) {
          return t + 1;
        });
      }
    }, "Retry")));
  }

  if (!mc) {
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'center',
        padding: 80
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
  }

  if (licenseBlocked) {
    return /*#__PURE__*/_react["default"].createElement(_LicenseBlocked["default"], {
      license: mc.license
    });
  } // A "group" (e.g. leaves' "For Approval" tabs about OTHER employees) is set
  // off from the preceding tabs by a divider + caption on its first tab, and
  // each grouped tab can carry a pending-count bubble.


  var reviewTabs = mc.config.tabs.filter(function (t) {
    return t.group === 'review';
  });
  var firstReviewKey = reviewTabs.length ? reviewTabs[0].key : null;
  var groupLabel = reviewTabs.length ? reviewTabs[0].groupLabel || '' : '';

  var renderTabLabel = function renderTabLabel(t) {
    var badge = typeof t.count === 'number' && t.count > 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Badge, {
      count: t.count,
      size: "small",
      overflowCount: 99,
      style: {
        marginLeft: 6
      }
    }) : null;

    var inner = /*#__PURE__*/_react["default"].createElement("span", null, t.label, badge);

    if (t.key && t.key === firstReviewKey) {
      return /*#__PURE__*/_react["default"].createElement("span", {
        style: {
          display: 'inline-flex',
          alignItems: 'center'
        }
      }, /*#__PURE__*/_react["default"].createElement("span", {
        style: {
          display: 'inline-block',
          width: 1,
          height: 18,
          background: token.colorSplit,
          marginRight: 14
        }
      }), groupLabel && /*#__PURE__*/_react["default"].createElement("span", {
        style: {
          fontSize: 11,
          textTransform: 'uppercase',
          letterSpacing: 0.6,
          color: token.colorTextTertiary,
          marginRight: 10,
          fontWeight: 600
        }
      }, groupLabel), inner);
    }

    return inner;
  };

  var items = mc.config.tabs.map(function (t) {
    if (t.component) {
      var Comp = TAB_COMPONENTS[t.component]; // Only mount the native component AFTER init has run (booting === false),
      // so window.modJs + its apiClient are in place. On SPA re-entry the bundle
      // is already cached, so without this gate a bespoke view would mount and
      // fetch before initFn re-ran (window.modJs was nulled on the prior unmount),
      // showing an empty state. The booting overlay covers this brief gap.

      return {
        key: t.key,
        label: renderTabLabel(t),
        children: Comp && !booting ? /*#__PURE__*/_react["default"].createElement(Comp, {
          shellConfig: shellConfig,
          tabKey: t.key,
          entity: t.entity,
          cardConfig: t.card,
          viewGlobal: t.viewGlobal,
          viewProps: t.props
        }) : null
      };
    } // Legacy adapter tabs render via their own React root; the adapter wraps that
    // render in shellThemeWrap so it follows the shell's colour mode. Use the
    // themed container surface so the legacy view sits on the right background.


    return {
      key: t.key,
      label: renderTabLabel(t),
      forceRender: true,
      children: /*#__PURE__*/_react["default"].createElement("div", {
        style: _objectSpread({
          background: token.colorBgContainer,
          color: token.colorText,
          borderRadius: 10,
          padding: 12,
          minHeight: 320
        }, t.scroll ? {
          overflowX: 'auto'
        } : {})
      }, (t.ids || []).map(function (id) {
        return /*#__PURE__*/_react["default"].createElement("div", {
          id: id,
          key: id,
          className: "reviewBlock"
        });
      }))
    };
  });
  var singleTab = items.length <= 1;
  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 24,
      position: 'relative'
    }
  }, booting && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      position: 'absolute',
      inset: 0,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2,
      background: token.colorBgLayout,
      opacity: 0.6
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
    size: "large"
  })), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      background: token.colorBgContainer,
      borderRadius: 14,
      boxShadow: _theme.MUI_SHADOW,
      padding: singleTab ? 20 : '4px 20px 20px'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Tabs, {
    activeKey: activeTab || items[0] && items[0].key,
    onChange: function onChange(k) {
      setActiveTab(k);
      startTab(k);
    },
    items: items,
    tabBarStyle: singleTab ? {
      display: 'none'
    } : {
      marginBottom: 16
    }
  })));
}

},{"./ApiAccess":1,"./CompanyStructureCards":3,"./LeaveCalendar":9,"./LeaveEntitlement":10,"./LicenseBlocked":11,"./MobileApp":13,"./NativeAdapterView":15,"./NativeCardList":16,"./NativeExtensionView":18,"./OrgChart":22,"./TimeSheets":23,"./theme":26,"antd":"antd","react":"react","react-dom":"react-dom"}],20:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = News;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * News — shows an announcement from icehrm.com on the dashboard, mirroring the
 * legacy dashboard "news" feature. The backend (appshell/news) fetches and
 * caches icehrm.com's /sapi/news for the current version + user level and only
 * returns an item when it's flagged to show and the user hasn't dismissed it.
 * Dismissing suppresses it per user for `dismiss_period` seconds via the shared
 * dismiss-news endpoint (UserMeta), the same store the legacy UI used.
 */
function News(_ref) {
  var config = _ref.config;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      news = _useState2[0],
      setNews = _useState2[1];

  (0, _react.useEffect)(function () {
    var cancelled = false;
    fetch("".concat(config.restApiBase, "appshell/news"), {
      headers: {
        Authorization: "Bearer ".concat(config.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.ok ? r.json() : null;
    }).then(function (item) {
      if (!cancelled) setNews(item && item.id ? item : null);
    })["catch"](function () {
      /* no news on failure — same as legacy */
    });
    return function () {
      cancelled = true;
    };
  }, [config.restApiBase, config.token]);

  if (!news || !news.id) {
    return null;
  }

  var dismiss = function dismiss() {
    setNews(null);
    fetch("".concat(config.restApiBase, "dismiss-news"), {
      method: 'POST',
      headers: {
        Authorization: "Bearer ".concat(config.token),
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        id: news.id,
        period: news.dismiss_period || 86400
      })
    })["catch"](function () {
      /* dismissal is best-effort */
    });
  };

  return /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "info",
    showIcon: true,
    style: {
      margin: '16px 24px 0'
    },
    message: news.title,
    description: news.message,
    action: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, news.url && news.button_text ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "small",
      type: "primary",
      onClick: function onClick() {
        return window.open(news.url, '_blank', 'noopener,noreferrer');
      }
    }, news.button_text) : null, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "small",
      onClick: dismiss
    }, "Dismiss"))
  });
}

},{"antd":"antd","react":"react"}],21:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = Notifications;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text; // Legacy tab anchors (used in notification action URLs) → native SPA tab keys,
// where they differ. Anchors that already match a SPA tab key pass through.

var LEGACY_TAB_ALIASES = {
  // Leave module: legacy notification anchors (tab + mapping name) → native SPA
  // tab keys, which the SPA rebuild shortened.
  tabSubEmployeeLeaveAll: 'tabSubLeaveAll',
  // "applied for a leave" → Leave Requests (Direct Reports)
  tabEmployeeLeaveApproval: 'tabLeaveApproval',
  // "assigned … for approval" → Approval Requests
  tabEmployeeLeaveApproved: 'tabMyLeaveApproved',
  // "leave approved" → Approved Leave
  tabSubEmployeeLeaveCancel: 'tabSubLeaveCancel' // cancellation request → Leave Cancellation Requests

}; // Notifications come from the legacy service.php (cookie session), the same
// source the legacy top bar polled. The shell reuses it with credentials.

function parseAction(action) {
  try {
    var a = typeof action === 'string' ? JSON.parse(action) : action;

    if (a && a.type === 'url' && a.url) {
      // The legacy anchor (e.g. #tabSubEmployeeLeaveAll) names the tab the
      // notification should land on — strip it before query parsing.
      var hashIdx = a.url.indexOf('#');
      var frag = hashIdx >= 0 ? a.url.slice(hashIdx + 1) : null;
      var urlNoFrag = hashIdx >= 0 ? a.url.slice(0, hashIdx) : a.url; // The url may be a full legacy URL or just a query string; parse the part
      // after '?' so the first key isn't swallowed by the scheme/path.

      var qs = urlNoFrag.indexOf('?') >= 0 ? urlNoFrag.split('?')[1] : urlNoFrag;
      var sp = new URLSearchParams(qs);
      var g = sp.get('g');
      var n = sp.get('n'); // The editor extension is a document viewer, not a standalone module — it
      // can't be mounted by route (it needs a hash/object). Open it in the native
      // document modal instead (e.g. a "you were assigned a task list" link).

      if (g === 'extension' && n === 'editor|user' && (sp.get('hash') || sp.get('object'))) {
        return {
          doc: a.url
        };
      }

      if (g && n) {
        var tab = frag ? LEGACY_TAB_ALIASES[frag] || frag : null;
        return {
          g: g,
          n: n,
          tab: tab
        };
      }
    }
  } catch (e) {
    /* ignore */
  }

  return null;
}

function Notifications(_ref) {
  var clientBaseUrl = _ref.clientBaseUrl,
      onNavigate = _ref.onNavigate,
      onOpenDocument = _ref.onOpenDocument;

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var _useState = (0, _react.useState)(0),
      _useState2 = _slicedToArray(_useState, 2),
      count = _useState2[0],
      setCount = _useState2[1];

  var _useState3 = (0, _react.useState)([]),
      _useState4 = _slicedToArray(_useState3, 2),
      list = _useState4[0],
      setList = _useState4[1];

  var _useState5 = (0, _react.useState)(false),
      _useState6 = _slicedToArray(_useState5, 2),
      open = _useState6[0],
      setOpen = _useState6[1];

  var load = (0, _react.useCallback)(function () {
    fetch("".concat(clientBaseUrl, "service.php?a=getNotifications"), {
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (j) {
      if (j && j.data) {
        setCount(j.data[0] || 0);
        setList(Array.isArray(j.data[1]) ? j.data[1] : []);
      }
    })["catch"](function () {});
  }, [clientBaseUrl]);
  (0, _react.useEffect)(function () {
    load();
    var id = setInterval(load, 60000);
    return function () {
      return clearInterval(id);
    };
  }, [load]);

  var clearAll = function clearAll() {
    fetch("".concat(clientBaseUrl, "service.php?a=clearNotifications"), {
      credentials: 'same-origin'
    }).then(function () {
      setCount(0);
      load();
    })["catch"](function () {});
  };

  var handleClick = function handleClick(item) {
    var dest = parseAction(item.action);
    setOpen(false);
    if (!dest) return;

    if (dest.doc && onOpenDocument) {
      onOpenDocument(dest.doc);
      return;
    }

    if (dest.g && onNavigate) {
      if (dest.tab) {
        // Land on the tab the notification points at (e.g. a leave application →
        // "Leave Requests (Direct Reports)"). NativeModuleHost consumes the hint
        // at module boot — and only if the module actually has that tab.
        try {
          window.__iceShellStartTab = dest.tab; // Already viewing that module? Switch its tab directly.

          var cur = decodeURIComponent(window.location.hash.replace(/^#/, ''));

          if (cur === "".concat(dest.g, "::").concat(dest.n) && window.iceShellSwitchModuleTab) {
            window.iceShellSwitchModuleTab(dest.tab);
          }
        } catch (e) {
          /* ignore */
        }
      }

      onNavigate(dest.g, dest.n);
    }
  };

  var panel = /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      width: 340,
      maxHeight: 440,
      overflow: 'auto'
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      padding: '4px 4px 10px',
      borderBottom: "1px solid ".concat(token.colorBorderSecondary),
      marginBottom: 4
    }
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    strong: true
  }, "Notifications"), list.length > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "link",
    size: "small",
    onClick: clearAll
  }, "Mark all read")), list.length === 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
    description: "No notifications",
    style: {
      padding: 16
    }
  }) : /*#__PURE__*/_react["default"].createElement(_antd.List, {
    dataSource: list,
    renderItem: function renderItem(item) {
      return /*#__PURE__*/_react["default"].createElement(_antd.List.Item, {
        style: {
          padding: '10px 6px',
          cursor: parseAction(item.action) ? 'pointer' : 'default'
        },
        onClick: function onClick() {
          return handleClick(item);
        }
      }, /*#__PURE__*/_react["default"].createElement(_antd.List.Item.Meta, {
        avatar: /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
          src: item.image,
          icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null),
          size: "small"
        }),
        title: /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            fontSize: 13,
            fontWeight: 500
          }
        }, item.type || 'Notification'),
        description: /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontSize: 13,
            color: token.colorText
          }
        }, item.message), /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontSize: 11,
            color: token.colorTextTertiary,
            marginTop: 2
          }
        }, item.time))
      }));
    }
  }));

  return /*#__PURE__*/_react["default"].createElement(_antd.Popover, {
    content: panel,
    trigger: "click",
    open: open,
    onOpenChange: setOpen,
    placement: "bottomRight"
  }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    type: "text",
    style: {
      display: 'flex',
      alignItems: 'center'
    },
    "aria-label": "Notifications"
  }, /*#__PURE__*/_react["default"].createElement(_antd.Badge, {
    count: count,
    size: "small",
    overflowCount: 99
  }, /*#__PURE__*/_react["default"].createElement(_icons.BellOutlined, {
    style: {
      fontSize: 18,
      color: '#fff'
    }
  }))));
}

},{"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],22:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = OrgChart;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

var _theme = require("./theme");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

// A clean, dependency-free top-down org chart (pure-CSS connectors) for the
// company structure. Replaces the legacy d3 graph.
var orgCss = function orgCss(token) {
  return "\n.org-wrap { overflow-x: auto; padding: 12px 8px 24px; }\n.org-tree, .org-tree ul { position: relative; padding-top: 22px; display: flex; justify-content: center; }\n.org-tree ul { padding-left: 0; }\n.org-tree li {\n  list-style: none; position: relative; padding: 22px 10px 0; text-align: center;\n}\n.org-tree li::before, .org-tree li::after {\n  content: ''; position: absolute; top: 0; right: 50%;\n  border-top: 2px solid ".concat(token.colorBorderSecondary, "; width: 50%; height: 22px;\n}\n.org-tree li::after { right: auto; left: 50%; border-left: 2px solid ").concat(token.colorBorderSecondary, "; }\n.org-tree li:only-child::after, .org-tree li:only-child::before { display: none; }\n.org-tree li:only-child { padding-top: 0; }\n.org-tree li:first-child::before, .org-tree li:last-child::after { border: 0 none; }\n.org-tree li:last-child::before { border-right: 2px solid ").concat(token.colorBorderSecondary, "; border-radius: 0 6px 0 0; }\n.org-tree li:first-child::after { border-radius: 6px 0 0 0; }\n.org-tree ul ul::before {\n  content: ''; position: absolute; top: 0; left: 50%;\n  border-left: 2px solid ").concat(token.colorBorderSecondary, "; width: 0; height: 22px;\n}\n.org-node {\n  display: inline-flex; align-items: center; gap: 12px;\n  padding: 12px 16px; min-width: 180px; max-width: 240px;\n  background: ").concat(token.colorBgContainer, "; border: 1px solid ").concat(token.colorBorderSecondary, "; border-radius: 12px;\n  box-shadow: ").concat(_theme.MUI_SHADOW, "; text-align: left; transition: box-shadow .15s, transform .15s;\n}\n.org-node:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }\n.org-node .ic {\n  width: 38px; height: 38px; border-radius: 10px; flex: 0 0 auto;\n  display: flex; align-items: center; justify-content: center; font-size: 18px;\n}\n.org-node .ttl { font-weight: 600; font-size: 14px; line-height: 1.2; color: ").concat(token.colorText, "; }\n.org-node .sub { font-size: 12px; color: ").concat(token.colorTextSecondary, "; margin-top: 2px; }\n");
};

var TYPE_STYLE = {
  Company: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.BankOutlined, null),
    color: _theme.MUI.primary
  },
  'Head Office': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.HomeOutlined, null),
    color: '#0288d1'
  },
  'Regional Office': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ClusterOutlined, null),
    color: '#7b1fa2'
  },
  Department: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#2e7d32'
  },
  Unit: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#ed6c02'
  },
  'Sub Unit': {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.TeamOutlined, null),
    color: '#ed6c02'
  },
  Other: {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#607d8b'
  }
};

function nodeStyle(type) {
  return TYPE_STYLE[type] || {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ApartmentOutlined, null),
    color: '#607d8b'
  };
}

function buildForest(nodes) {
  var byId = {};
  nodes.forEach(function (n) {
    byId[n.id] = _objectSpread({}, n, {
      children: []
    });
  });
  var roots = [];
  nodes.forEach(function (n) {
    if (n.parent && byId[n.parent]) byId[n.parent].children.push(byId[n.id]);else roots.push(byId[n.id]);
  });
  return roots;
}

function NodeCard(_ref) {
  var node = _ref.node;
  var st = nodeStyle(node.type);
  return /*#__PURE__*/_react["default"].createElement("div", {
    className: "org-node"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    className: "ic",
    style: {
      background: "".concat(st.color, "18"),
      color: st.color
    }
  }, st.icon), /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      minWidth: 0
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    className: "ttl"
  }, node.title), /*#__PURE__*/_react["default"].createElement("div", {
    className: "sub"
  }, node.type || 'Unit', node.headcount > 0 && /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, ' · ', /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, {
    style: {
      fontSize: 11
    }
  }), ' ', node.headcount))));
}

function TreeNode(_ref2) {
  var node = _ref2.node;
  return /*#__PURE__*/_react["default"].createElement("li", null, /*#__PURE__*/_react["default"].createElement(NodeCard, {
    node: node
  }), node.children && node.children.length > 0 && /*#__PURE__*/_react["default"].createElement("ul", null, node.children.map(function (c) {
    return /*#__PURE__*/_react["default"].createElement(TreeNode, {
      key: c.id,
      node: c
    });
  })));
}

function OrgChart(_ref3) {
  var shellConfig = _ref3.shellConfig;

  var _useState = (0, _react.useState)(null),
      _useState2 = _slicedToArray(_useState, 2),
      nodes = _useState2[0],
      setNodes = _useState2[1];

  var _useState3 = (0, _react.useState)(false),
      _useState4 = _slicedToArray(_useState3, 2),
      err = _useState4[0],
      setErr = _useState4[1];

  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  (0, _react.useEffect)(function () {
    var alive = true;
    fetch("".concat(shellConfig.restApiBase, "appshell/org-structure"), {
      headers: {
        Authorization: "Bearer ".concat(shellConfig.token)
      },
      credentials: 'same-origin'
    }).then(function (r) {
      return r.json();
    }).then(function (d) {
      if (alive) setNodes(d && d.nodes || []);
    })["catch"](function () {
      if (alive) setErr(true);
    });
    return function () {
      alive = false;
    };
  }, [shellConfig]);
  if (err) return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "Could not load the org chart"
  });
  if (!nodes) return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      padding: 60
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, null));
  if (nodes.length === 0) return /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
    description: "No company structure defined yet"
  });
  var roots = buildForest(nodes);
  return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("style", null, orgCss(token)), /*#__PURE__*/_react["default"].createElement("div", {
    className: "org-wrap"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    className: "org-tree"
  }, /*#__PURE__*/_react["default"].createElement("ul", null, roots.map(function (r) {
    return /*#__PURE__*/_react["default"].createElement(TreeNode, {
      key: r.id,
      node: r
    });
  })))));
}

},{"./theme":26,"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],23:[function(require,module,exports){
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = TimeSheets;

var _react = _interopRequireWildcard(require("react"));

var _antd = require("antd");

var _icons = require("@ant-design/icons");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var Text = _antd.Typography.Text,
    Title = _antd.Typography.Title;
var STATUS_COLORS = {
  Pending: 'orange',
  Submitted: 'blue',
  Approved: 'green',
  Rejected: 'red'
}; // EmployeeLeaves.status values (see the leave module's approval workflow).

var LEAVE_STATUS_COLORS = {
  Approved: 'green',
  Pending: 'orange',
  Processing: 'blue',
  Rejected: 'red',
  'Cancellation Requested': 'gold',
  Cancelled: 'default'
};
var MANAGER_LEVELS = ['Admin', 'Manager', 'Restricted Admin', 'Restricted Manager'];
var MODULE = 'modules=time_sheets';
var DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

var ctx = function ctx() {
  return typeof window !== 'undefined' && window.__timesheetsCtx || {};
};

var adapters = function adapters() {
  return typeof window !== 'undefined' && window.modJsList || {};
};

function fmtDate(d) {
  if (!d) return '';
  var dt = new Date("".concat(String(d).slice(0, 10), "T00:00:00"));
  if (Number.isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
} // Compact date for the leave list: "Mon, Aug 3".


function fmtShortDate(d) {
  if (!d) return '';
  var dt = new Date("".concat(String(d).slice(0, 10), "T00:00:00"));
  if (Number.isNaN(dt.getTime())) return String(d);
  return dt.toLocaleDateString(undefined, {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  });
} // A leave day is stored as 'Full Day', 'Half Day - Morning', '2 Hours - Afternoon', …
// Anything that is not a full day is a partial day and gets the softer colour.


function isFullDayLeave(type) {
  return /^\s*full day\s*$/i.test(String(type || ''));
}

function parseDT(s) {
  if (!s) return null;
  var dt = new Date(String(s).replace(' ', 'T'));
  return Number.isNaN(dt.getTime()) ? null : dt;
}

function fmtTime(dt) {
  return dt ? dt.toLocaleTimeString(undefined, {
    hour: '2-digit',
    minute: '2-digit'
  }) : '';
}

function duration(start, end) {
  var a = parseDT(start);
  var b = parseDT(end);
  if (!a || !b) return '';
  var min = Math.max(0, Math.round((b - a) / 60000));
  return "".concat(Math.floor(min / 60), "h ").concat(min % 60, "m");
} // Run an adapter custom action and resolve with the server payload.


var actionSeq = 0;

function callAction(adapter, action, req) {
  var isPost = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
  return new Promise(function (resolve, reject) {
    if (!adapter || typeof adapter.customAction !== 'function') {
      reject(new Error('no adapter'));
      return;
    }

    actionSeq += 1;
    var okName = "__ts_ok_".concat(actionSeq);
    var failName = "__ts_fail_".concat(actionSeq);

    adapter[okName] = function (payload) {
      return resolve(payload);
    };

    adapter[failName] = function (payload) {
      return reject(payload || new Error('action failed'));
    };

    var cb = {
      callBackData: [],
      callBackSuccess: okName,
      callBackFail: failName
    };

    try {
      adapter.customAction(action, MODULE, JSON.stringify(req || {}), cb, isPost);
    } catch (e) {
      reject(e);
    }
  });
}

function TimeSheets() {
  var _theme$useToken = _antd.theme.useToken(),
      token = _theme$useToken.token;

  var isManager = MANAGER_LEVELS.indexOf(ctx().userLevel) !== -1;

  var _useState = (0, _react.useState)('list'),
      _useState2 = _slicedToArray(_useState, 2),
      view = _useState2[0],
      setView = _useState2[1]; // 'list' | 'grid' | 'calendar'


  var _useState3 = (0, _react.useState)('All'),
      _useState4 = _slicedToArray(_useState3, 2),
      tab = _useState4[0],
      setTab = _useState4[1];

  var _useState5 = (0, _react.useState)(null),
      _useState6 = _slicedToArray(_useState5, 2),
      reportEmp = _useState6[0],
      setReportEmp = _useState6[1]; // Direct Reports employee filter


  var _useState7 = (0, _react.useState)(['Submitted']),
      _useState8 = _slicedToArray(_useState7, 2),
      reportStatuses = _useState8[0],
      setReportStatuses = _useState8[1]; // visible statuses


  var _useState9 = (0, _react.useState)([]),
      _useState10 = _slicedToArray(_useState9, 2),
      selectedReportIds = _useState10[0],
      setSelectedReportIds = _useState10[1]; // bulk-approve selection


  var _useState11 = (0, _react.useState)(false),
      _useState12 = _slicedToArray(_useState11, 2),
      bulkApproving = _useState12[0],
      setBulkApproving = _useState12[1];

  var _useState13 = (0, _react.useState)(null),
      _useState14 = _slicedToArray(_useState13, 2),
      myRows = _useState14[0],
      setMyRows = _useState14[1];

  var _useState15 = (0, _react.useState)(null),
      _useState16 = _slicedToArray(_useState15, 2),
      reportRows = _useState16[0],
      setReportRows = _useState16[1];

  var _useState17 = (0, _react.useState)({}),
      _useState18 = _slicedToArray(_useState17, 2),
      reportLeave = _useState18[0],
      setReportLeave = _useState18[1]; // { timesheetId: leaveDays }


  var _useState19 = (0, _react.useState)(false),
      _useState20 = _slicedToArray(_useState19, 2),
      reportLeaveOn = _useState20[0],
      setReportLeaveOn = _useState20[1]; // leave module installed?


  var _useState21 = (0, _react.useState)(null),
      _useState22 = _slicedToArray(_useState21, 2),
      busyId = _useState22[0],
      setBusyId = _useState22[1];

  var _useState23 = (0, _react.useState)(null),
      _useState24 = _slicedToArray(_useState23, 2),
      statusModal = _useState24[0],
      setStatusModal = _useState24[1];

  var _useState25 = (0, _react.useState)(null),
      _useState26 = _slicedToArray(_useState25, 2),
      current = _useState26[0],
      setCurrent = _useState26[1]; // { id, date_start, date_end, status, readOnly }
  // Grid state


  var _useState27 = (0, _react.useState)(null),
      _useState28 = _slicedToArray(_useState27, 2),
      grid = _useState28[0],
      setGrid = _useState28[1];

  var _useState29 = (0, _react.useState)({}),
      _useState30 = _slicedToArray(_useState29, 2),
      edits = _useState30[0],
      setEdits = _useState30[1];

  var _useState31 = (0, _react.useState)([]),
      _useState32 = _slicedToArray(_useState31, 2),
      leaveDays = _useState32[0],
      setLeaveDays = _useState32[1]; // [{ date:'Y-m-d', type, half }]


  var _useState33 = (0, _react.useState)(false),
      _useState34 = _slicedToArray(_useState33, 2),
      gridLoading = _useState34[0],
      setGridLoading = _useState34[1];

  var _useState35 = (0, _react.useState)(false),
      _useState36 = _slicedToArray(_useState35, 2),
      saving = _useState36[0],
      setSaving = _useState36[1]; // Calendar / entries state


  var _useState37 = (0, _react.useState)(null),
      _useState38 = _slicedToArray(_useState37, 2),
      cal = _useState38[0],
      setCal = _useState38[1]; // { entries, employee, timesheet }


  var _useState39 = (0, _react.useState)(false),
      _useState40 = _slicedToArray(_useState39, 2),
      calLoading = _useState40[0],
      setCalLoading = _useState40[1];

  var _useState41 = (0, _react.useState)([]),
      _useState42 = _slicedToArray(_useState41, 2),
      tsLogs = _useState42[0],
      setTsLogs = _useState42[1]; // approval log entries (newest first)
  // Leave requests overlapping the open timesheet, every status:
  // { available, requests: [{ id, leave_type, date_start, date_end, status, details, days, days_total }] }


  var _useState43 = (0, _react.useState)({
    available: false,
    requests: []
  }),
      _useState44 = _slicedToArray(_useState43, 2),
      leaveReq = _useState44[0],
      setLeaveReq = _useState44[1];

  var _useState45 = (0, _react.useState)(null),
      _useState46 = _slicedToArray(_useState45, 2),
      rejectModal = _useState46[0],
      setRejectModal = _useState46[1]; // { id, note } | null
  // ---- list loading -------------------------------------------------------


  var loadMine = (0, _react.useCallback)(function () {
    var a = adapters().tabEmployeeTimeSheetAll;

    if (!a || !a.dataPipe) {
      setMyRows([]);
      return;
    }

    setMyRows(null);
    a.dataPipe.get({
      page: 1,
      limit: 500,
      search: ''
    }).then(function (d) {
      return setMyRows(d && d.items || []);
    })["catch"](function () {
      return setMyRows([]);
    });
  }, []);
  var loadReports = (0, _react.useCallback)(function () {
    var a = adapters().tabSubEmployeeTimeSheetAll;

    if (!a || !a.dataPipe) {
      setReportRows([]);
      return;
    }

    setReportRows(null);
    a.dataPipe.get({
      page: 1,
      limit: 500,
      search: ''
    }).then(function (d) {
      var items = d && d.items || [];
      setReportRows(items); // Leave time per timesheet (only when the leave module is installed).

      var ids = items.map(function (r) {
        return r.id;
      });

      if (ids.length) {
        callAction(a, 'getLeaveDaysCountForTimeSheets', {
          ids: ids
        }).then(function (res) {
          setReportLeaveOn(!!(res && res.available));
          setReportLeave(res && res.counts || {});
        })["catch"](function () {
          setReportLeaveOn(false);
          setReportLeave({});
        });
      } else {
        setReportLeaveOn(false);
        setReportLeave({});
      }
    })["catch"](function () {
      return setReportRows([]);
    });
  }, []);
  (0, _react.useEffect)(function () {
    loadMine();
    if (isManager) loadReports();
  }, [loadMine, loadReports, isManager]);

  var reloadList = function reloadList() {
    if (tab === 'Reports') loadReports();else loadMine();
  };

  var backToList = function backToList() {
    setView('list');
    setCurrent(null);
    setGrid(null);
    setCal(null);
    setEdits({});
    reloadList();
  }; // ---- grid (Edit) --------------------------------------------------------


  var openGrid = function openGrid(ts) {
    var qt = adapters().tabQtsheet;
    if (!qt) return;
    qt.setCurrentTimeSheetId(ts.id);
    setCurrent(_objectSpread({}, ts, {
      readOnly: false
    }));
    setEdits({});
    setGrid(null);
    setLeaveDays([]);
    setView('grid');
    setGridLoading(true);
    callAction(qt, 'getAllData', {
      rowTable: 'Project',
      columnTable: 'QTDays',
      valueTable: 'EmployeeTimeEntry',
      currentId: ts.id,
      save: 0
    }).then(function (d) {
      setGrid({
        projects: d[0] || [],
        dates: d[1] || [],
        entries: d[2] || []
      });
      setGridLoading(false);
    })["catch"](function () {
      setGridLoading(false);

      _antd.message.error('Could not load the timesheet grid', 5);
    });
    callAction(qt, 'getLeaveDaysForTimeSheet', {
      id: ts.id
    }).then(function (d) {
      return setLeaveDays(Array.isArray(d) ? d : []);
    })["catch"](function () {
      return setLeaveDays([]);
    });
  }; // ---- calendar / entries (View) -----------------------------------------


  var openCalendar = function openCalendar(ts, isReport) {
    var a = isReport ? adapters().tabSubEmployeeTimeSheetAll : adapters().tabEmployeeTimeSheetAll;
    var entryAdapter = adapters().tabEmployeeTimeEntry;
    var sm = entryAdapter && entryAdapter.getSourceMapping ? JSON.stringify(entryAdapter.getSourceMapping()) : '';
    setCurrent(_objectSpread({}, ts, {
      readOnly: true,
      isReport: !!isReport
    }));
    setCal(null);
    setTsLogs([]);
    setLeaveReq({
      available: false,
      requests: []
    });
    setView('calendar');
    setCalLoading(true);
    callAction(a, 'getTimeEntries', {
      id: ts.id,
      sm: sm
    }).then(function (d) {
      setCal({
        entries: d[0] || [],
        employee: d[1] || {},
        timesheet: d[2] || {}
      });
      setCalLoading(false);
    })["catch"](function () {
      setCalLoading(false);

      _antd.message.error('Could not load timesheet entries', 5);
    });
    callAction(a, 'getTimeSheetLogs', {
      id: ts.id
    }).then(function (d) {
      return setTsLogs(Array.isArray(d) ? d : []);
    })["catch"](function () {
      return setTsLogs([]);
    }); // Leave the employee has in this period — hidden when the leave module is
    // not installed (available: false).

    callAction(a, 'getLeaveRequestsForTimeSheet', {
      id: ts.id
    }).then(function (d) {
      return setLeaveReq({
        available: !!(d && d.available),
        requests: d && Array.isArray(d.requests) ? d.requests : []
      });
    })["catch"](function () {
      return setLeaveReq({
        available: false,
        requests: []
      });
    });
  };

  var reloadCalendar = function reloadCalendar() {
    if (current) openCalendar(current, current.isReport);
  }; // ---- row / status actions ----------------------------------------------


  var rowAction = function rowAction(adapter, action, id, okMsg) {
    setBusyId(id);
    callAction(adapter, action, {
      id: id
    }, true).then(function () {
      _antd.message.success(okMsg);

      loadMine();
    })["catch"](function (e) {
      return _antd.message.error(e && e.message || "Could not ".concat(action), 5);
    })["finally"](function () {
      return setBusyId(null);
    });
  };

  var doBulkApprove = function doBulkApprove() {
    var a = adapters().tabSubEmployeeTimeSheetAll || adapters().tabEmployeeTimeSheetAll;
    if (!a || !selectedReportIds.length) return;
    setBulkApproving(true);
    callAction(a, 'bulkApproveTimeSheets', {
      ids: selectedReportIds
    }, true).then(function (res) {
      var n = res && res.approved || 0;

      _antd.message.success("".concat(n, " timesheet").concat(n === 1 ? '' : 's', " approved"));

      setSelectedReportIds([]);
      loadReports();
    })["catch"](function () {
      return _antd.message.error('Could not approve the selected timesheets', 5);
    })["finally"](function () {
      return setBulkApproving(false);
    });
  };

  var changeStatus = function changeStatus(id, status, after, note) {
    var a = adapters().tabSubEmployeeTimeSheetAll || adapters().tabEmployeeTimeSheetAll;
    setBusyId(id);
    return callAction(a, 'changeTimeSheetStatus', {
      id: id,
      status: status,
      note: note || ''
    }, true).then(function () {
      _antd.message.success("Timesheet ".concat(status.toLowerCase()));

      if (after) after();
    })["catch"](function () {
      return _antd.message.error('Could not change status', 5);
    })["finally"](function () {
      return setBusyId(null);
    });
  };

  var deleteEntry = function deleteEntry(entryId) {
    var e = adapters().tabEmployeeTimeEntry;
    if (!e || typeof e.cleanDelete !== 'function') return;
    e.cleanDelete(entryId, function (httpStatus, status) {
      if (httpStatus === 200 && status === 'SUCCESS') {
        _antd.message.success('Entry deleted');

        reloadCalendar();
      } else _antd.message.error('Could not delete entry', 5);
    });
  }; // ---- grid helpers -------------------------------------------------------


  var realProjects = (0, _react.useMemo)(function () {
    return grid ? grid.projects.filter(function (p) {
      return p.id !== -1 && p.id !== '-1';
    }) : [];
  }, [grid]);
  var cellValue = (0, _react.useCallback)(function (projectId, dateId) {
    var key = "".concat(dateId, "=").concat(projectId);
    if (key in edits) return edits[key];
    if (!grid) return '';
    var e = grid.entries.find(function (x) {
      return x.project === projectId && x.date === dateId;
    });
    return e && e.amount != null ? e.amount : '';
  }, [edits, grid]);
  var colTotal = (0, _react.useCallback)(function (dateId) {
    return realProjects.reduce(function (s, p) {
      var v = parseFloat(cellValue(p.id, dateId));
      return s + (Number.isNaN(v) ? 0 : v);
    }, 0);
  }, [realProjects, cellValue]);
  var rowTotal = (0, _react.useCallback)(function (projectId) {
    return grid ? grid.dates.reduce(function (s, d) {
      var v = parseFloat(cellValue(projectId, d.id));
      return s + (Number.isNaN(v) ? 0 : v);
    }, 0) : 0;
  }, [grid, cellValue]);
  var editable = current && !current.readOnly && current.status !== 'Approved';

  var setCell = function setCell(projectId, dateId, value) {
    return setEdits(function (p) {
      return _objectSpread({}, p, _defineProperty({}, "".concat(dateId, "=").concat(projectId), value == null ? '' : value));
    });
  }; // Map a leave day to a readable label + its per-day hour cap.


  var leaveDayInfo = function leaveDayInfo(ld) {
    var dt = new Date("".concat(String(ld.date).slice(0, 10), "T00:00:00"));
    var dateStr = Number.isNaN(dt.getTime()) ? ld.date : dt.toLocaleDateString(undefined, {
      weekday: 'short',
      month: 'short',
      day: 'numeric'
    });
    var kind = 'Full Day';

    if (ld.half) {
      var part = /morning/i.test(ld.type) ? 'Morning' : /after|evening/i.test(ld.type) ? 'Afternoon' : 'Half Day';
      kind = "Half Day \xB7 ".concat(part);
    }

    return {
      dateStr: dateStr,
      kind: kind,
      cap: ld.half ? 4 : 0
    };
  };

  var persist = function persist(action) {
    var qt = adapters().tabQtsheet;
    if (!qt || !current) return;
    var over = grid.dates.find(function (d) {
      return colTotal(d.id) > 24;
    });

    if (over) {
      _antd.message.error("Total hours for ".concat(over.name, " exceed 24."), 5);

      return;
    } // On save AND submit, block time logged on approved-leave days (full day:
    // none; half day: up to 4h). The backend enforces the same rule.


    if (leaveDays.length) {
      var violations = leaveDays.map(function (ld) {
        return {
          ld: ld,
          total: colTotal(ld.date)
        };
      }).filter(function (_ref) {
        var ld = _ref.ld,
            total = _ref.total;
        return total > (ld.half ? 4 : 0);
      });

      if (violations.length) {
        _antd.Modal.error({
          title: 'Time logged on leave days',
          content: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("p", null, "You cannot ".concat(action === 'updateAllData' ? 'submit' : 'save', " \u2014 these days have approved leave:")), /*#__PURE__*/_react["default"].createElement("ul", {
            style: {
              paddingLeft: 18,
              margin: 0
            }
          }, violations.map(function (_ref2) {
            var ld = _ref2.ld,
                total = _ref2.total;
            var info = leaveDayInfo(ld);
            return /*#__PURE__*/_react["default"].createElement("li", {
              key: ld.date,
              style: {
                marginBottom: 4
              }
            }, /*#__PURE__*/_react["default"].createElement("b", null, info.dateStr), " \u2014 ", info.kind, ": ", ld.half ? "max 4h allowed, you logged ".concat(total, "h") : "no time allowed, you logged ".concat(total, "h"));
          })))
        });

        return;
      }
    }

    var req = {
      rowTable: 'Project',
      columnTable: 'QTDays',
      valueTable: 'EmployeeTimeEntry',
      currentId: current.id
    };
    Object.keys(edits).forEach(function (key) {
      var _key$split = key.split('='),
          _key$split2 = _slicedToArray(_key$split, 2),
          dateId = _key$split2[0],
          projectId = _key$split2[1];

      req[key] = [dateId, projectId, "".concat(edits[key] === '' || edits[key] == null ? 0 : edits[key])];
    });
    setSaving(true);
    callAction(qt, action, req, true).then(function () {
      _antd.message.success(action === 'updateAllData' ? 'Timesheet submitted' : 'Timesheet saved');

      setEdits({});
      var newStatus = action === 'updateAllData' ? 'Submitted' : current.status;
      openGrid(_objectSpread({}, current, {
        status: newStatus
      }));
    })["catch"](function () {
      return _antd.message.error('Could not save the timesheet', 5);
    })["finally"](function () {
      return setSaving(false);
    });
  };

  var download = function download() {
    if (!grid) return;
    var header = ['Project'].concat(_toConsumableArray(grid.dates.map(function (d) {
      return d.name;
    })), ['Total']);
    var lines = [header.join(',')];
    realProjects.forEach(function (p) {
      return lines.push([JSON.stringify(p.name)].concat(_toConsumableArray(grid.dates.map(function (d) {
        return cellValue(p.id, d.id) || 0;
      })), [rowTotal(p.id)]).join(','));
    });
    lines.push(['Total'].concat(_toConsumableArray(grid.dates.map(function (d) {
      return colTotal(d.id);
    })), ['']).join(','));
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([lines.join('\r\n')], {
      type: 'text/csv;charset=utf-8'
    }));
    a.download = "timesheet_".concat(current.id, ".csv");
    a.click();
    URL.revokeObjectURL(a.href);
  };

  var statusTag = function statusTag(s) {
    return /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
      color: STATUS_COLORS[s] || 'default'
    }, s);
  }; // ---- list view ----------------------------------------------------------


  var renderList = function renderList() {
    var reports = tab === 'Reports';
    var all = reports ? reportRows : myRows;
    if (all === null) return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        textAlign: 'center',
        padding: 60
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
    var rows = reports ? all : tab === 'All' ? all : all.filter(function (r) {
      return r.status === tab;
    });
    if (reports && reportEmp) rows = rows.filter(function (r) {
      return r.employee === reportEmp;
    });
    if (reports) rows = rows.filter(function (r) {
      return reportStatuses.indexOf(r.status) !== -1;
    });
    var columns = [].concat(_toConsumableArray(reports ? [{
      title: 'Employee',
      dataIndex: 'employee',
      key: 'employee'
    }] : []), [{
      title: 'Period',
      key: 'period',
      render: function render(_, r) {
        return /*#__PURE__*/_react["default"].createElement(Text, {
          strong: true
        }, "".concat(fmtDate(r.date_start), " \u2013 ").concat(fmtDate(r.date_end)));
      }
    }, {
      title: 'Total Time',
      dataIndex: 'total_time',
      key: 'total_time',
      render: function render(t) {
        return /*#__PURE__*/_react["default"].createElement(_antd.Space, {
          size: 4
        }, /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null), t || '00:00');
      }
    }], _toConsumableArray(reports && reportLeaveOn ? [{
      title: 'Leave Time',
      key: 'leave_time',
      render: function render(_, r) {
        var d = reportLeave[r.id] || 0;
        return d > 0 ? /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
          color: "gold",
          style: {
            margin: 0
          }
        }, "".concat(d, " day").concat(d === 1 ? '' : 's')) : /*#__PURE__*/_react["default"].createElement(Text, {
          type: "secondary"
        }, "\u2014");
      }
    }] : []), [{
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: statusTag
    }, {
      title: 'Actions',
      key: 'actions',
      // Clicks on action buttons must not trigger the row's view action.
      render: function render(_, r) {
        return /*#__PURE__*/_react["default"].createElement(_antd.Space, {
          wrap: true,
          onClick: function onClick(e) {
            return e.stopPropagation();
          }
        }, !reports && /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
          title: "Edit hours grid"
        }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
          size: "small",
          icon: /*#__PURE__*/_react["default"].createElement(_icons.EditOutlined, null),
          onClick: function onClick() {
            return openGrid(r);
          }
        })), reports && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
          size: "small",
          icon: /*#__PURE__*/_react["default"].createElement(_icons.AuditOutlined, null),
          onClick: function onClick() {
            return setStatusModal({
              id: r.id,
              value: 'Approved'
            });
          }
        }, "Status"), !reports && /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
          title: "Create the previous week's timesheet"
        }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
          size: "small",
          icon: /*#__PURE__*/_react["default"].createElement(_icons.StepBackwardOutlined, null),
          loading: busyId === r.id,
          onClick: function onClick() {
            return rowAction(adapters().tabEmployeeTimeSheetAll, 'createPreviousTimesheet', r.id, 'Previous timesheet created');
          }
        }, "Previous Week")), /*#__PURE__*/_react["default"].createElement(_antd.Tooltip, {
          title: "Create the next week's timesheet"
        }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
          size: "small",
          icon: /*#__PURE__*/_react["default"].createElement(_icons.StepForwardOutlined, null),
          loading: busyId === r.id,
          onClick: function onClick() {
            return rowAction(adapters().tabEmployeeTimeSheetAll, 'createNextWeekTimesheet', r.id, 'Next timesheet created');
          }
        }, "Next Week"))));
      }
    }]);
    return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, reports && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center',
        flexWrap: 'wrap',
        gap: 8,
        marginBottom: 14
      }
    }, /*#__PURE__*/_react["default"].createElement(Text, {
      type: "secondary",
      style: {
        marginRight: 4
      }
    }, "Show status:"), /*#__PURE__*/_react["default"].createElement(_antd.Checkbox.Group, {
      value: reportStatuses,
      onChange: setReportStatuses,
      options: ['Pending', 'Submitted', 'Approved', 'Rejected'].map(function (s) {
        return {
          label: s,
          value: s
        };
      })
    }), selectedReportIds.length > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      type: "primary",
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
      loading: bulkApproving,
      style: {
        marginLeft: 'auto'
      },
      onClick: doBulkApprove
    }, "Approve ".concat(selectedReportIds.length, " selected"))), /*#__PURE__*/_react["default"].createElement(_antd.Table, _extends({
      rowKey: "id",
      size: "middle",
      columns: columns,
      dataSource: rows,
      pagination: {
        pageSize: 10,
        hideOnSinglePage: true
      },
      scroll: {
        x: 'max-content'
      },
      onRow: function onRow(r) {
        return {
          onClick: function onClick() {
            return openCalendar(r, reports);
          },
          style: {
            cursor: 'pointer'
          }
        };
      },
      locale: {
        emptyText: /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
          image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
          description: "No timesheets"
        })
      }
    }, reports ? {
      rowSelection: {
        selectedRowKeys: selectedReportIds,
        onChange: setSelectedReportIds,
        // Only Submitted timesheets are approvable in bulk.
        getCheckboxProps: function getCheckboxProps(r) {
          return {
            disabled: r.status !== 'Submitted'
          };
        }
      }
    } : {})));
  }; // ---- grid view ----------------------------------------------------------


  var renderGrid = function renderGrid() {
    if (gridLoading || !grid) return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        textAlign: 'center',
        padding: 60
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
    var columns = [{
      title: 'Project',
      dataIndex: 'name',
      key: 'name',
      fixed: 'left',
      width: 200,
      render: function render(n) {
        return /*#__PURE__*/_react["default"].createElement(Text, {
          strong: true
        }, n);
      }
    }].concat(_toConsumableArray(grid.dates.map(function (d) {
      return {
        title: d.name,
        key: d.id,
        align: 'center',
        width: 96,
        render: function render(_, p) {
          var ed = editable && d.editable !== 'No';
          var val = cellValue(p.id, d.id);
          if (!ed) return /*#__PURE__*/_react["default"].createElement("span", null, val === '' || val == null ? '–' : val);
          return /*#__PURE__*/_react["default"].createElement(_antd.InputNumber, {
            size: "small",
            min: 0,
            max: 24,
            step: 0.5,
            controls: false,
            value: val === '' ? null : Number(val),
            onChange: function onChange(v) {
              return setCell(p.id, d.id, v);
            },
            style: {
              width: 70
            }
          });
        }
      };
    })), [{
      title: 'Total',
      key: 'rowtotal',
      align: 'center',
      fixed: 'right',
      width: 80,
      render: function render(_, p) {
        return /*#__PURE__*/_react["default"].createElement(Text, {
          strong: true
        }, rowTotal(p.id).toFixed(2).replace(/\.00$/, ''));
      }
    }]);
    return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement(_antd.Table, {
      rowKey: "id",
      size: "small",
      bordered: true,
      columns: columns,
      dataSource: realProjects,
      pagination: false,
      scroll: {
        x: 'max-content'
      },
      locale: {
        emptyText: /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
          image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
          description: "No projects assigned"
        })
      },
      summary: function summary() {
        return /*#__PURE__*/_react["default"].createElement(_antd.Table.Summary, {
          fixed: true
        }, /*#__PURE__*/_react["default"].createElement(_antd.Table.Summary.Row, null, /*#__PURE__*/_react["default"].createElement(_antd.Table.Summary.Cell, {
          index: 0
        }, /*#__PURE__*/_react["default"].createElement(Text, {
          strong: true
        }, "Total")), grid.dates.map(function (d, i) {
          var t = colTotal(d.id);
          return /*#__PURE__*/_react["default"].createElement(_antd.Table.Summary.Cell, {
            key: d.id,
            index: i + 1,
            align: "center"
          }, /*#__PURE__*/_react["default"].createElement(Text, {
            strong: true,
            style: {
              color: t > 24 ? token.colorError : undefined
            }
          }, t.toFixed(2).replace(/\.00$/, '')));
        }), /*#__PURE__*/_react["default"].createElement(_antd.Table.Summary.Cell, {
          index: grid.dates.length + 1,
          align: "center"
        }, /*#__PURE__*/_react["default"].createElement(Text, {
          strong: true
        }, realProjects.reduce(function (s, p) {
          return s + rowTotal(p.id);
        }, 0).toFixed(2).replace(/\.00$/, '')))));
      }
    }), leaveDays.length > 0 && /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
      type: "warning",
      showIcon: true,
      icon: /*#__PURE__*/_react["default"].createElement(_icons.CoffeeOutlined, null),
      style: {
        marginTop: 16,
        borderRadius: 10
      },
      message: /*#__PURE__*/_react["default"].createElement(Text, {
        strong: true
      }, "You have approved leave this week"),
      description: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          display: 'flex',
          flexWrap: 'wrap',
          gap: 8,
          margin: '8px 0'
        }
      }, leaveDays.map(function (ld) {
        var info = leaveDayInfo(ld);
        return /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
          key: ld.date,
          color: ld.half ? 'gold' : 'volcano',
          icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
          style: {
            padding: '3px 12px',
            borderRadius: 14,
            fontSize: 13,
            margin: 0
          }
        }, /*#__PURE__*/_react["default"].createElement("b", null, info.dateStr), ' · ', info.kind);
      })), /*#__PURE__*/_react["default"].createElement(Text, {
        type: "secondary",
        style: {
          fontSize: 12.5
        }
      }, "No time can be logged on a full-day leave; up to 4 hours on a half-day leave."))
    }));
  }; // ---- calendar / entries view -------------------------------------------


  var fmtHM = function fmtHM(min) {
    return min > 0 ? "".concat(Math.floor(min / 60), "h").concat(min % 60 ? " ".concat(min % 60, "m") : '') : '0h';
  };

  var entryMin = function entryMin(e) {
    var a = parseDT(e.date_start);
    var b = parseDT(e.date_end);
    return a && b ? Math.max(0, Math.round((b - a) / 60000)) : 0;
  };

  var renderCalendar = function renderCalendar() {
    if (calLoading || !cal) return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        textAlign: 'center',
        padding: 60
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Spin, {
      size: "large"
    }));
    var start = parseDT(current.date_start);
    var todayKey = new Date().toISOString().slice(0, 10);
    var days = Array.from({
      length: 7
    }, function (_, i) {
      var d = new Date(start);
      d.setDate(start.getDate() + i);
      var key = d.toISOString().slice(0, 10);
      return {
        key: key,
        dayName: DAY_NAMES[d.getDay()],
        dayNum: d.getDate(),
        mon: d.toLocaleDateString(undefined, {
          month: 'short'
        }),
        isToday: key === todayKey,
        isWeekend: d.getDay() === 0 || d.getDay() === 6,
        entries: [],
        minutes: 0
      };
    });
    var totalMin = 0;
    cal.entries.forEach(function (e) {
      var k = String(e.date_start || '').slice(0, 10);
      var min = entryMin(e);
      var day = days.find(function (dd) {
        return dd.key === k;
      });

      if (day) {
        day.entries.push(e);
        day.minutes += min;
      }

      totalMin += min;
    });
    var daysWorked = days.filter(function (d) {
      return d.minutes > 0;
    }).length;
    var entryCount = cal.entries.length;
    var canDelete = !current.isReport && current.status !== 'Approved';

    var Stat = function Stat(_ref3) {
      var icon = _ref3.icon,
          label = _ref3.label,
          value = _ref3.value,
          accent = _ref3.accent;
      return /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          flex: '1 1 130px',
          minWidth: 130,
          background: token.colorFillQuaternary,
          borderRadius: 12,
          padding: '12px 16px'
        }
      }, /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          fontSize: 12,
          color: token.colorTextSecondary,
          marginBottom: 3
        }
      }, label), /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          fontSize: 22,
          fontWeight: 700,
          lineHeight: 1.1,
          color: accent || token.colorText,
          display: 'flex',
          alignItems: 'center',
          gap: 7
        }
      }, icon, value));
    };

    return /*#__PURE__*/_react["default"].createElement(_react["default"].Fragment, null, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        gap: 12,
        flexWrap: 'wrap',
        marginBottom: 18
      }
    }, /*#__PURE__*/_react["default"].createElement(Stat, {
      icon: /*#__PURE__*/_react["default"].createElement(_icons.ClockCircleOutlined, null),
      label: "Total logged",
      value: fmtHM(totalMin),
      accent: token.colorPrimary
    }), /*#__PURE__*/_react["default"].createElement(Stat, {
      label: "Entries",
      value: entryCount
    }), /*#__PURE__*/_react["default"].createElement(Stat, {
      label: "Days worked",
      value: "".concat(daysWorked, " / 7")
    }), /*#__PURE__*/_react["default"].createElement(Stat, {
      label: "Avg / working day",
      value: daysWorked ? fmtHM(Math.round(totalMin / daysWorked)) : '0h'
    })), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        overflowX: 'auto'
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        display: 'flex',
        minWidth: 760,
        borderRadius: 12,
        overflow: 'hidden',
        border: "1px solid ".concat(token.colorBorderSecondary)
      }
    }, days.map(function (d, i) {
      return /*#__PURE__*/_react["default"].createElement("div", {
        key: d.key,
        style: {
          flex: 1,
          minWidth: 106,
          borderRight: i < 6 ? "1px solid ".concat(token.colorBorderSecondary) : 'none',
          background: d.isWeekend ? token.colorFillQuaternary : token.colorBgContainer
        }
      }, /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          textAlign: 'center',
          padding: '8px 4px',
          background: d.isToday ? token.colorPrimary : token.colorFillSecondary,
          color: d.isToday ? '#fff' : token.colorText,
          borderBottom: "1px solid ".concat(token.colorBorderSecondary)
        }
      }, /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          fontSize: 10.5,
          textTransform: 'uppercase',
          letterSpacing: 0.6,
          opacity: 0.85
        }
      }, d.dayName), /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          fontSize: 17,
          fontWeight: 700,
          lineHeight: 1.15
        }
      }, d.dayNum), /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          fontSize: 10.5,
          opacity: 0.8
        }
      }, d.mon)), /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          textAlign: 'center',
          padding: '4px 0',
          fontSize: 12,
          fontWeight: 700,
          color: d.minutes > 0 ? token.colorPrimary : token.colorTextQuaternary,
          borderBottom: "1px dashed ".concat(token.colorBorderSecondary)
        }
      }, d.minutes > 0 ? fmtHM(d.minutes) : '—'), /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          minHeight: 78,
          padding: '6px',
          display: 'flex',
          flexDirection: 'column',
          gap: 5
        }
      }, d.entries.map(function (e) {
        return /*#__PURE__*/_react["default"].createElement("div", {
          key: e.id,
          style: {
            background: token.colorPrimaryBg,
            border: "1px solid ".concat(token.colorPrimaryBorder),
            borderRadius: 7,
            padding: '4px 7px',
            fontSize: 11.5
          }
        }, /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            fontWeight: 600,
            display: 'flex',
            justifyContent: 'space-between',
            gap: 4
          }
        }, /*#__PURE__*/_react["default"].createElement("span", null, fmtTime(parseDT(e.date_start))), /*#__PURE__*/_react["default"].createElement("span", {
          style: {
            color: token.colorPrimary
          }
        }, fmtHM(entryMin(e)))), /*#__PURE__*/_react["default"].createElement("div", {
          style: {
            color: token.colorTextSecondary,
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            textOverflow: 'ellipsis'
          }
        }, e.project && e.project !== 'null' ? e.project : 'No project'));
      }), d.entries.length === 0 && /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          textAlign: 'center',
          color: token.colorTextQuaternary,
          fontSize: 11,
          paddingTop: 6
        }
      }, "\u2014")));
    }))), leaveReq.available && leaveReq.requests.length > 0 && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        marginTop: 22
      }
    }, /*#__PURE__*/_react["default"].createElement(Text, {
      strong: true,
      style: {
        display: 'block',
        marginBottom: 8
      }
    }, /*#__PURE__*/_react["default"].createElement(_icons.CoffeeOutlined, {
      style: {
        marginRight: 6
      }
    }), "Leave in this period (".concat(leaveReq.requests.length, ")")), /*#__PURE__*/_react["default"].createElement(_antd.Table, {
      rowKey: "id",
      size: "small",
      pagination: false,
      scroll: {
        x: 'max-content'
      },
      dataSource: leaveReq.requests,
      columns: [{
        title: 'Leave Type',
        key: 'type',
        render: function render(_, r) {
          return /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
            style: {
              fontWeight: 600
            }
          }, r.leave_type || 'Leave'), r.details && String(r.details).trim() ? /*#__PURE__*/_react["default"].createElement(Text, {
            type: "secondary",
            style: {
              fontSize: 12
            }
          }, String(r.details).trim()) : null);
        }
      }, {
        title: 'Requested',
        key: 'range',
        render: function render(_, r) {
          return r.date_start === r.date_end ? fmtShortDate(r.date_start) : "".concat(fmtShortDate(r.date_start), " \u2013 ").concat(fmtShortDate(r.date_end));
        }
      }, {
        title: 'Days in this period',
        key: 'days',
        render: function render(_, r) {
          var days = Array.isArray(r.days) ? r.days : [];
          if (!days.length) return /*#__PURE__*/_react["default"].createElement(Text, {
            type: "secondary"
          }, "\u2014");
          return /*#__PURE__*/_react["default"].createElement("div", {
            style: {
              display: 'flex',
              flexWrap: 'wrap',
              gap: 6
            }
          }, days.map(function (d) {
            return /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
              key: "".concat(r.id, "-").concat(d.date),
              color: isFullDayLeave(d.type) ? 'volcano' : 'gold',
              icon: /*#__PURE__*/_react["default"].createElement(_icons.CalendarOutlined, null),
              style: {
                margin: 0,
                borderRadius: 12,
                padding: '1px 10px'
              }
            }, /*#__PURE__*/_react["default"].createElement("b", null, fmtShortDate(d.date)), ' · ', d.type);
          }));
        }
      }, {
        title: 'Total',
        key: 'total',
        align: 'center',
        render: function render(_, r) {
          var t = Number(r.days_total) || 0;
          return t ? "".concat(t, " day").concat(t === 1 ? '' : 's') : /*#__PURE__*/_react["default"].createElement(Text, {
            type: "secondary"
          }, "\u2014");
        }
      }, {
        title: 'Status',
        key: 'status',
        render: function render(_, r) {
          return /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
            color: LEAVE_STATUS_COLORS[r.status] || 'default',
            style: {
              margin: 0
            }
          }, r.status);
        }
      }]
    })), /*#__PURE__*/_react["default"].createElement(Text, {
      strong: true,
      style: {
        display: 'block',
        margin: '20px 0 8px'
      }
    }, "All entries (".concat(entryCount, ")")), /*#__PURE__*/_react["default"].createElement(_antd.Table, {
      rowKey: "id",
      size: "small",
      dataSource: cal.entries,
      pagination: false,
      locale: {
        emptyText: /*#__PURE__*/_react["default"].createElement(_antd.Empty, {
          image: _antd.Empty.PRESENTED_IMAGE_SIMPLE,
          description: "No time entries"
        })
      },
      columns: [{
        title: 'Start',
        key: 'start',
        render: function render(_, e) {
          var d = parseDT(e.date_start);
          return d ? "".concat(d.toLocaleDateString(), " ").concat(fmtTime(d)) : '-';
        }
      }, {
        title: 'End',
        key: 'end',
        render: function render(_, e) {
          var d = parseDT(e.date_end);
          return d ? "".concat(d.toLocaleDateString(), " ").concat(fmtTime(d)) : '-';
        }
      }, {
        title: 'Duration',
        key: 'dur',
        render: function render(_, e) {
          return /*#__PURE__*/_react["default"].createElement(_antd.Tag, {
            color: "blue",
            style: {
              margin: 0
            }
          }, fmtHM(entryMin(e)));
        }
      }, {
        title: 'Project',
        key: 'project',
        render: function render(_, e) {
          return e.project && e.project !== 'null' ? e.project : /*#__PURE__*/_react["default"].createElement(Text, {
            type: "secondary"
          }, "No project");
        }
      }, {
        title: 'Details',
        dataIndex: 'details',
        key: 'details',
        ellipsis: true,
        render: function render(t) {
          return t || /*#__PURE__*/_react["default"].createElement(Text, {
            type: "secondary"
          }, "\u2014");
        }
      }].concat(_toConsumableArray(canDelete ? [{
        title: '',
        key: 'del',
        width: 50,
        render: function render(_, e) {
          return /*#__PURE__*/_react["default"].createElement(_antd.Popconfirm, {
            title: "Delete this entry?",
            onConfirm: function onConfirm() {
              return deleteEntry(e.id);
            },
            okText: "Delete",
            okButtonProps: {
              danger: true
            }
          }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
            size: "small",
            type: "text",
            danger: true,
            icon: /*#__PURE__*/_react["default"].createElement(_icons.DeleteOutlined, null)
          }));
        }
      }] : []))
    }), tsLogs.length > 0 && /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        marginTop: 24
      }
    }, /*#__PURE__*/_react["default"].createElement(Text, {
      strong: true,
      style: {
        display: 'block',
        marginBottom: 12
      }
    }, /*#__PURE__*/_react["default"].createElement(_icons.HistoryOutlined, {
      style: {
        marginRight: 6
      }
    }), "Approval Log"), /*#__PURE__*/_react["default"].createElement(_antd.Timeline, {
      items: tsLogs.map(function (l, i) {
        return {
          color: l.status_to === 'Approved' ? 'green' : l.status_to === 'Rejected' ? 'red' : 'blue',
          key: i,
          children: /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
            style: {
              fontWeight: 600
            }
          }, "".concat(l.status_from || '', " \u2192 ").concat(l.status_to || '')), /*#__PURE__*/_react["default"].createElement("div", {
            style: {
              color: token.colorTextSecondary,
              fontSize: 12
            }
          }, l.time), l.note && l.note.trim() ? /*#__PURE__*/_react["default"].createElement("div", {
            style: {
              marginTop: 2
            }
          }, l.note.trim()) : null)
        };
      })
    })));
  }; // ---- top-level ----------------------------------------------------------


  if (view === 'grid' && current) {
    var canSubmit = editable && current.status !== 'Submitted';
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 8
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      style: {
        borderRadius: 14
      },
      title: /*#__PURE__*/_react["default"].createElement(_antd.Space, {
        wrap: true
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: /*#__PURE__*/_react["default"].createElement(_icons.ArrowLeftOutlined, null),
        onClick: backToList
      }, "Back"), /*#__PURE__*/_react["default"].createElement(Title, {
        level: 5,
        style: {
          margin: 0
        }
      }, "".concat(fmtDate(current.date_start), " \u2013 ").concat(fmtDate(current.date_end))), statusTag(current.status)),
      extra: /*#__PURE__*/_react["default"].createElement(_antd.Space, {
        wrap: true
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: /*#__PURE__*/_react["default"].createElement(_icons.DownloadOutlined, null),
        onClick: download
      }, "Download"), editable && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: /*#__PURE__*/_react["default"].createElement(_icons.SaveOutlined, null),
        loading: saving,
        onClick: function onClick() {
          return persist('updateData');
        }
      }, "Save"), canSubmit && /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        type: "primary",
        icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
        loading: saving,
        onClick: function onClick() {
          return persist('updateAllData');
        }
      }, "Submit"))
    }, renderGrid()));
  }

  if (view === 'calendar' && current) {
    var emp = cal && cal.employee || {};
    var ts = cal && cal.timesheet || {};
    var isReport = current.isReport;
    return /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        padding: 8
      }
    }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
      style: {
        borderRadius: 14
      },
      styles: {
        header: {
          paddingTop: 12,
          paddingBottom: 12
        }
      },
      title: /*#__PURE__*/_react["default"].createElement(_antd.Space, {
        wrap: true,
        size: "middle"
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        icon: /*#__PURE__*/_react["default"].createElement(_icons.ArrowLeftOutlined, null),
        onClick: backToList
      }, "Back"), /*#__PURE__*/_react["default"].createElement(_antd.Avatar, {
        size: 40,
        src: emp.image,
        icon: /*#__PURE__*/_react["default"].createElement(_icons.UserOutlined, null)
      }), /*#__PURE__*/_react["default"].createElement("div", null, /*#__PURE__*/_react["default"].createElement("div", {
        style: {
          display: 'flex',
          alignItems: 'center',
          gap: 8
        }
      }, /*#__PURE__*/_react["default"].createElement(Title, {
        level: 5,
        style: {
          margin: 0
        }
      }, emp.name || 'My Timesheet'), statusTag(ts.status || current.status)), /*#__PURE__*/_react["default"].createElement(Text, {
        type: "secondary",
        style: {
          fontSize: 12.5,
          fontWeight: 400
        }
      }, "".concat(fmtDate(current.date_start), " \u2013 ").concat(fmtDate(current.date_end))))),
      extra: isReport ? /*#__PURE__*/_react["default"].createElement(_antd.Space, {
        wrap: true
      }, /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        type: "primary",
        icon: /*#__PURE__*/_react["default"].createElement(_icons.CheckCircleOutlined, null),
        loading: busyId === current.id,
        onClick: function onClick() {
          return changeStatus(current.id, 'Approved', backToList);
        }
      }, "Approve"), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
        danger: true,
        icon: /*#__PURE__*/_react["default"].createElement(_icons.CloseCircleOutlined, null),
        loading: busyId === current.id,
        onClick: function onClick() {
          return setRejectModal({
            id: current.id,
            note: ''
          });
        }
      }, "Reject")) : null
    }, renderCalendar()), /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
      title: "Reject Timesheet",
      open: !!rejectModal,
      okText: "Reject",
      okButtonProps: {
        danger: true
      },
      confirmLoading: busyId === (rejectModal && rejectModal.id),
      onOk: function onOk() {
        var rm = rejectModal;
        setRejectModal(null);
        changeStatus(rm.id, 'Rejected', backToList, rm.note);
      },
      onCancel: function onCancel() {
        return setRejectModal(null);
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        marginBottom: 6,
        color: token.colorTextSecondary
      }
    }, "Note (optional)"), /*#__PURE__*/_react["default"].createElement(_antd.Input.TextArea, {
      rows: 3,
      value: rejectModal ? rejectModal.note : '',
      onChange: function onChange(e) {
        return setRejectModal(function (m) {
          return _objectSpread({}, m, {
            note: e.target.value
          });
        });
      },
      placeholder: "Add a note explaining why this timesheet is rejected\u2026"
    })));
  }

  var segOptions = [{
    label: 'All My Timesheets',
    value: 'All'
  }, {
    label: 'Approved',
    value: 'Approved'
  }, {
    label: 'Pending',
    value: 'Pending'
  }, {
    label: 'Rejected',
    value: 'Rejected'
  }].concat(_toConsumableArray(isManager ? [{
    label: 'Direct Reports - Time Sheets',
    value: 'Reports'
  }] : []));
  var reportEmployees = Array.from(new Set((reportRows || []).map(function (r) {
    return r.employee;
  }).filter(Boolean))).sort();
  return /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      padding: 8
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Card, {
    style: {
      borderRadius: 14
    },
    styles: {
      body: {
        paddingTop: 16
      }
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Space, {
    style: {
      width: '100%',
      justifyContent: 'space-between',
      marginBottom: 16
    },
    wrap: true
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      overflowX: 'auto'
    }
  }, /*#__PURE__*/_react["default"].createElement(_antd.Segmented, {
    options: segOptions,
    value: tab,
    onChange: function onChange(t) {
      setSelectedReportIds([]);
      setTab(t);
    }
  })), /*#__PURE__*/_react["default"].createElement(_antd.Space, {
    wrap: true
  }, tab === 'Reports' && /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    allowClear: true,
    showSearch: true,
    placeholder: "All employees",
    style: {
      minWidth: 200
    },
    value: reportEmp || undefined,
    onChange: function onChange(v) {
      return setReportEmp(v || null);
    },
    options: reportEmployees.map(function (e) {
      return {
        value: e,
        label: e
      };
    })
  }), /*#__PURE__*/_react["default"].createElement(_antd.Button, {
    icon: /*#__PURE__*/_react["default"].createElement(_icons.ReloadOutlined, null),
    onClick: reloadList
  }, "Refresh"))), renderList()), /*#__PURE__*/_react["default"].createElement(_antd.Modal, {
    title: "Change Timesheet Status",
    open: !!statusModal,
    onCancel: function onCancel() {
      return setStatusModal(null);
    },
    onOk: function onOk() {
      return changeStatus(statusModal.id, statusModal.value, function () {
        setStatusModal(null);
        loadReports();
      }, statusModal && statusModal.note);
    },
    confirmLoading: busyId === (statusModal && statusModal.id),
    okText: "Change Status"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 8
    }
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary"
  }, "Timesheet Status")), /*#__PURE__*/_react["default"].createElement(_antd.Select, {
    style: {
      width: '100%'
    },
    value: statusModal && statusModal.value,
    onChange: function onChange(v) {
      return setStatusModal(function (m) {
        return _objectSpread({}, m, {
          value: v
        });
      });
    },
    options: ['Approved', 'Pending', 'Rejected', 'Submitted'].map(function (s) {
      return {
        value: s,
        label: s
      };
    })
  }), statusModal && statusModal.value === 'Rejected' && /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginTop: 14
    }
  }, /*#__PURE__*/_react["default"].createElement("div", {
    style: {
      marginBottom: 6
    }
  }, /*#__PURE__*/_react["default"].createElement(Text, {
    type: "secondary"
  }, "Note (optional)")), /*#__PURE__*/_react["default"].createElement(_antd.Input.TextArea, {
    rows: 3,
    value: statusModal.note || '',
    onChange: function onChange(e) {
      return setStatusModal(function (m) {
        return _objectSpread({}, m, {
          note: e.target.value
        });
      });
    },
    placeholder: "Add a note explaining why this timesheet is rejected\u2026"
  }))));
}

},{"@ant-design/icons":"@ant-design/icons","antd":"antd","react":"react"}],24:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = UpdateAvailableBanner;

var _react = _interopRequireDefault(require("react"));

var _antd = require("antd");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var DISMISS_KEY = 'icehrm-update-banner-dismissed';
/**
 * "A newer IceHRM is available" banner.
 *
 * The payload is built server-side by Classes\UpdateAvailability and is null unless the
 * signed-in user is an administrator AND the marketplace snapshot advertises a version
 * newer than the installed one — so there is no version comparison, and no notion of
 * who may see this, in the browser.
 *
 * Dismissal is per browser session and keyed on the version, so it comes back when a
 * newer release appears and after the next sign-in, but does not nag on every dashboard
 * visit in between.
 *
 * The Update link is single-use in spirit: it carries a signature that expires two hours
 * after the page was rendered. Opening it in a new tab keeps the admin's IceHRM session
 * intact, which matters because the updater deliberately runs its own separate session.
 */

function UpdateAvailableBanner(_ref) {
  var updateAvailable = _ref.updateAvailable;
  var version = updateAvailable ? updateAvailable.latestVersion : null;

  var _React$useState = _react["default"].useState(function () {
    if (!version) return false;

    try {
      return window.sessionStorage.getItem(DISMISS_KEY) === version;
    } catch (e) {
      return false;
    }
  }),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      dismissed = _React$useState2[0],
      setDismissed = _React$useState2[1];

  if (!updateAvailable || dismissed) return null;
  var currentVersion = updateAvailable.currentVersion,
      latestVersion = updateAvailable.latestVersion,
      changelogUrl = updateAvailable.changelogUrl,
      updaterUrl = updateAvailable.updaterUrl;

  var dismiss = function dismiss() {
    try {
      window.sessionStorage.setItem(DISMISS_KEY, latestVersion);
    } catch (e) {// Private browsing or a full quota: dismissing for this render is enough.
    }

    setDismissed(true);
  };

  return /*#__PURE__*/_react["default"].createElement(_antd.Alert, {
    type: "info",
    banner: true,
    showIcon: true,
    closable: true,
    onClose: dismiss,
    style: {
      padding: '18px 28px',
      alignItems: 'center',
      marginBottom: 16
    },
    message: /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        lineHeight: 1.35
      }
    }, /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 17,
        fontWeight: 700,
        marginBottom: 3
      }
    }, "IceHRM ", latestVersion, " is available"), /*#__PURE__*/_react["default"].createElement("div", {
      style: {
        fontSize: 14,
        opacity: 0.9
      }
    }, "You are running ", currentVersion, ". Updating replaces the program files; your settings, uploads and data are kept, and the previous version is backed up so the update can be undone.")),
    action: /*#__PURE__*/_react["default"].createElement(_antd.Space, null, changelogUrl ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "large",
      href: changelogUrl,
      target: "_blank",
      rel: "noopener noreferrer"
    }, "What's New") : null, updaterUrl ? /*#__PURE__*/_react["default"].createElement(_antd.Button, {
      size: "large",
      type: "primary",
      href: updaterUrl,
      target: "_blank",
      rel: "noopener noreferrer",
      style: {
        fontWeight: 600
      }
    }, "Update") : null)
  });
}

},{"antd":"antd","react":"react"}],25:[function(require,module,exports){
"use strict";

var _react = _interopRequireDefault(require("react"));

var _reactDom = _interopRequireDefault(require("react-dom"));

var _antd = require("antd");

var _AppShell = _interopRequireDefault(require("./AppShell"));

var _theme = require("./theme");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * App shell entry (SPA migration Phase 1).
 * Reads bootstrap config injected by core/spa-shell.php, fetches /appshell/bootstrap
 * with the JWT, and renders the persistent shell.
 */
function readConfig() {
  var el = document.getElementById('app-shell-config');
  if (!el) return {};

  try {
    return JSON.parse(el.textContent || '{}');
  } catch (e) {
    return {};
  }
}

var COLOR_MODE_KEY = 'shell-color-mode';

function readColorMode() {
  try {
    var saved = localStorage.getItem(COLOR_MODE_KEY);
    if (saved === 'dark' || saved === 'light') return saved;
  } catch (e) {}
  /* ignore */
  // Fall back to the OS preference the first time.


  try {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
  } catch (e) {
    /* ignore */
  }

  return 'light';
} // Keep the document background (visible during loads / outside React) in sync
// with the active mode, so there is no light flash in dark mode.


function applyBodyBackground(mode) {
  var bg = mode === 'dark' ? _theme.MUI_DARK.bg : _theme.MUI.bg;
  var fg = mode === 'dark' ? _theme.MUI_DARK.text : _theme.MUI.text;

  try {
    document.documentElement.style.background = bg;
    document.body.style.background = bg;
    document.body.style.color = fg;
    document.body.setAttribute('data-color-mode', mode); // Exposed so legacy adapter modals (rendered in their own React roots,
    // outside this ConfigProvider) can theme themselves to match — see
    // ReactModalAdapterBase.shellThemeWrap().

    window.__shellColorMode = mode;
  } catch (e) {
    /* ignore */
  }
} // A tiny stateful wrapper so the in-app toggle can re-theme the whole shell.


function ThemedShell(_ref) {
  var bootstrap = _ref.bootstrap,
      config = _ref.config;

  var _React$useState = _react["default"].useState(readColorMode),
      _React$useState2 = _slicedToArray(_React$useState, 2),
      mode = _React$useState2[0],
      setMode = _React$useState2[1];

  _react["default"].useEffect(function () {
    applyBodyBackground(mode);
  }, [mode]);

  var toggleColorMode = _react["default"].useCallback(function () {
    setMode(function (prev) {
      var next = prev === 'dark' ? 'light' : 'dark';

      try {
        localStorage.setItem(COLOR_MODE_KEY, next);
      } catch (e) {
        /* ignore */
      }

      return next;
    });
  }, []);

  return _react["default"].createElement(_antd.ConfigProvider, {
    theme: (0, _theme.buildTheme)(mode)
  }, _react["default"].createElement(_AppShell["default"], {
    bootstrap: bootstrap,
    config: config,
    colorMode: mode,
    onToggleColorMode: toggleColorMode
  }));
}

function renderRaw(children) {
  _reactDom["default"].render(_react["default"].createElement(_antd.ConfigProvider, {
    theme: (0, _theme.buildTheme)(readColorMode())
  }, children), document.getElementById('app-shell-root'));
} // NOTE: intentionally using Promise chains (not async/await) so the bundle does
// not depend on regeneratorRuntime, which is not provided by the vendor bundles.


function boot() {
  applyBodyBackground(readColorMode());
  var config = readConfig();
  fetch("".concat(config.restApiBase, "appshell/bootstrap"), {
    headers: {
      Authorization: "Bearer ".concat(config.token)
    },
    credentials: 'same-origin'
  }).then(function (res) {
    if (!res.ok) {
      throw new Error("bootstrap failed: ".concat(res.status));
    }

    return res.json();
  }).then(function (bootstrap) {
    _reactDom["default"].render(_react["default"].createElement(ThemedShell, {
      bootstrap: bootstrap,
      config: config
    }), document.getElementById('app-shell-root'));
  })["catch"](function (err) {
    // eslint-disable-next-line no-console
    console.error('[app-shell] boot failed', err);
    renderRaw(_react["default"].createElement('div', {
      style: {
        padding: 24
      }
    }, "Failed to load the app shell: ".concat(err.message)));

    try {
      _antd.message.error('Failed to load the app shell');
    } catch (e) {
      /* noop */
    }
  });
}

boot();

},{"./AppShell":2,"./theme":26,"antd":"antd","react":"react","react-dom":"react-dom"}],26:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.buildTheme = buildTheme;
exports.MUI_THEME = exports.MUI_SHADOW_HOVER = exports.MUI_SHADOW = exports.SIDEBAR_BG = exports.MUI_DARK = exports.MUI = void 0;

var _antd = require("antd");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var MUI = {
  primary: '#1976d2',
  primaryDark: '#1565c0',
  success: '#2e7d32',
  warning: '#ed6c02',
  error: '#d32f2f',
  info: '#0288d1',
  bg: '#f4f6f8',
  paper: '#ffffff',
  text: 'rgba(0, 0, 0, 0.87)',
  textSecondary: 'rgba(0, 0, 0, 0.6)',
  divider: 'rgba(0, 0, 0, 0.12)'
}; // Dark-mode surface palette (Material dark: near-black layout, raised papers).

exports.MUI = MUI;
var MUI_DARK = {
  bg: '#0f141b',
  // app/layout background (behind cards)
  paper: '#1a212b',
  // card / container surface
  elevated: '#222b36',
  // popovers, dropdowns, modals
  text: 'rgba(255, 255, 255, 0.92)',
  textSecondary: 'rgba(255, 255, 255, 0.78)',
  border: '#2a3441'
}; // The sidebar uses a fixed dark navy in BOTH modes (it reads as intentional
// chrome rather than following the body) — exported so the shell can reuse it.

exports.MUI_DARK = MUI_DARK;
var SIDEBAR_BG = '#1a2233'; // Material elevation-1 style card shadow.

exports.SIDEBAR_BG = SIDEBAR_BG;
var MUI_SHADOW = '0 2px 1px -1px rgba(0,0,0,0.08), 0 1px 3px 0 rgba(0,0,0,0.12), 0 1px 1px 0 rgba(0,0,0,0.06)'; // Slightly stronger (hover / elevation-3).

exports.MUI_SHADOW = MUI_SHADOW;
var MUI_SHADOW_HOVER = '0 3px 5px -1px rgba(0,0,0,0.12), 0 5px 8px 0 rgba(0,0,0,0.08), 0 1px 14px 0 rgba(0,0,0,0.06)';
exports.MUI_SHADOW_HOVER = MUI_SHADOW_HOVER;
var FONT = '"Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif'; // Build the antd ConfigProvider theme for a colour mode ('light' | 'dark').

function buildTheme(mode) {
  var dark = mode === 'dark';
  return {
    algorithm: dark ? _antd.theme.darkAlgorithm : _antd.theme.defaultAlgorithm,
    token: _objectSpread({
      colorPrimary: MUI.primary,
      colorInfo: MUI.primary,
      colorSuccess: MUI.success,
      colorWarning: MUI.warning,
      colorError: MUI.error,
      colorLink: MUI.primary,
      fontFamily: FONT,
      fontSize: 14,
      borderRadius: 8,
      wireframe: false
    }, dark ? {
      colorBgLayout: MUI_DARK.bg,
      colorBgContainer: MUI_DARK.paper,
      colorBgElevated: MUI_DARK.elevated,
      colorText: MUI_DARK.text,
      colorTextHeading: MUI_DARK.text,
      colorTextSecondary: MUI_DARK.textSecondary,
      colorBorderSecondary: MUI_DARK.border
    } : {
      colorBgLayout: MUI.bg,
      colorText: MUI.text,
      colorTextHeading: MUI.text,
      colorTextSecondary: MUI.textSecondary,
      colorBorderSecondary: '#eceff1'
    }),
    components: {
      Card: {
        borderRadiusLG: 12,
        paddingLG: 20
      },
      Button: {
        fontWeight: 500,
        primaryShadow: 'none',
        defaultShadow: 'none',
        controlHeight: 36
      },
      Table: dark ? {
        headerBg: '#222b36',
        headerSplitColor: 'transparent',
        rowHoverBg: '#222b36',
        cellPaddingBlock: 14
      } : {
        headerBg: '#f7f9fb',
        headerColor: MUI.textSecondary,
        headerSplitColor: 'transparent',
        borderColor: '#eceff1',
        rowHoverBg: '#f3f6f9',
        cellPaddingBlock: 14
      },
      Tabs: {
        itemSelectedColor: MUI.primary,
        inkBarColor: MUI.primary,
        titleFontSize: 14,
        horizontalItemGutter: 28
      },
      Layout: {
        headerBg: dark ? MUI_DARK.paper : '#ffffff',
        bodyBg: dark ? MUI_DARK.bg : MUI.bg,
        headerHeight: 64
      },
      Input: {
        controlHeight: 36
      },
      Select: {
        controlHeight: 36
      },
      // The sidebar Menu stays dark-themed in both modes.
      Menu: {
        darkItemBg: SIDEBAR_BG,
        darkSubMenuItemBg: '#161d2b',
        darkItemSelectedBg: MUI.primary,
        darkItemHoverBg: 'rgba(255,255,255,0.08)',
        itemBorderRadius: 8
      }
    }
  };
} // Backwards-compatible default (light) theme object.


var MUI_THEME = buildTheme('light');
exports.MUI_THEME = MUI_THEME;

},{"antd":"antd"}]},{},[25])

//# sourceMappingURL=app-shell.js.map
