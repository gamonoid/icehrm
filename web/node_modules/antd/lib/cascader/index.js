"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _rcCascader = _interopRequireDefault(require("rc-cascader"));

var _arrayTreeFilter = _interopRequireDefault(require("array-tree-filter"));

var _classnames = _interopRequireDefault(require("classnames"));

var _omit = _interopRequireDefault(require("omit.js"));

var _isEqual = _interopRequireDefault(require("lodash/isEqual"));

var _KeyCode = _interopRequireDefault(require("rc-util/lib/KeyCode"));

var _CloseCircleFilled = _interopRequireDefault(require("@ant-design/icons/CloseCircleFilled"));

var _DownOutlined = _interopRequireDefault(require("@ant-design/icons/DownOutlined"));

var _RightOutlined = _interopRequireDefault(require("@ant-design/icons/RightOutlined"));

var _RedoOutlined = _interopRequireDefault(require("@ant-design/icons/RedoOutlined"));

var _LeftOutlined = _interopRequireDefault(require("@ant-design/icons/LeftOutlined"));

var _input = _interopRequireDefault(require("../input"));

var _configProvider = require("../config-provider");

var _LocaleReceiver = _interopRequireDefault(require("../locale-provider/LocaleReceiver"));

var _warning = _interopRequireDefault(require("../_util/warning"));

var _SizeContext = _interopRequireDefault(require("../config-provider/SizeContext"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { return function () { var Super = _getPrototypeOf(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

var __rest = void 0 && (void 0).__rest || function (s, e) {
  var t = {};

  for (var p in s) {
    if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0) t[p] = s[p];
  }

  if (s != null && typeof Object.getOwnPropertySymbols === "function") for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
    if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i])) t[p[i]] = s[p[i]];
  }
  return t;
};

// We limit the filtered item count by default
var defaultLimit = 50;

function highlightKeyword(str, keyword, prefixCls) {
  return str.split(keyword).map(function (node, index) {
    return index === 0 ? node : [/*#__PURE__*/React.createElement("span", {
      className: "".concat(prefixCls, "-menu-item-keyword"),
      key: "seperator"
    }, keyword), node];
  });
}

function defaultFilterOption(inputValue, path, names) {
  return path.some(function (option) {
    return option[names.label].indexOf(inputValue) > -1;
  });
}

function defaultRenderFilteredOption(inputValue, path, prefixCls, names) {
  return path.map(function (option, index) {
    var label = option[names.label];
    var node = label.indexOf(inputValue) > -1 ? highlightKeyword(label, inputValue, prefixCls) : label;
    return index === 0 ? node : [' / ', node];
  });
}

function defaultSortFilteredOption(a, b, inputValue, names) {
  function callback(elem) {
    return elem[names.label].indexOf(inputValue) > -1;
  }

  return a.findIndex(callback) - b.findIndex(callback);
}

function getFieldNames(_ref) {
  var fieldNames = _ref.fieldNames;
  return fieldNames;
}

function getFilledFieldNames(props) {
  var fieldNames = getFieldNames(props) || {};
  var names = {
    children: fieldNames.children || 'children',
    label: fieldNames.label || 'label',
    value: fieldNames.value || 'value'
  };
  return names;
}

function flattenTree(options, props) {
  var ancestor = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
  var names = getFilledFieldNames(props);
  var flattenOptions = [];
  var childrenName = names.children;
  options.forEach(function (option) {
    var path = ancestor.concat(option);

    if (props.changeOnSelect || !option[childrenName] || !option[childrenName].length) {
      flattenOptions.push(path);
    }

    if (option[childrenName]) {
      flattenOptions = flattenOptions.concat(flattenTree(option[childrenName], props, path));
    }
  });
  return flattenOptions;
}

var defaultDisplayRender = function defaultDisplayRender(label) {
  return label.join(' / ');
};

function warningValueNotExist(list) {
  var fieldNames = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  (list || []).forEach(function (item) {
    var valueFieldName = fieldNames.value || 'value';
    (0, _warning["default"])(valueFieldName in item, 'Cascader', 'Not found `value` in `options`.');
    warningValueNotExist(item[fieldNames.children || 'children'], fieldNames);
  });
}

