"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.validateRules = validateRules;

var _regenerator = _interopRequireDefault(require("@babel/runtime/regenerator"));

var _toConsumableArray2 = _interopRequireDefault(require("@babel/runtime/helpers/toConsumableArray"));

var _asyncToGenerator2 = _interopRequireDefault(require("@babel/runtime/helpers/asyncToGenerator"));

var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));

var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));

var _asyncValidator = _interopRequireDefault(require("async-validator"));

var React = _interopRequireWildcard(require("react"));

var _warning = _interopRequireDefault(require("warning"));

var _valueUtil = require("./valueUtil");

var _messages = require("./messages");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { (0, _defineProperty2.default)(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

// Remove incorrect original ts define
var AsyncValidator = _asyncValidator.default;
/**
 * Replace with template.
 *   `I'm ${name}` + { name: 'bamboo' } = I'm bamboo
 */

function replaceMessage(template, kv) {
  return template.replace(/\$\{\w+\}/g, function (str) {
    var key = str.slice(2, -1);
    return kv[key];
  });
}
/**
 * We use `async-validator` to validate rules. So have to hot replace the message with validator.
 * { required: '${name} is required' } => { required: () => 'field is required' }
 */


function convertMessages(messages, name, rule, messageVariables) {
  var kv = _objectSpread({}, rule, {
    name: name,
    enum: (rule.enum || []).join(', ')
  });

  var replaceFunc = function replaceFunc(template, additionalKV) {
    return function () {
      return replaceMessage(template, _objectSpread({}, kv, {}, additionalKV));
    };
  };
  /* eslint-disable no-param-reassign */


  function fillTemplate(source) {
    var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    Object.keys(source).forEach(function (ruleName) {
      var value = source[ruleName];

      if (typeof value === 'string') {
        target[ruleName] = replaceFunc(value, messageVariables);
      } else if (value && (0, _typeof2.default)(value) === 'object') {
        target[ruleName] = {};
        fillTemplate(value, target[ruleName]);
      } else {
        target[ruleName] = value;
      }
    });
    return target;
  }
  /* eslint-enable */


  return fillTemplate((0, _valueUtil.setValues)({}, _messages.defaultValidateMessages, messages));
}

function validateRule(_x, _x2, _x3, _x4, _x5) {
  return _validateRule.apply(this, arguments);
}
/**
 * We use `async-validator` to validate the value.
 * But only check one value in a time to avoid namePath validate issue.
 */


function _validateRule() {
  _validateRule = (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee(name, value, rule, options, messageVariables) {
    var cloneRule, subRuleField, validator, messages, result, subResults;
    return _regenerator.default.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            cloneRule = _objectSpread({}, rule); // We should special handle array validate

            subRuleField = null;

            if (cloneRule && cloneRule.type === 'array' && cloneRule.defaultField) {
              subRuleField = cloneRule.defaultField;
              delete cloneRule.defaultField;
            }

            validator = new AsyncValidator((0, _defineProperty2.default)({}, name, [cloneRule]));
            messages = convertMessages(options.validateMessages, name, cloneRule, messageVariables);
            validator.messages(messages);
            result = [];
            _context.prev = 7;
            _context.next = 10;
            return Promise.resolve(validator.validate((0, _defineProperty2.default)({}, name, value), _objectSpread({}, options)));

          case 10:
            _context.next = 15;
            break;

          case 12:
            _context.prev = 12;
            _context.t0 = _context["catch"](7);

            if (_context.t0.errors) {
              result = _context.t0.errors.map(function (_ref2, index) {
                var message = _ref2.message;
                return (// Wrap ReactNode with `key`
                  React.isValidElement(message) ? React.cloneElement(message, {
                    key: "error_".concat(index)
                  }) : message
                );
              });
            } else {
              console.error(_context.t0);
              result = [messages.default()];
            }

          case 15:
            if (!(!result.length && subRuleField)) {
              _context.next = 20;
              break;
            }

            _context.next = 18;
            return Promise.all(value.map(function (subValue, i) {
              return validateRule("".concat(name, ".").concat(i), subValue, subRuleField, options, messageVariables);
            }));

          case 18:
            subResults = _context.sent;
            return _context.abrupt("return", subResults.reduce(function (prev, errors) {
              return [].concat((0, _toConsumableArray2.default)(prev), (0, _toConsumableArray2.default)(errors));
            }, []));

          case 20:
            return _context.abrupt("return", result);

          case 21:
          case "end":
            return _context.stop();
        }
      }
    }, _callee, null, [[7, 12]]);
  }));
  return _validateRule.apply(this, arguments);
}

