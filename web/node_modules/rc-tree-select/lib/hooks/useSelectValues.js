"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = useSelectValues;

var _react = _interopRequireDefault(require("react"));

var _valueUtil = require("../utils/valueUtil");

var _strategyUtil = require("../utils/strategyUtil");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/** Return  */
function useSelectValues(rawValues, _ref) {
  var value = _ref.value,
      getEntityByValue = _ref.getEntityByValue,
      getEntityByKey = _ref.getEntityByKey,
      treeConduction = _ref.treeConduction,
      showCheckedStrategy = _ref.showCheckedStrategy,
      conductKeyEntities = _ref.conductKeyEntities,
      getLabelProp = _ref.getLabelProp;
  return _react.default.useMemo(function () {
    var mergedRawValues = rawValues;

    if (treeConduction) {
      var rawKeys = (0, _strategyUtil.formatStrategyKeys)(rawValues.map(function (val) {
        var entity = getEntityByValue(val);
        return entity ? entity.key : val;
      }), showCheckedStrategy, conductKeyEntities);
      mergedRawValues = rawKeys.map(function (key) {
        var entity = getEntityByKey(key);
        return entity ? entity.data.value : key;
      });
    }

    return (0, _valueUtil.getRawValueLabeled)(mergedRawValues, value, getEntityByValue, getLabelProp);
  }, [rawValues, value, treeConduction, showCheckedStrategy, getEntityByValue]);
}