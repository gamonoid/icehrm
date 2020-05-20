"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = useWeekDisabled;

var React = _interopRequireWildcard(require("react"));

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function useWeekDisabled(_ref) {
  var disabledDate = _ref.disabledDate,
      locale = _ref.locale,
      generateConfig = _ref.generateConfig;
  var disabledCache = React.useMemo(function () {
    return new Map();
  }, [disabledDate]);

  function disabledWeekDate(date) {
    var weekStr = generateConfig.locale.format(locale.locale, date, 'YYYY-WW');

    if (!disabledCache.has(weekStr)) {
      var disabled = false;

      var checkDisabled = function checkDisabled(offset) {
        for (var i = 0; i < 7; i += 1) {
          var currentDate = generateConfig.addDate(date, i * offset);
          var currentWeekStr = generateConfig.locale.format(locale.locale, currentDate, 'YYYY-WW');

          if (currentWeekStr !== weekStr) {
            break;
          }

          if (disabledDate(currentDate)) {
            disabled = true;
            break;
          }
        }
      };

      checkDisabled(1);
      checkDisabled(-1);
      disabledCache.set(weekStr, disabled);
    }

    return disabledCache.get(weekStr);
  }

  return [disabledWeekDate];
}