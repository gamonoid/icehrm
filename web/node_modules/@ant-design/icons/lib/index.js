"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
var _exportNames = {
  createFromIconfontCN: true
};
Object.defineProperty(exports, "createFromIconfontCN", {
  enumerable: true,
  get: function get() {
    return _IconFont.default;
  }
});
Object.defineProperty(exports, "default", {
  enumerable: true,
  get: function get() {
    return _Icon.default;
  }
});

var _icons = require("./icons");

Object.keys(_icons).forEach(function (key) {
  if (key === "default" || key === "__esModule") return;
  if (Object.prototype.hasOwnProperty.call(_exportNames, key)) return;
  Object.defineProperty(exports, key, {
    enumerable: true,
    get: function get() {
      return _icons[key];
    }
  });
});

var _twoTonePrimaryColor = require("./components/twoTonePrimaryColor");

Object.keys(_twoTonePrimaryColor).forEach(function (key) {
  if (key === "default" || key === "__esModule") return;
  if (Object.prototype.hasOwnProperty.call(_exportNames, key)) return;
  Object.defineProperty(exports, key, {
    enumerable: true,
    get: function get() {
      return _twoTonePrimaryColor[key];
    }
  });
});

var _IconFont = _interopRequireDefault(require("./components/IconFont"));

var _Icon = _interopRequireDefault(require("./components/Icon"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }