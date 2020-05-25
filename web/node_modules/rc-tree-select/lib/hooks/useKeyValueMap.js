"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = useKeyValueMap;

var _react = _interopRequireDefault(require("react"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Return cached Key Value map with DataNode.
 * Only re-calculate when `flattenOptions` changed.
 */
function useKeyValueMap(flattenOptions) {
  return _react.default.useMemo(function () {
    var cacheKeyMap = new Map();
    var cacheValueMap = new Map(); // Cache options by key

    flattenOptions.forEach(function (dataNode) {
      cacheKeyMap.set(dataNode.key, dataNode);
      cacheValueMap.set(dataNode.data.value, dataNode);
    });
    return [cacheKeyMap, cacheValueMap];
  }, [flattenOptions]);
}