function validateRules(namePath, value, rules, options, validateFirst, messageVariables) {
  var name = namePath.join('.'); // Fill rule with context

  var filledRules = rules.map(function (currentRule) {
    var originValidatorFunc = currentRule.validator;

    if (!originValidatorFunc) {
      return currentRule;
    }

    return _objectSpread({}, currentRule, {
      validator: function validator(rule, val, callback) {
        var hasPromise = false; // Wrap callback only accept when promise not provided

        var wrappedCallback = function wrappedCallback() {
          for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
            args[_key] = arguments[_key];
          }

          // Wait a tick to make sure return type is a promise
          Promise.resolve().then(function () {
            (0, _warning.default)(!hasPromise, 'Your validator function has already return a promise. `callback` will be ignored.');

            if (!hasPromise) {
              callback.apply(void 0, args);
            }
          });
        }; // Get promise


        var promise = originValidatorFunc(rule, val, wrappedCallback);
        hasPromise = promise && typeof promise.then === 'function' && typeof promise.catch === 'function';
        /**
         * 1. Use promise as the first priority.
         * 2. If promise not exist, use callback with warning instead
         */

        (0, _warning.default)(hasPromise, '`callback` is deprecated. Please return a promise instead.');

        if (hasPromise) {
          promise.then(function () {
            callback();
          }).catch(function (err) {
            callback(err);
          });
        }
      }
    });
  });
  var rulePromises = filledRules.map(function (rule) {
    return validateRule(name, value, rule, options, messageVariables);
  });
  var summaryPromise = (validateFirst ? finishOnFirstFailed(rulePromises) : finishOnAllFailed(rulePromises)).then(function (errors) {
    if (!errors.length) {
      return [];
    }

    return Promise.reject(errors);
  }); // Internal catch error to avoid console error log.

  summaryPromise.catch(function (e) {
    return e;
  });
  return summaryPromise;
}

function finishOnAllFailed(_x6) {
  return _finishOnAllFailed.apply(this, arguments);
}

function _finishOnAllFailed() {
  _finishOnAllFailed = (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee2(rulePromises) {
    return _regenerator.default.wrap(function _callee2$(_context2) {
      while (1) {
        switch (_context2.prev = _context2.next) {
          case 0:
            return _context2.abrupt("return", Promise.all(rulePromises).then(function (errorsList) {
              var _ref3;

              var errors = (_ref3 = []).concat.apply(_ref3, (0, _toConsumableArray2.default)(errorsList));

              return errors;
            }));

          case 1:
          case "end":
            return _context2.stop();
        }
      }
    }, _callee2);
  }));
  return _finishOnAllFailed.apply(this, arguments);
}

function finishOnFirstFailed(_x7) {
  return _finishOnFirstFailed.apply(this, arguments);
}

function _finishOnFirstFailed() {
  _finishOnFirstFailed = (0, _asyncToGenerator2.default)( /*#__PURE__*/_regenerator.default.mark(function _callee3(rulePromises) {
    var count;
    return _regenerator.default.wrap(function _callee3$(_context3) {
      while (1) {
        switch (_context3.prev = _context3.next) {
          case 0:
            count = 0;
            return _context3.abrupt("return", new Promise(function (resolve) {
              rulePromises.forEach(function (promise) {
                promise.then(function (errors) {
                  if (errors.length) {
                    resolve(errors);
                  }

                  count += 1;

                  if (count === rulePromises.length) {
                    resolve([]);
                  }
                });
              });
            }));

          case 2:
          case "end":
            return _context3.stop();
        }
      }
    }, _callee3);
  }));
  return _finishOnFirstFailed.apply(this, arguments);
}