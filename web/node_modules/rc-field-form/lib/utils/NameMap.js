"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _valueUtil = require("./valueUtil");

/**
 * NameMap like a `Map` but accepts `string[]` as key.
 */
var NameMap = /*#__PURE__*/function () {
  function NameMap() {
    (0, _classCallCheck2.default)(this, NameMap);
    this.list = [];
  }

  (0, _createClass2.default)(NameMap, [{
    key: "set",
    value: function set(key, value) {
      var index = this.list.findIndex(function (item) {
        return (0, _valueUtil.matchNamePath)(item.key, key);
      });

      if (index !== -1) {
        this.list[index].value = value;
      } else {
        this.list.push({
          key: key,
          value: value
        });
      }
    }
  }, {
    key: "get",
    value: function get(key) {
      var result = this.list.find(function (item) {
        return (0, _valueUtil.matchNamePath)(item.key, key);
      });
      return result && result.value;
    }
  }, {
    key: "update",
    value: function update(key, updater) {
      var origin = this.get(key);
      var next = updater(origin);

      if (!next) {
        this.delete(key);
      } else {
        this.set(key, next);
      }
    }
  }, {
    key: "delete",
    value: function _delete(key) {
      this.list = this.list.filter(function (item) {
        return !(0, _valueUtil.matchNamePath)(item.key, key);
      });
    }
  }, {
    key: "map",
    value: function map(callback) {
      return this.list.map(callback);
    }
  }, {
    key: "toJSON",
    value: function toJSON() {
      var json = {};
      this.map(function (_ref) {
        var key = _ref.key,
            value = _ref.value;
        json[key.join('.')] = value;
        return null;
      });
      return json;
    }
  }]);
  return NameMap;
}();

var _default = NameMap;
exports.default = _default;