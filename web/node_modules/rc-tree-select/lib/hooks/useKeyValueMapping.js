"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isDisabled = isDisabled;
exports.default = useKeyValueMapping;

var _react = _interopRequireDefault(require("react"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function isDisabled(dataNode, skipType) {
  if (!dataNode) {
    return true;
  }

  var _dataNode$data = dataNode.data,
      disabled = _dataNode$data.disabled,
      disableCheckbox = _dataNode$data.disableCheckbox;

  switch (skipType) {
    case 'select':
      return disabled;

    case 'checkbox':
      return disabled || disableCheckbox;
  }

  return false;
}

function useKeyValueMapping(cacheKeyMap, cacheValueMap) {
  var getEntityByKey = _react.default.useCallback(function (key) {
    var skipType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'select';
    var dataNode = cacheKeyMap.get(key);

    if (isDisabled(dataNode, skipType)) {
      return null;
    }

    return dataNode;
  }, [cacheKeyMap]);

  var getEntityByValue = _react.default.useCallback(function (value) {
    var skipType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'select';
    var dataNode = cacheValueMap.get(value);

    if (isDisabled(dataNode, skipType)) {
      return null;
    }

    return dataNode;
  }, [cacheValueMap]);

  return [getEntityByKey, getEntityByValue];
}