var Cascader = /*#__PURE__*/function (_React$Component) {
  _inherits(Cascader, _React$Component);

  var _super = _createSuper(Cascader);

  function Cascader(props) {
    var _this;

    _classCallCheck(this, Cascader);

    _this = _super.call(this, props);
    _this.cachedOptions = [];

    _this.setValue = function (value) {
      var selectedOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];

      if (!('value' in _this.props)) {
        _this.setState({
          value: value
        });
      }

      var onChange = _this.props.onChange;

      if (onChange) {
        onChange(value, selectedOptions);
      }
    };

    _this.saveInput = function (node) {
      _this.input = node;
    };

    _this.handleChange = function (value, selectedOptions) {
      _this.setState({
        inputValue: ''
      });

      if (selectedOptions[0].__IS_FILTERED_OPTION) {
        var unwrappedValue = value[0];
        var unwrappedSelectedOptions = selectedOptions[0].path;

        _this.setValue(unwrappedValue, unwrappedSelectedOptions);

        return;
      }

      _this.setValue(value, selectedOptions);
    };

    _this.handlePopupVisibleChange = function (popupVisible) {
      if (!('popupVisible' in _this.props)) {
        _this.setState(function (state) {
          return {
            popupVisible: popupVisible,
            inputFocused: popupVisible,
            inputValue: popupVisible ? state.inputValue : ''
          };
        });
      }

      var onPopupVisibleChange = _this.props.onPopupVisibleChange;

      if (onPopupVisibleChange) {
        onPopupVisibleChange(popupVisible);
      }
    };

    _this.handleInputBlur = function () {
      _this.setState({
        inputFocused: false
      });
    };

    _this.handleInputClick = function (e) {
      var _this$state = _this.state,
          inputFocused = _this$state.inputFocused,
          popupVisible = _this$state.popupVisible; // Prevent `Trigger` behaviour.

      if (inputFocused || popupVisible) {
        e.stopPropagation();

        if (e.nativeEvent.stopImmediatePropagation) {
          e.nativeEvent.stopImmediatePropagation();
        }
      }
    };

    _this.handleKeyDown = function (e) {
      // SPACE => https://github.com/ant-design/ant-design/issues/16871
      if (e.keyCode === _KeyCode["default"].BACKSPACE || e.keyCode === _KeyCode["default"].SPACE) {
        e.stopPropagation();
      }
    };

    _this.handleInputChange = function (e) {
      var inputValue = e.target.value;

      _this.setState({
        inputValue: inputValue
      });
    };

    _this.clearSelection = function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (!_this.state.inputValue) {
        _this.setValue([]);

        _this.handlePopupVisibleChange(false);
      } else {
        _this.setState({
          inputValue: ''
        });
      }
    };

    _this.renderCascader = function (_ref2, locale) {
      var getContextPopupContainer = _ref2.getPopupContainer,
          getPrefixCls = _ref2.getPrefixCls,
          renderEmpty = _ref2.renderEmpty,
          direction = _ref2.direction;
      return /*#__PURE__*/React.createElement(_SizeContext["default"].Consumer, null, function (size) {
        var _classNames, _classNames2, _classNames3, _classNames4;

        var _assertThisInitialize = _assertThisInitialized(_this),
            props = _assertThisInitialize.props,
            state = _assertThisInitialize.state;

        var customizePrefixCls = props.prefixCls,
            customizeInputPrefixCls = props.inputPrefixCls,
            children = props.children,
            _props$placeholder = props.placeholder,
            placeholder = _props$placeholder === void 0 ? locale.placeholder || 'Please select' : _props$placeholder,
            customizeSize = props.size,
            disabled = props.disabled,
            className = props.className,
            style = props.style,
            allowClear = props.allowClear,
            _props$showSearch = props.showSearch,
            showSearch = _props$showSearch === void 0 ? false : _props$showSearch,
            suffixIcon = props.suffixIcon,
            notFoundContent = props.notFoundContent,
            popupClassName = props.popupClassName,
            bordered = props.bordered,
            otherProps = __rest(props, ["prefixCls", "inputPrefixCls", "children", "placeholder", "size", "disabled", "className", "style", "allowClear", "showSearch", "suffixIcon", "notFoundContent", "popupClassName", "bordered"]);

        var mergedSize = customizeSize || size;
        var value = state.value,
            inputFocused = state.inputFocused;
        var isRtlLayout = direction === 'rtl';
        var prefixCls = getPrefixCls('cascader', customizePrefixCls);
        var inputPrefixCls = getPrefixCls('input', customizeInputPrefixCls);
        var sizeCls = (0, _classnames["default"])((_classNames = {}, _defineProperty(_classNames, "".concat(inputPrefixCls, "-lg"), mergedSize === 'large'), _defineProperty(_classNames, "".concat(inputPrefixCls, "-sm"), mergedSize === 'small'), _classNames));
        var clearIcon = allowClear && !disabled && value.length > 0 || state.inputValue ? /*#__PURE__*/React.createElement(_CloseCircleFilled["default"], {
          className: "".concat(prefixCls, "-picker-clear"),
          onClick: _this.clearSelection
        }) : null;
        var arrowCls = (0, _classnames["default"])((_classNames2 = {}, _defineProperty(_classNames2, "".concat(prefixCls, "-picker-arrow"), true), _defineProperty(_classNames2, "".concat(prefixCls, "-picker-arrow-expand"), state.popupVisible), _classNames2));
        var pickerCls = (0, _classnames["default"])(className, "".concat(prefixCls, "-picker"), (_classNames3 = {}, _defineProperty(_classNames3, "".concat(prefixCls, "-picker-rtl"), isRtlLayout), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-with-value"), state.inputValue), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-disabled"), disabled), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-").concat(mergedSize), !!mergedSize), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-show-search"), !!showSearch), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-focused"), inputFocused), _defineProperty(_classNames3, "".concat(prefixCls, "-picker-borderless"), !bordered), _classNames3)); // Fix bug of https://github.com/facebook/react/pull/5004
        // and https://fb.me/react-unknown-prop

        var inputProps = (0, _omit["default"])(otherProps, ['onChange', 'options', 'popupPlacement', 'transitionName', 'displayRender', 'onPopupVisibleChange', 'changeOnSelect', 'expandTrigger', 'popupVisible', 'getPopupContainer', 'loadData', 'popupClassName', 'filterOption', 'renderFilteredOption', 'sortFilteredOption', 'notFoundContent', 'fieldNames', 'bordered']);
        var options = props.options;
        var names = getFilledFieldNames(_this.props);

        if (options && options.length > 0) {
          if (state.inputValue) {
            var filteredOptions = _this.generateFilteredOptions(prefixCls, renderEmpty);

            options = (0, _isEqual["default"])(filteredOptions, _this.cachedOptions) ? _this.cachedOptions : filteredOptions;
          }
        } else {
          var _ref3;

          options = [(_ref3 = {}, _defineProperty(_ref3, names.label, notFoundContent || renderEmpty('Cascader')), _defineProperty(_ref3, names.value, 'ANT_CASCADER_NOT_FOUND'), _defineProperty(_ref3, "disabled", true), _ref3)];
        } // Dropdown menu should keep previous status until it is fully closed.


        if (!state.popupVisible) {
          options = _this.cachedOptions;
        } else {
          _this.cachedOptions = options;
        }

        var dropdownMenuColumnStyle = {};
        var isNotFound = (options || []).length === 1 && options[0].isEmptyNode;

        if (isNotFound) {
          dropdownMenuColumnStyle.height = 'auto'; // Height of one row.
        } // The default value of `matchInputWidth` is `true`


        var resultListMatchInputWidth = showSearch.matchInputWidth !== false;

        if (resultListMatchInputWidth && (state.inputValue || isNotFound) && _this.input) {
          dropdownMenuColumnStyle.width = _this.input.input.offsetWidth;
        }

        var inputIcon = suffixIcon && (React.isValidElement(suffixIcon) ? React.cloneElement(suffixIcon, {
          className: (0, _classnames["default"])((_classNames4 = {}, _defineProperty(_classNames4, suffixIcon.props.className, suffixIcon.props.className), _defineProperty(_classNames4, "".concat(prefixCls, "-picker-arrow"), true), _classNames4))
        }) : /*#__PURE__*/React.createElement("span", {
          className: "".concat(prefixCls, "-picker-arrow")
        }, suffixIcon)) || /*#__PURE__*/React.createElement(_DownOutlined["default"], {
          className: arrowCls
        });
        var input = children || /*#__PURE__*/React.createElement("span", {
          style: style,
          className: pickerCls
        }, /*#__PURE__*/React.createElement("span", {
          className: "".concat(prefixCls, "-picker-label")
        }, _this.getLabel()), /*#__PURE__*/React.createElement(_input["default"], _extends({}, inputProps, {
          tabIndex: "-1",
          ref: _this.saveInput,
          prefixCls: inputPrefixCls,
          placeholder: value && value.length > 0 ? undefined : placeholder,
          className: "".concat(prefixCls, "-input ").concat(sizeCls),
          value: state.inputValue,
          disabled: disabled,
          readOnly: !showSearch,
          autoComplete: inputProps.autoComplete || 'off',
          onClick: showSearch ? _this.handleInputClick : undefined,
          onBlur: showSearch ? _this.handleInputBlur : undefined,
          onKeyDown: _this.handleKeyDown,
          onChange: showSearch ? _this.handleInputChange : undefined
        })), clearIcon, inputIcon);
        var expandIcon = /*#__PURE__*/React.createElement(_RightOutlined["default"], null);

        if (isRtlLayout) {
          expandIcon = /*#__PURE__*/React.createElement(_LeftOutlined["default"], null);
        }

        var loadingIcon = /*#__PURE__*/React.createElement("span", {
          className: "".concat(prefixCls, "-menu-item-loading-icon")
        }, /*#__PURE__*/React.createElement(_RedoOutlined["default"], {
          spin: true
        }));
        var getPopupContainer = props.getPopupContainer || getContextPopupContainer;
        var rest = (0, _omit["default"])(props, ['inputIcon', 'expandIcon', 'loadingIcon', 'bordered']);
        var rcCascaderRtlPopupClassName = (0, _classnames["default"])(popupClassName, _defineProperty({}, "".concat(prefixCls, "-menu-").concat(direction), direction === 'rtl'));
        return /*#__PURE__*/React.createElement(_rcCascader["default"], _extends({}, rest, {
          prefixCls: prefixCls,
          getPopupContainer: getPopupContainer,
          options: options,
          value: value,
          popupVisible: state.popupVisible,
          onPopupVisibleChange: _this.handlePopupVisibleChange,
          onChange: _this.handleChange,
          dropdownMenuColumnStyle: dropdownMenuColumnStyle,
          expandIcon: expandIcon,
          loadingIcon: loadingIcon,
          popupClassName: rcCascaderRtlPopupClassName,
          popupPlacement: _this.getPopupPlacement(direction)
        }), input);
      });
    };

    _this.state = {
      value: props.value || props.defaultValue || [],
      inputValue: '',
      inputFocused: false,
      popupVisible: props.popupVisible,
      flattenOptions: props.showSearch ? flattenTree(props.options, props) : undefined,
      prevProps: props
    };
    return _this;
  }

  _createClass(Cascader, [{
    key: "getLabel",
    value: function getLabel() {
      var _this$props = this.props,
          options = _this$props.options,
          _this$props$displayRe = _this$props.displayRender,
          displayRender = _this$props$displayRe === void 0 ? defaultDisplayRender : _this$props$displayRe;
      var names = getFilledFieldNames(this.props);
      var value = this.state.value;
      var unwrappedValue = Array.isArray(value[0]) ? value[0] : value;
      var selectedOptions = (0, _arrayTreeFilter["default"])(options, function (o, level) {
        return o[names.value] === unwrappedValue[level];
      }, {
        childrenKeyName: names.children
      });
      var label = selectedOptions.map(function (o) {
        return o[names.label];
      });
      return displayRender(label, selectedOptions);
    }
  }, {
    key: "generateFilteredOptions",
    value: function generateFilteredOptions(prefixCls, renderEmpty) {
      var _this2 = this,
          _ref5;

      var _this$props2 = this.props,
          showSearch = _this$props2.showSearch,
          notFoundContent = _this$props2.notFoundContent;
      var names = getFilledFieldNames(this.props);
      var _showSearch$filter = showSearch.filter,
          filter = _showSearch$filter === void 0 ? defaultFilterOption : _showSearch$filter,
          _showSearch$render = showSearch.render,
          render = _showSearch$render === void 0 ? defaultRenderFilteredOption : _showSearch$render,
          _showSearch$sort = showSearch.sort,
          sort = _showSearch$sort === void 0 ? defaultSortFilteredOption : _showSearch$sort,
          _showSearch$limit = showSearch.limit,
          limit = _showSearch$limit === void 0 ? defaultLimit : _showSearch$limit;
      var _this$state2 = this.state,
          _this$state2$flattenO = _this$state2.flattenOptions,
          flattenOptions = _this$state2$flattenO === void 0 ? [] : _this$state2$flattenO,
          inputValue = _this$state2.inputValue; // Limit the filter if needed

      var filtered;

      if (limit > 0) {
        filtered = [];
        var matchCount = 0; // Perf optimization to filter items only below the limit

        flattenOptions.some(function (path) {
          var match = filter(_this2.state.inputValue, path, names);

          if (match) {
            filtered.push(path);
            matchCount += 1;
          }

          return matchCount >= limit;
        });
      } else {
        (0, _warning["default"])(typeof limit !== 'number', 'Cascader', "'limit' of showSearch should be positive number or false.");
        filtered = flattenOptions.filter(function (path) {
          return filter(_this2.state.inputValue, path, names);
        });
      }

      filtered.sort(function (a, b) {
        return sort(a, b, inputValue, names);
      });

      if (filtered.length > 0) {
        return filtered.map(function (path) {
          var _ref4;

          return _ref4 = {
            __IS_FILTERED_OPTION: true,
            path: path
          }, _defineProperty(_ref4, names.value, path.map(function (o) {
            return o[names.value];
          })), _defineProperty(_ref4, names.label, render(inputValue, path, prefixCls, names)), _defineProperty(_ref4, "disabled", path.some(function (o) {
            return !!o.disabled;
          })), _defineProperty(_ref4, "isEmptyNode", true), _ref4;
        });
      }

      return [(_ref5 = {}, _defineProperty(_ref5, names.value, 'ANT_CASCADER_NOT_FOUND'), _defineProperty(_ref5, names.label, notFoundContent || renderEmpty('Cascader')), _defineProperty(_ref5, "disabled", true), _defineProperty(_ref5, "isEmptyNode", true), _ref5)];
    }
  }, {
    key: "focus",
    value: function focus() {
      this.input.focus();
    }
  }, {
    key: "blur",
    value: function blur() {
      this.input.blur();
    }
  }, {
    key: "getPopupPlacement",
    value: function getPopupPlacement() {
      var direction = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'ltr';
      var popupPlacement = this.props.popupPlacement;

      if (popupPlacement !== undefined) {
        return popupPlacement;
      }

      return direction === 'rtl' ? 'bottomRight' : 'bottomLeft';
    }
  }, {
    key: "render",
    value: function render() {
      var _this3 = this;

      return /*#__PURE__*/React.createElement(_configProvider.ConfigConsumer, null, function (configArgument) {
        return /*#__PURE__*/React.createElement(_LocaleReceiver["default"], null, function (locale) {
          return _this3.renderCascader(configArgument, locale);
        });
      });
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function getDerivedStateFromProps(nextProps, _ref6) {
      var prevProps = _ref6.prevProps;
      var newState = {
        prevProps: nextProps
      };

      if ('value' in nextProps) {
        newState.value = nextProps.value || [];
      }

      if ('popupVisible' in nextProps) {
        newState.popupVisible = nextProps.popupVisible;
      }

      if (nextProps.showSearch && prevProps.options !== nextProps.options) {
        newState.flattenOptions = flattenTree(nextProps.options, nextProps);
      }

      if (process.env.NODE_ENV !== 'production' && nextProps.options) {
        warningValueNotExist(nextProps.options, getFieldNames(nextProps));
      }

      return newState;
    }
  }]);

  return Cascader;
}(React.Component);

Cascader.defaultProps = {
  transitionName: 'slide-up',
  options: [],
  disabled: false,
  allowClear: true,
  bordered: true
};
var _default = Cascader;
exports["default"] = _default;