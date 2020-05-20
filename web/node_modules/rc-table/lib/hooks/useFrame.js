"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.useFrameState = useFrameState;
exports.useTimeoutLock = useTimeoutLock;

var _react = require("react");

var _raf = _interopRequireDefault(require("raf"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function useFrameState(defaultState) {
  var stateRef = (0, _react.useRef)(defaultState);

  var _useState = (0, _react.useState)({}),
      _useState2 = _slicedToArray(_useState, 2),
      forceUpdate = _useState2[1];

  var timeoutRef = (0, _react.useRef)(null);
  var updateBatchRef = (0, _react.useRef)([]);

  function setFrameState(updater) {
    if (timeoutRef.current === null) {
      updateBatchRef.current = [];
      timeoutRef.current = (0, _raf.default)(function () {
        updateBatchRef.current.forEach(function (batchUpdater) {
          stateRef.current = batchUpdater(stateRef.current);
        });
        timeoutRef.current = null;
        forceUpdate({});
      });
    }

    updateBatchRef.current.push(updater);
  }

  (0, _react.useEffect)(function () {
    return function () {
      _raf.default.cancel(timeoutRef.current);
    };
  }, []);
  return [stateRef.current, setFrameState];
}
/** Lock frame, when frame pass reset the lock. */


function useTimeoutLock(defaultState) {
  var frameRef = (0, _react.useRef)(defaultState);
  var timeoutRef = (0, _react.useRef)(null);

  function cleanUp() {
    window.clearTimeout(timeoutRef.current);
  }

  function setState(newState) {
    frameRef.current = newState;
    cleanUp();
    timeoutRef.current = window.setTimeout(function () {
      frameRef.current = null;
      timeoutRef.current = null;
    }, 100);
  }

  function getState() {
    return frameRef.current;
  }

  (0, _react.useEffect)(function () {
    return cleanUp;
  }, []);
  return [setState, getState];
}