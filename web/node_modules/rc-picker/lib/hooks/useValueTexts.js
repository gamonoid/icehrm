"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = useValueTexts;

var _shallowequal = _interopRequireDefault(require("shallowequal"));

var _useMemo = _interopRequireDefault(require("rc-util/lib/hooks/useMemo"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function useValueTexts(value, _ref) {
  var formatList = _ref.formatList,
      generateConfig = _ref.generateConfig,
      locale = _ref.locale;
  return (0, _useMemo.default)(function () {
    if (!value) {
      return [''];
    }

    return formatList.map(function (subFormat) {
      return generateConfig.locale.format(locale.locale, value, subFormat);
    });
  }, [value, formatList], function (prev, next) {
    return prev[0] !== next[0] || !(0, _shallowequal.default)(prev[1], next[1]);
  });
}