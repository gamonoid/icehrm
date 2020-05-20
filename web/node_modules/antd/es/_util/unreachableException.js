function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var UnreachableException = function UnreachableException(value) {
  _classCallCheck(this, UnreachableException);

  return new Error("unreachable case: ".concat(JSON.stringify(value)));
};

export { UnreachableException as default };