import * as React from 'react';
export default function useWeekDisabled(_ref) {
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