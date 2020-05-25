function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import * as React from 'react';
import classNames from 'classnames';

var Indent = function Indent(_ref) {
  var prefixCls = _ref.prefixCls,
      level = _ref.level,
      isStart = _ref.isStart,
      isEnd = _ref.isEnd;

  if (!level) {
    return null;
  }

  var baseClassName = "".concat(prefixCls, "-indent-unit");
  var list = [];

  for (var i = 0; i < level; i += 1) {
    var _classNames;

    list.push(React.createElement("span", {
      key: i,
      className: classNames(baseClassName, (_classNames = {}, _defineProperty(_classNames, "".concat(baseClassName, "-start"), isStart[i + 1]), _defineProperty(_classNames, "".concat(baseClassName, "-end"), isEnd[i + 1]), _classNames))
    }));
  }

  return React.createElement("span", {
    "aria-hidden": "true",
    className: "".concat(prefixCls, "-indent")
  }, list);
};

export default Indent;