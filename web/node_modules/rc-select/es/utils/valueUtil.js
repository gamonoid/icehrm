function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _toArray(arr) { return _arrayWithHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import warning from "rc-util/es/warning";
import { toArray } from './commonUtil';

function getKey(data, index) {
  var key = data.key;
  var value;

  if ('value' in data) {
    value = data.value;
  }

  if (key !== null && key !== undefined) {
    return key;
  }

  if (value !== undefined) {
    return value;
  }

  return "rc-index-key-".concat(index);
}
/**
 * Flat options into flatten list.
 * We use `optionOnly` here is aim to avoid user use nested option group.
 * Here is simply set `key` to the index if not provided.
 */


export function flattenOptions(options) {
  var flattenList = [];

  function dig(list, isGroupOption) {
    list.forEach(function (data) {
      if (isGroupOption || !('options' in data)) {
        // Option
        flattenList.push({
          key: getKey(data, flattenList.length),
          groupOption: isGroupOption,
          data: data
        });
      } else {
        // Option Group
        flattenList.push({
          key: getKey(data, flattenList.length),
          group: true,
          data: data
        });
        dig(data.options, true);
      }
    });
  }

  dig(options, false);
  return flattenList;
}
/**
 * Inject `props` into `option` for legacy usage
 */

function injectPropsWithOption(option) {
  var newOption = _objectSpread({}, option);

  if (!('props' in newOption)) {
    Object.defineProperty(newOption, 'props', {
      get: function get() {
        warning(false, 'Return type is option instead of Option instance. Please read value directly instead of reading from `props`.');
        return newOption;
      }
    });
  }

  return newOption;
}

export function findValueOption(values, options) {
  var optionMap = new Map();
  options.forEach(function (flattenItem) {
    if (!flattenItem.group) {
      var data = flattenItem.data; // Check if match

      optionMap.set(data.value, data);
    }
  });
  return values.map(function (val) {
    return injectPropsWithOption(optionMap.get(val));
  });
}
export var getLabeledValue = function getLabeledValue(value, _ref) {
  var options = _ref.options,
      prevValue = _ref.prevValue,
      labelInValue = _ref.labelInValue,
      optionLabelProp = _ref.optionLabelProp;
  var item = findValueOption([value], options)[0];
  var result = {
    value: value
  };
  var prevValItem;
  var prevValues = toArray(prevValue);

  if (labelInValue) {
    prevValItem = prevValues.find(function (prevItem) {
      if (_typeof(prevItem) === 'object' && 'value' in prevItem) {
        return prevItem.value === value;
      } // [Legacy] Support `key` as `value`


      return prevItem.key === value;
    });
  }

  if (prevValItem && _typeof(prevValItem) === 'object' && 'label' in prevValItem) {
    result.label = prevValItem.label;

    if (item && typeof prevValItem.label === 'string' && typeof item[optionLabelProp] === 'string' && prevValItem.label.trim() !== item[optionLabelProp].trim()) {
      warning(false, '`label` of `value` is not same as `label` in Select options.');
    }
  } else if (item && optionLabelProp in item) {
    result.label = item[optionLabelProp];
  } else {
    result.label = value;
  } // [Legacy] We need fill `key` as `value` to compatible old code usage


  result.key = result.value;
  return result;
};

function toRawString(content) {
  return toArray(content).join('');
}
/** Filter single option if match the search text */


function getFilterFunction(optionFilterProp) {
  return function (searchValue, option) {
    var lowerSearchText = searchValue.toLowerCase(); // Group label search

    if ('options' in option) {
      return toRawString(option.label).toLowerCase().includes(lowerSearchText);
    } // Option value search


    var rawValue = option[optionFilterProp];
    var value = toRawString(rawValue).toLowerCase();
    return value.includes(lowerSearchText) && !option.disabled;
  };
}
/** Filter options and return a new options by the search text */


export function filterOptions(searchValue, options, _ref2) {
  var optionFilterProp = _ref2.optionFilterProp,
      filterOption = _ref2.filterOption;
  var filteredOptions = [];
  var filterFunc;

  if (filterOption === false) {
    return options;
  }

  if (typeof filterOption === 'function') {
    filterFunc = filterOption;
  } else {
    filterFunc = getFilterFunction(optionFilterProp);
  }

  options.forEach(function (item) {
    // Group should check child options
    if ('options' in item) {
      // Check group first
      var matchGroup = filterFunc(searchValue, item);

      if (matchGroup) {
        filteredOptions.push(item);
      } else {
        // Check option
        var subOptions = item.options.filter(function (subItem) {
          return filterFunc(searchValue, subItem);
        });

        if (subOptions.length) {
          filteredOptions.push(_objectSpread({}, item, {
            options: subOptions
          }));
        }
      }

      return;
    }

    if (filterFunc(searchValue, injectPropsWithOption(item))) {
      filteredOptions.push(item);
    }
  });
  return filteredOptions;
}
export function getSeparatedContent(text, tokens) {
  if (!tokens || !tokens.length) {
    return null;
  }

  var match = false;

  function separate(str, _ref3) {
    var _ref4 = _toArray(_ref3),
        token = _ref4[0],
        restTokens = _ref4.slice(1);

    if (!token) {
      return [str];
    }

    var list = str.split(token);
    match = match || list.length > 1;
    return list.reduce(function (prevList, unitStr) {
      return [].concat(_toConsumableArray(prevList), _toConsumableArray(separate(unitStr, restTokens)));
    }, []).filter(function (unit) {
      return unit;
    });
  }

  var list = separate(text, tokens);
  return match ? list : null;
}
export function isValueDisabled(value, options) {
  var option = findValueOption([value], options)[0];
  return option.disabled;
}
/**
 * `tags` mode should fill un-list item into the option list
 */

export function fillOptionsWithMissingValue(options, value, optionLabelProp, labelInValue) {
  var values = toArray(value).slice().sort();

  var cloneOptions = _toConsumableArray(options); // Convert options value to set


  var optionValues = new Set();
  options.forEach(function (opt) {
    if (opt.options) {
      opt.options.forEach(function (subOpt) {
        optionValues.add(subOpt.value);
      });
    } else {
      optionValues.add(opt.value);
    }
  }); // Fill missing value

  values.forEach(function (item) {
    var val = labelInValue ? item.value : item;

    if (!optionValues.has(val)) {
      var _ref5;

      cloneOptions.push(labelInValue ? (_ref5 = {}, _defineProperty(_ref5, optionLabelProp, item.label), _defineProperty(_ref5, "value", val), _ref5) : {
        value: val
      });
    }
  });
  return cloneOptions;
}