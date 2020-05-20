"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var React = _interopRequireWildcard(require("react"));

var _omit = _interopRequireDefault(require("omit.js"));

var _classnames = _interopRequireDefault(require("classnames"));

var _checkbox = _interopRequireDefault(require("../checkbox"));

var _search = _interopRequireDefault(require("./search"));

var _renderListBody = _interopRequireWildcard(require("./renderListBody"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

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

var defaultRender = function defaultRender() {
  return null;
};

function isRenderResultPlainObject(result) {
  return result && !React.isValidElement(result) && Object.prototype.toString.call(result) === '[object Object]';
}

function renderListNode(renderList, props) {
  var bodyContent = renderList ? renderList(props) : null;
  var customize = !!bodyContent;

  if (!customize) {
    bodyContent = (0, _renderListBody["default"])(props);
  }

  return {
    customize: customize,
    bodyContent: bodyContent
  };
}

var TransferList = /*#__PURE__*/function (_React$PureComponent) {
  _inherits(TransferList, _React$PureComponent);

  var _super = _createSuper(TransferList);

  function TransferList(props) {
    var _this;

    _classCallCheck(this, TransferList);

    _this = _super.call(this, props);

    _this.handleFilter = function (e) {
      var handleFilter = _this.props.handleFilter;
      var filterValue = e.target.value;

      _this.setState({
        filterValue: filterValue
      });

      handleFilter(e);
    };

    _this.handleClear = function () {
      var handleClear = _this.props.handleClear;

      _this.setState({
        filterValue: ''
      });

      handleClear();
    };

    _this.matchFilter = function (text, item) {
      var filterValue = _this.state.filterValue;
      var filterOption = _this.props.filterOption;

      if (filterOption) {
        return filterOption(filterValue, item);
      }

      return text.indexOf(filterValue) >= 0;
    };

    _this.renderItem = function (item) {
      var _this$props$render = _this.props.render,
          render = _this$props$render === void 0 ? defaultRender : _this$props$render;
      var renderResult = render(item);
      var isRenderResultPlain = isRenderResultPlainObject(renderResult);
      return {
        renderedText: isRenderResultPlain ? renderResult.value : renderResult,
        renderedEl: isRenderResultPlain ? renderResult.label : renderResult,
        item: item
      };
    };

    _this.getSelectAllLabel = function (selectedCount, totalCount) {
      var _this$props = _this.props,
          itemsUnit = _this$props.itemsUnit,
          itemUnit = _this$props.itemUnit,
          selectAllLabel = _this$props.selectAllLabel;

      if (selectAllLabel) {
        return typeof selectAllLabel === 'function' ? selectAllLabel({
          selectedCount: selectedCount,
          totalCount: totalCount
        }) : selectAllLabel;
      }

      var unit = totalCount > 1 ? itemsUnit : itemUnit;
      return /*#__PURE__*/React.createElement(React.Fragment, null, (selectedCount > 0 ? "".concat(selectedCount, "/") : '') + totalCount, " ", unit);
    };

    _this.state = {
      filterValue: ''
    };
    return _this;
  }

  _createClass(TransferList, [{
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      clearTimeout(this.triggerScrollTimer);
    }
  }, {
    key: "getCheckStatus",
    value: function getCheckStatus(filteredItems) {
      var checkedKeys = this.props.checkedKeys;

      if (checkedKeys.length === 0) {
        return 'none';
      }

      if (filteredItems.every(function (item) {
        return checkedKeys.indexOf(item.key) >= 0 || !!item.disabled;
      })) {
        return 'all';
      }

      return 'part';
    }
  }, {
    key: "getFilteredItems",
    value: function getFilteredItems(dataSource, filterValue) {
      var _this2 = this;

      var filteredItems = [];
      var filteredRenderItems = [];
      dataSource.forEach(function (item) {
        var renderedItem = _this2.renderItem(item);

        var renderedText = renderedItem.renderedText; // Filter skip

        if (filterValue && filterValue.trim() && !_this2.matchFilter(renderedText, item)) {
          return null;
        }

        filteredItems.push(item);
        filteredRenderItems.push(renderedItem);
      });
      return {
        filteredItems: filteredItems,
        filteredRenderItems: filteredRenderItems
      };
    }
  }, {
    key: "getListBody",
    value: function getListBody(prefixCls, searchPlaceholder, filterValue, filteredItems, notFoundContent, filteredRenderItems, checkedKeys, renderList, showSearch, disabled) {
      var search = showSearch ? /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-body-search-wrapper")
      }, /*#__PURE__*/React.createElement(_search["default"], {
        prefixCls: "".concat(prefixCls, "-search"),
        onChange: this.handleFilter,
        handleClear: this.handleClear,
        placeholder: searchPlaceholder,
        value: filterValue,
        disabled: disabled
      })) : null;

      var _renderListNode = renderListNode(renderList, _extends(_extends({}, (0, _omit["default"])(this.props, _renderListBody.OmitProps)), {
        filteredItems: filteredItems,
        filteredRenderItems: filteredRenderItems,
        selectedKeys: checkedKeys
      })),
          bodyContent = _renderListNode.bodyContent,
          customize = _renderListNode.customize;

      var bodyNode; // We should wrap customize list body in a classNamed div to use flex layout.

      if (customize) {
        bodyNode = /*#__PURE__*/React.createElement("div", {
          className: "".concat(prefixCls, "-body-customize-wrapper")
        }, bodyContent);
      } else {
        bodyNode = filteredItems.length ? bodyContent : /*#__PURE__*/React.createElement("div", {
          className: "".concat(prefixCls, "-body-not-found")
        }, notFoundContent);
      }

      return /*#__PURE__*/React.createElement("div", {
        className: (0, _classnames["default"])(showSearch ? "".concat(prefixCls, "-body ").concat(prefixCls, "-body-with-search") : "".concat(prefixCls, "-body"))
      }, search, bodyNode);
    }
  }, {
    key: "getCheckBox",
    value: function getCheckBox(filteredItems, onItemSelectAll, showSelectAll, disabled) {
      var checkStatus = this.getCheckStatus(filteredItems);
      var checkedAll = checkStatus === 'all';
      var checkAllCheckbox = showSelectAll !== false && /*#__PURE__*/React.createElement(_checkbox["default"], {
        disabled: disabled,
        checked: checkedAll,
        indeterminate: checkStatus === 'part',
        onChange: function onChange() {
          // Only select enabled items
          onItemSelectAll(filteredItems.filter(function (item) {
            return !item.disabled;
          }).map(function (_ref) {
            var key = _ref.key;
            return key;
          }), !checkedAll);
        }
      });
      return checkAllCheckbox;
    }
  }, {
    key: "render",
    value: function render() {
      var filterValue = this.state.filterValue;
      var _this$props2 = this.props,
          prefixCls = _this$props2.prefixCls,
          dataSource = _this$props2.dataSource,
          titleText = _this$props2.titleText,
          checkedKeys = _this$props2.checkedKeys,
          disabled = _this$props2.disabled,
          footer = _this$props2.footer,
          showSearch = _this$props2.showSearch,
          style = _this$props2.style,
          searchPlaceholder = _this$props2.searchPlaceholder,
          notFoundContent = _this$props2.notFoundContent,
          renderList = _this$props2.renderList,
          onItemSelectAll = _this$props2.onItemSelectAll,
          showSelectAll = _this$props2.showSelectAll; // Custom Layout

      var footerDom = footer && footer(this.props);
      var listCls = (0, _classnames["default"])(prefixCls, _defineProperty({}, "".concat(prefixCls, "-with-footer"), !!footerDom)); // ====================== Get filtered, checked item list ======================

      var _this$getFilteredItem = this.getFilteredItems(dataSource, filterValue),
          filteredItems = _this$getFilteredItem.filteredItems,
          filteredRenderItems = _this$getFilteredItem.filteredRenderItems; // ================================= List Body =================================


      var listBody = this.getListBody(prefixCls, searchPlaceholder, filterValue, filteredItems, notFoundContent, filteredRenderItems, checkedKeys, renderList, showSearch, disabled); // ================================ List Footer ================================

      var listFooter = footerDom ? /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-footer")
      }, footerDom) : null;
      var checkAllCheckbox = this.getCheckBox(filteredItems, onItemSelectAll, showSelectAll, disabled); // ================================== Render ===================================

      return /*#__PURE__*/React.createElement("div", {
        className: listCls,
        style: style
      }, /*#__PURE__*/React.createElement("div", {
        className: "".concat(prefixCls, "-header")
      }, checkAllCheckbox, /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-header-selected")
      }, /*#__PURE__*/React.createElement("span", null, this.getSelectAllLabel(checkedKeys.length, filteredItems.length)), /*#__PURE__*/React.createElement("span", {
        className: "".concat(prefixCls, "-header-title")
      }, titleText))), listBody, listFooter);
    }
  }]);

  return TransferList;
}(React.PureComponent);

exports["default"] = TransferList;
TransferList.defaultProps = {
  dataSource: [],
  titleText: '',
  showSearch: false
};