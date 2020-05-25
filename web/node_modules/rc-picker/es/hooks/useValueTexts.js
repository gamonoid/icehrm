import shallowEqual from 'shallowequal';
import useMemo from "rc-util/es/hooks/useMemo";
export default function useValueTexts(value, _ref) {
  var formatList = _ref.formatList,
      generateConfig = _ref.generateConfig,
      locale = _ref.locale;
  return useMemo(function () {
    if (!value) {
      return [''];
    }

    return formatList.map(function (subFormat) {
      return generateConfig.locale.format(locale.locale, value, subFormat);
    });
  }, [value, formatList], function (prev, next) {
    return prev[0] !== next[0] || !shallowEqual(prev[1], next[1]);
  });
}