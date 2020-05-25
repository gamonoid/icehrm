"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _moment = _interopRequireDefault(require("rc-picker/lib/generate/moment"));

var _generateCalendar = _interopRequireDefault(require("./generateCalendar"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var Calendar = (0, _generateCalendar["default"])(_moment["default"]);
var _default = Calendar;
exports["default"] = _default;