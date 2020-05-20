function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

import * as React from 'react';
import classNames from 'classnames';

var Paragraph = function Paragraph(props) {
  var getWidth = function getWidth(index) {
    var width = props.width,
        _props$rows = props.rows,
        rows = _props$rows === void 0 ? 2 : _props$rows;

    if (Array.isArray(width)) {
      return width[index];
    } // last paragraph


    if (rows - 1 === index) {
      return width;
    }

    return undefined;
  };

  var prefixCls = props.prefixCls,
      className = props.className,
      style = props.style,
      rows = props.rows;

  var rowList = _toConsumableArray(Array(rows)).map(function (_, index) {
    return (
      /*#__PURE__*/
      // eslint-disable-next-line react/no-array-index-key
      React.createElement("li", {
        key: index,
        style: {
          width: getWidth(index)
        }
      })
    );
  });

  return /*#__PURE__*/React.createElement("ul", {
    className: classNames(prefixCls, className),
    style: style
  }, rowList);
};

export default Paragraph;