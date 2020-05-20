function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _createSuper(Derived) { return function () { var Super = _getPrototypeOf(Derived), result; if (_isNativeReflectConstruct()) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

/* eslint react/prop-types: 0 */
import React, { cloneElement, isValidElement } from 'react';
import classNames from 'classnames';
import Pager from './Pager';
import Options from './Options';
import KEYCODE from './KeyCode';
import LOCALE from './locale/zh_CN';

function noop() {}

function isInteger(value) {
  return (// eslint-disable-next-line no-restricted-globals
    typeof value === 'number' && isFinite(value) && Math.floor(value) === value
  );
}

function defaultItemRender(page, type, element) {
  return element;
}

function calculatePage(p, state, props) {
  var pageSize = typeof p === 'undefined' ? state.pageSize : p;
  return Math.floor((props.total - 1) / pageSize) + 1;
}

var Pagination = /*#__PURE__*/function (_React$Component) {
  _inherits(Pagination, _React$Component);

  var _super = _createSuper(Pagination);

  function Pagination(props) {
    var _this;

    _classCallCheck(this, Pagination);

    _this = _super.call(this, props);

    _this.getJumpPrevPage = function () {
      return Math.max(1, _this.state.current - (_this.props.showLessItems ? 3 : 5));
    };

    _this.getJumpNextPage = function () {
      return Math.min(calculatePage(undefined, _this.state, _this.props), _this.state.current + (_this.props.showLessItems ? 3 : 5));
    };

    _this.getItemIcon = function (icon) {
      var prefixCls = _this.props.prefixCls; // eslint-disable-next-line jsx-a11y/anchor-has-content

      var iconNode = icon || /*#__PURE__*/React.createElement("a", {
        className: "".concat(prefixCls, "-item-link")
      });

      if (typeof icon === 'function') {
        iconNode = React.createElement(icon, _objectSpread({}, _this.props));
      }

      return iconNode;
    };

    _this.savePaginationNode = function (node) {
      _this.paginationNode = node;
    };

    _this.isValid = function (page) {
      return isInteger(page) && page !== _this.state.current;
    };

    _this.shouldDisplayQuickJumper = function () {
      var _this$props = _this.props,
          showQuickJumper = _this$props.showQuickJumper,
          pageSize = _this$props.pageSize,
          total = _this$props.total;

      if (total <= pageSize) {
        return false;
      }

      return showQuickJumper;
    };

    _this.handleKeyDown = function (e) {
      if (e.keyCode === KEYCODE.ARROW_UP || e.keyCode === KEYCODE.ARROW_DOWN) {
        e.preventDefault();
      }
    };

    _this.handleKeyUp = function (e) {
      var value = _this.getValidValue(e);

      var currentInputValue = _this.state.currentInputValue;

      if (value !== currentInputValue) {
        _this.setState({
          currentInputValue: value
        });
      }

      if (e.keyCode === KEYCODE.ENTER) {
        _this.handleChange(value);
      } else if (e.keyCode === KEYCODE.ARROW_UP) {
        _this.handleChange(value - 1);
      } else if (e.keyCode === KEYCODE.ARROW_DOWN) {
        _this.handleChange(value + 1);
      }
    };

    _this.changePageSize = function (size) {
      var current = _this.state.current;
      var newCurrent = calculatePage(size, _this.state, _this.props);
      current = current > newCurrent ? newCurrent : current; // fix the issue:
      // Once 'total' is 0, 'current' in 'onShowSizeChange' is 0, which is not correct.

      if (newCurrent === 0) {
        // eslint-disable-next-line prefer-destructuring
        current = _this.state.current;
      }

      if (typeof size === 'number') {
        if (!('pageSize' in _this.props)) {
          _this.setState({
            pageSize: size
          });
        }

        if (!('current' in _this.props)) {
          _this.setState({
            current: current,
            currentInputValue: current
          });
        }
      }

      _this.props.onShowSizeChange(current, size);
    };

    _this.handleChange = function (p) {
      var disabled = _this.props.disabled;
      var page = p;

      if (_this.isValid(page) && !disabled) {
        var currentPage = calculatePage(undefined, _this.state, _this.props);

        if (page > currentPage) {
          page = currentPage;
        } else if (page < 1) {
          page = 1;
        }

        if (!('current' in _this.props)) {
          _this.setState({
            current: page,
            currentInputValue: page
          });
        }

        var pageSize = _this.state.pageSize;

        _this.props.onChange(page, pageSize);

        return page;
      }

      return _this.state.current;
    };

    _this.prev = function () {
      if (_this.hasPrev()) {
        _this.handleChange(_this.state.current - 1);
      }
    };

    _this.next = function () {
      if (_this.hasNext()) {
        _this.handleChange(_this.state.current + 1);
      }
    };

    _this.jumpPrev = function () {
      _this.handleChange(_this.getJumpPrevPage());
    };

    _this.jumpNext = function () {
      _this.handleChange(_this.getJumpNextPage());
    };

    _this.hasPrev = function () {
      return _this.state.current > 1;
    };

    _this.hasNext = function () {
      return _this.state.current < calculatePage(undefined, _this.state, _this.props);
    };

    _this.runIfEnter = function (event, callback) {
      if (event.key === 'Enter' || event.charCode === 13) {
        for (var _len = arguments.length, restParams = new Array(_len > 2 ? _len - 2 : 0), _key = 2; _key < _len; _key++) {
          restParams[_key - 2] = arguments[_key];
        }

        callback.apply(void 0, restParams);
      }
    };

    _this.runIfEnterPrev = function (e) {
      _this.runIfEnter(e, _this.prev);
    };

    _this.runIfEnterNext = function (e) {
      _this.runIfEnter(e, _this.next);
    };

    _this.runIfEnterJumpPrev = function (e) {
      _this.runIfEnter(e, _this.jumpPrev);
    };

    _this.runIfEnterJumpNext = function (e) {
      _this.runIfEnter(e, _this.jumpNext);
    };

    _this.handleGoTO = function (e) {
      if (e.keyCode === KEYCODE.ENTER || e.type === 'click') {
        _this.handleChange(_this.state.currentInputValue);
      }
    };

    var hasOnChange = props.onChange !== noop;
    var hasCurrent = ('current' in props);

    if (hasCurrent && !hasOnChange) {
      // eslint-disable-next-line no-console
      console.warn('Warning: You provided a `current` prop to a Pagination component without an `onChange` handler. This will render a read-only component.');
    }

    var _current = props.defaultCurrent;

    if ('current' in props) {
      // eslint-disable-next-line prefer-destructuring
      _current = props.current;
    }

    var _pageSize = props.defaultPageSize;

    if ('pageSize' in props) {
      // eslint-disable-next-line prefer-destructuring
      _pageSize = props.pageSize;
    }

    _current = Math.min(_current, calculatePage(_pageSize, undefined, props));
    _this.state = {
      current: _current,
      currentInputValue: _current,
      pageSize: _pageSize
    };
    return _this;
  }

  _createClass(Pagination, [{
    key: "componentDidUpdate",
    value: function componentDidUpdate(prevProps, prevState) {
      // When current page change, fix focused style of prev item
      // A hacky solution of https://github.com/ant-design/ant-design/issues/8948
      var prefixCls = this.props.prefixCls;

      if (prevState.current !== this.state.current && this.paginationNode) {
        var lastCurrentNode = this.paginationNode.querySelector(".".concat(prefixCls, "-item-").concat(prevState.current));

        if (lastCurrentNode && document.activeElement === lastCurrentNode) {
          lastCurrentNode.blur();
        }
      }
    }
  }, {
    key: "getValidValue",
    value: function getValidValue(e) {
      var inputValue = e.target.value;
      var allPages = calculatePage(undefined, this.state, this.props);
      var currentInputValue = this.state.currentInputValue;
      var value;

      if (inputValue === '') {
        value = inputValue; // eslint-disable-next-line no-restricted-globals
      } else if (isNaN(Number(inputValue))) {
        value = currentInputValue;
      } else if (inputValue >= allPages) {
        value = allPages;
      } else {
        value = Number(inputValue);
      }

      return value;
    }
  }, {
    key: "getShowSizeChanger",
    value: function getShowSizeChanger() {
      var _this$props2 = this.props,
          showSizeChanger = _this$props2.showSizeChanger,
          total = _this$props2.total,
          totalBoundaryShowSizeChanger = _this$props2.totalBoundaryShowSizeChanger;

      if (typeof showSizeChanger !== 'undefined') {
        return showSizeChanger;
      }

      return total > totalBoundaryShowSizeChanger;
    }
  }, {
    key: "renderPrev",
    value: function renderPrev(prevPage) {
      var _this$props3 = this.props,
          prevIcon = _this$props3.prevIcon,
          itemRender = _this$props3.itemRender;
      var prevButton = itemRender(prevPage, 'prev', this.getItemIcon(prevIcon));
      var disabled = !this.hasPrev();
      return isValidElement(prevButton) ? cloneElement(prevButton, {
        disabled: disabled
      }) : prevButton;
    }
  }, {
    key: "renderNext",
    value: function renderNext(nextPage) {
      var _this$props4 = this.props,
          nextIcon = _this$props4.nextIcon,
          itemRender = _this$props4.itemRender;
      var nextButton = itemRender(nextPage, 'next', this.getItemIcon(nextIcon));
      var disabled = !this.hasNext();
      return isValidElement(nextButton) ? cloneElement(nextButton, {
        disabled: disabled
      }) : nextButton;
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;

      var _this$props5 = this.props,
          prefixCls = _this$props5.prefixCls,
          className = _this$props5.className,
          style = _this$props5.style,
          disabled = _this$props5.disabled,
          hideOnSinglePage = _this$props5.hideOnSinglePage,
          total = _this$props5.total,
          locale = _this$props5.locale,
          showQuickJumper = _this$props5.showQuickJumper,
          showLessItems = _this$props5.showLessItems,
          showTitle = _this$props5.showTitle,
          showTotal = _this$props5.showTotal,
          simple = _this$props5.simple,
          itemRender = _this$props5.itemRender,
          showPrevNextJumpers = _this$props5.showPrevNextJumpers,
          jumpPrevIcon = _this$props5.jumpPrevIcon,
          jumpNextIcon = _this$props5.jumpNextIcon,
          selectComponentClass = _this$props5.selectComponentClass,
          selectPrefixCls = _this$props5.selectPrefixCls,
          pageSizeOptions = _this$props5.pageSizeOptions;
      var _this$state = this.state,
          current = _this$state.current,
          pageSize = _this$state.pageSize,
          currentInputValue = _this$state.currentInputValue; // When hideOnSinglePage is true and there is only 1 page, hide the pager

      if (hideOnSinglePage === true && total <= pageSize) {
        return null;
      }

      var allPages = calculatePage(undefined, this.state, this.props);
      var pagerList = [];
      var jumpPrev = null;
      var jumpNext = null;
      var firstPager = null;
      var lastPager = null;
      var gotoButton = null;
      var goButton = showQuickJumper && showQuickJumper.goButton;
      var pageBufferSize = showLessItems ? 1 : 2;
      var prevPage = current - 1 > 0 ? current - 1 : 0;
      var nextPage = current + 1 < allPages ? current + 1 : allPages;
      var dataOrAriaAttributeProps = Object.keys(this.props).reduce(function (prev, key) {
        if (key.substr(0, 5) === 'data-' || key.substr(0, 5) === 'aria-' || key === 'role') {
          // eslint-disable-next-line no-param-reassign
          prev[key] = _this2.props[key];
        }

        return prev;
      }, {});

      if (simple) {
        if (goButton) {
          if (typeof goButton === 'boolean') {
            gotoButton = /*#__PURE__*/React.createElement("button", {
              type: "button",
              onClick: this.handleGoTO,
              onKeyUp: this.handleGoTO
            }, locale.jump_to_confirm);
          } else {
            gotoButton = /*#__PURE__*/React.createElement("span", {
              onClick: this.handleGoTO,
              onKeyUp: this.handleGoTO
            }, goButton);
          }

          gotoButton = /*#__PURE__*/React.createElement("li", {
            title: showTitle ? "".concat(locale.jump_to).concat(current, "/").concat(allPages) : null,
            className: "".concat(prefixCls, "-simple-pager")
          }, gotoButton);
        }

        return /*#__PURE__*/React.createElement("ul", _extends({
          className: classNames(prefixCls, "".concat(prefixCls, "-simple"), className),
          style: style,
          ref: this.savePaginationNode
        }, dataOrAriaAttributeProps), /*#__PURE__*/React.createElement("li", {
          title: showTitle ? locale.prev_page : null,
          onClick: this.prev,
          tabIndex: this.hasPrev() ? 0 : null,
          onKeyPress: this.runIfEnterPrev,
          className: classNames("".concat(prefixCls, "-prev"), _defineProperty({}, "".concat(prefixCls, "-disabled"), !this.hasPrev())),
          "aria-disabled": !this.hasPrev()
        }, this.renderPrev(prevPage)), /*#__PURE__*/React.createElement("li", {
          title: showTitle ? "".concat(current, "/").concat(allPages) : null,
          className: "".concat(prefixCls, "-simple-pager")
        }, /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: currentInputValue,
          onKeyDown: this.handleKeyDown,
          onKeyUp: this.handleKeyUp,
          onChange: this.handleKeyUp,
          size: "3"
        }), /*#__PURE__*/React.createElement("span", {
          className: "".concat(prefixCls, "-slash")
        }, "/"), allPages), /*#__PURE__*/React.createElement("li", {
          title: showTitle ? locale.next_page : null,
          onClick: this.next,
          tabIndex: this.hasPrev() ? 0 : null,
          onKeyPress: this.runIfEnterNext,
          className: classNames("".concat(prefixCls, "-next"), _defineProperty({}, "".concat(prefixCls, "-disabled"), !this.hasNext())),
          "aria-disabled": !this.hasNext()
        }, this.renderNext(nextPage)), gotoButton);
      }

      if (allPages <= 3 + pageBufferSize * 2) {
        var pagerProps = {
          locale: locale,
          rootPrefixCls: prefixCls,
          onClick: this.handleChange,
          onKeyPress: this.runIfEnter,
          showTitle: showTitle,
          itemRender: itemRender
        };

        if (!allPages) {
          pagerList.push( /*#__PURE__*/React.createElement(Pager, _extends({}, pagerProps, {
            key: "noPager",
            page: allPages,
            className: "".concat(prefixCls, "-disabled")
          })));
        }

        for (var i = 1; i <= allPages; i += 1) {
          var active = current === i;
          pagerList.push( /*#__PURE__*/React.createElement(Pager, _extends({}, pagerProps, {
            key: i,
            page: i,
            active: active
          })));
        }
      } else {
        var prevItemTitle = showLessItems ? locale.prev_3 : locale.prev_5;
        var nextItemTitle = showLessItems ? locale.next_3 : locale.next_5;

        if (showPrevNextJumpers) {
          jumpPrev = /*#__PURE__*/React.createElement("li", {
            title: showTitle ? prevItemTitle : null,
            key: "prev",
            onClick: this.jumpPrev,
            tabIndex: "0",
            onKeyPress: this.runIfEnterJumpPrev,
            className: classNames("".concat(prefixCls, "-jump-prev"), _defineProperty({}, "".concat(prefixCls, "-jump-prev-custom-icon"), !!jumpPrevIcon))
          }, itemRender(this.getJumpPrevPage(), 'jump-prev', this.getItemIcon(jumpPrevIcon)));
          jumpNext = /*#__PURE__*/React.createElement("li", {
            title: showTitle ? nextItemTitle : null,
            key: "next",
            tabIndex: "0",
            onClick: this.jumpNext,
            onKeyPress: this.runIfEnterJumpNext,
            className: classNames("".concat(prefixCls, "-jump-next"), _defineProperty({}, "".concat(prefixCls, "-jump-next-custom-icon"), !!jumpNextIcon))
          }, itemRender(this.getJumpNextPage(), 'jump-next', this.getItemIcon(jumpNextIcon)));
        }

        lastPager = /*#__PURE__*/React.createElement(Pager, {
          locale: locale,
          last: true,
          rootPrefixCls: prefixCls,
          onClick: this.handleChange,
          onKeyPress: this.runIfEnter,
          key: allPages,
          page: allPages,
          active: false,
          showTitle: showTitle,
          itemRender: itemRender
        });
        firstPager = /*#__PURE__*/React.createElement(Pager, {
          locale: locale,
          rootPrefixCls: prefixCls,
          onClick: this.handleChange,
          onKeyPress: this.runIfEnter,
          key: 1,
          page: 1,
          active: false,
          showTitle: showTitle,
          itemRender: itemRender
        });
        var left = Math.max(1, current - pageBufferSize);
        var right = Math.min(current + pageBufferSize, allPages);

        if (current - 1 <= pageBufferSize) {
          right = 1 + pageBufferSize * 2;
        }

        if (allPages - current <= pageBufferSize) {
          left = allPages - pageBufferSize * 2;
        }

        for (var _i = left; _i <= right; _i += 1) {
          var _active = current === _i;

          pagerList.push( /*#__PURE__*/React.createElement(Pager, {
            locale: locale,
            rootPrefixCls: prefixCls,
            onClick: this.handleChange,
            onKeyPress: this.runIfEnter,
            key: _i,
            page: _i,
            active: _active,
            showTitle: showTitle,
            itemRender: itemRender
          }));
        }

        if (current - 1 >= pageBufferSize * 2 && current !== 1 + 2) {
          pagerList[0] = cloneElement(pagerList[0], {
            className: "".concat(prefixCls, "-item-after-jump-prev")
          });
          pagerList.unshift(jumpPrev);
        }

        if (allPages - current >= pageBufferSize * 2 && current !== allPages - 2) {
          pagerList[pagerList.length - 1] = cloneElement(pagerList[pagerList.length - 1], {
            className: "".concat(prefixCls, "-item-before-jump-next")
          });
          pagerList.push(jumpNext);
        }

        if (left !== 1) {
          pagerList.unshift(firstPager);
        }

        if (right !== allPages) {
          pagerList.push(lastPager);
        }
      }

      var totalText = null;

      if (showTotal) {
        totalText = /*#__PURE__*/React.createElement("li", {
          className: "".concat(prefixCls, "-total-text")
        }, showTotal(total, [total === 0 ? 0 : (current - 1) * pageSize + 1, current * pageSize > total ? total : current * pageSize]));
      }

      var prevDisabled = !this.hasPrev() || !allPages;
      var nextDisabled = !this.hasNext() || !allPages;
      return /*#__PURE__*/React.createElement("ul", _extends({
        className: classNames(prefixCls, className, _defineProperty({}, "".concat(prefixCls, "-disabled"), disabled)),
        style: style,
        unselectable: "unselectable",
        ref: this.savePaginationNode
      }, dataOrAriaAttributeProps), totalText, /*#__PURE__*/React.createElement("li", {
        title: showTitle ? locale.prev_page : null,
        onClick: this.prev,
        tabIndex: prevDisabled ? null : 0,
        onKeyPress: this.runIfEnterPrev,
        className: classNames("".concat(prefixCls, "-prev"), _defineProperty({}, "".concat(prefixCls, "-disabled"), prevDisabled)),
        "aria-disabled": prevDisabled
      }, this.renderPrev(prevPage)), pagerList, /*#__PURE__*/React.createElement("li", {
        title: showTitle ? locale.next_page : null,
        onClick: this.next,
        tabIndex: nextDisabled ? null : 0,
        onKeyPress: this.runIfEnterNext,
        className: classNames("".concat(prefixCls, "-next"), _defineProperty({}, "".concat(prefixCls, "-disabled"), nextDisabled)),
        "aria-disabled": nextDisabled
      }, this.renderNext(nextPage)), /*#__PURE__*/React.createElement(Options, {
        disabled: disabled,
        locale: locale,
        rootPrefixCls: prefixCls,
        selectComponentClass: selectComponentClass,
        selectPrefixCls: selectPrefixCls,
        changeSize: this.getShowSizeChanger() ? this.changePageSize : null,
        current: current,
        pageSize: pageSize,
        pageSizeOptions: pageSizeOptions,
        quickGo: this.shouldDisplayQuickJumper() ? this.handleChange : null,
        goButton: goButton
      }));
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function getDerivedStateFromProps(props, prevState) {
      var newState = {};

      if ('current' in props) {
        newState.current = props.current;

        if (props.current !== prevState.current) {
          newState.currentInputValue = newState.current;
        }
      }

      if ('pageSize' in props && props.pageSize !== prevState.pageSize) {
        var current = prevState.current;
        var newCurrent = calculatePage(props.pageSize, prevState, props);
        current = current > newCurrent ? newCurrent : current;

        if (!('current' in props)) {
          newState.current = current;
          newState.currentInputValue = current;
        }

        newState.pageSize = props.pageSize;
      }

      return newState;
    }
  }]);

  return Pagination;
}(React.Component);

Pagination.defaultProps = {
  defaultCurrent: 1,
  total: 0,
  defaultPageSize: 10,
  onChange: noop,
  className: '',
  selectPrefixCls: 'rc-select',
  prefixCls: 'rc-pagination',
  selectComponentClass: null,
  hideOnSinglePage: false,
  showPrevNextJumpers: true,
  showQuickJumper: false,
  showLessItems: false,
  showTitle: true,
  onShowSizeChange: noop,
  locale: LOCALE,
  style: {},
  itemRender: defaultItemRender,
  totalBoundaryShowSizeChanger: 50
};
export default Pagination;