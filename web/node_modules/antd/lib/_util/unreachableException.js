"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var UnreachableException = function UnreachableException(value) {
  _classCallCheck(this, UnreachableException);

  return new Error("unreachable case: ".concat(JSON.stringify(value)));
};

exports["default"] = UnreachableException;