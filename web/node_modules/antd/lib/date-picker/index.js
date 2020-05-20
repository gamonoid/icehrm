"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _moment = _interopRequireDefault(require("rc-picker/lib/generate/moment"));

var _generatePicker = _interopRequireDefault(require("./generatePicker"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var DatePicker = (0, _generatePicker["default"])(_moment["default"]);
var _default = DatePicker;
exports["default"] = _default;