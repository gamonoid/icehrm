"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isWindow = isWindow;
exports["default"] = getScroll;

function isWindow(obj) {
  return obj !== null && obj !== undefined && obj === obj.window;
}

function getScroll(target, top) {
  if (typeof window === 'undefined') {
    return 0;
  }

  var method = top ? 'scrollTop' : 'scrollLeft';
  var result = 0;

  if (isWindow(target)) {
    result = target[top ? 'pageYOffset' : 'pageXOffset'];
  } else if (target instanceof Document) {
    result = target.documentElement[method];
  } else if (target) {
    result = target[method];
  }

  if (target && !isWindow(target) && typeof result !== 'number') {
    result = (target.ownerDocument || target).documentElement[method];
  }

  return result;
}