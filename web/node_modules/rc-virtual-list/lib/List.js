"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var React = _interopRequireWildcard(require("react"));

var _classnames = _interopRequireDefault(require("classnames"));

var _raf = _interopRequireDefault(require("raf"));

var _Filler = _interopRequireDefault(require("./Filler"));

var _itemUtil = require("./utils/itemUtil");

var _algorithmUtil = require("./utils/algorithmUtil");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ScrollStyle = {
  overflowY: 'auto',
  overflowAnchor: 'none'
};
var ITEM_SCALE_RATE = 1;
/**
 * We use class component here since typescript can not support generic in function component
 *
 * Virtual list display logic:
 * 1. scroll / initialize trigger measure
 * 2. Get location item of current `scrollTop`
 * 3. [Render] Render visible items
 * 4. Get all the visible items height
 * 5. [Render] Update top item `margin-top` to fit the position
 *
 * Algorithm:
 * We split scroll bar into equal slice. An item with whatever height occupy the same range slice.
 * When `scrollTop` change,
 * it will calculate the item percentage position and move item to the position.
 * Then calculate other item position base on the located item.
 *
 * Concept:
 *
 * # located item
 * The base position item which other items position calculate base on.
 */

var List = /*#__PURE__*/function (_React$Component) {
  _inherits(List, _React$Component);

  function List(props) {
    var _this;

    _classCallCheck(this, List);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(List).call(this, props));
    _this.listRef = React.createRef();
    _this.itemElements = {};
    _this.itemElementHeights = {};
    /**
     * Lock scroll process with `onScroll` event.
     * This is used for `data` length change and `scrollTop` restore
     */

    _this.lockScroll = false;
    /**
     * Phase 2: Trigger render since we should re-calculate current position.
     */

    _this.onScroll = function (event) {
      var _this$props = _this.props,
          data = _this$props.data,
          height = _this$props.height,
          itemHeight = _this$props.itemHeight,
          disabled = _this$props.disabled;
      var _this$listRef$current = _this.listRef.current,
          originScrollTop = _this$listRef$current.scrollTop,
          clientHeight = _this$listRef$current.clientHeight,
          scrollHeight = _this$listRef$current.scrollHeight;
      var scrollTop = (0, _itemUtil.alignScrollTop)(originScrollTop, scrollHeight - clientHeight); // Skip if `scrollTop` not change to avoid shake

      if (scrollTop === _this.state.scrollTop || _this.lockScroll || disabled) {
        return;
      }

      var scrollPtg = (0, _itemUtil.getElementScrollPercentage)(_this.listRef.current);
      var visibleCount = Math.ceil(height / itemHeight);

      var _getRangeIndex = (0, _itemUtil.getRangeIndex)(scrollPtg, data.length, visibleCount),
          itemIndex = _getRangeIndex.itemIndex,
          itemOffsetPtg = _getRangeIndex.itemOffsetPtg,
          startIndex = _getRangeIndex.startIndex,
          endIndex = _getRangeIndex.endIndex;

      _this.setState({
        status: 'MEASURE_START',
        scrollTop: scrollTop,
        itemIndex: itemIndex,
        itemOffsetPtg: itemOffsetPtg,
        startIndex: startIndex,
        endIndex: endIndex
      });

      _this.triggerOnScroll(event);
    };

    _this.onRawScroll = function (event) {
      var scrollTop = _this.listRef.current.scrollTop;

      _this.setState({
        scrollTop: scrollTop
      });

      _this.triggerOnScroll(event);
    };

    _this.triggerOnScroll = function (event) {
      var onScroll = _this.props.onScroll;

      if (onScroll && event) {
        onScroll(event);
      }
    };

    _this.getIndexKey = function (index, props) {
      var mergedProps = props || _this.props;
      var _mergedProps$data = mergedProps.data,
          data = _mergedProps$data === void 0 ? [] : _mergedProps$data; // Return ghost key as latest index item

      if (index === data.length) {
        return _itemUtil.GHOST_ITEM_KEY;
      }

      var item = data[index];

      if (!item) {
        /* istanbul ignore next */
        console.error('Not find index item. Please report this since it is a bug.');
      }

      return _this.getItemKey(item, mergedProps);
    };

    _this.getItemKey = function (item, props) {
      var _ref = props || _this.props,
          itemKey = _ref.itemKey;

      return typeof itemKey === 'function' ? itemKey(item) : item[itemKey];
    };
    /**
     * Collect current rendered dom element item heights
     */


    _this.collectItemHeights = function (range) {
      var _ref2 = range || _this.state,
          startIndex = _ref2.startIndex,
          endIndex = _ref2.endIndex;

      var data = _this.props.data; // Record here since measure item height will get warning in `render`

      for (var index = startIndex; index <= endIndex; index += 1) {
        var item = data[index]; // Only collect exist item height

        if (item) {
          var eleKey = _this.getItemKey(item);

          _this.itemElementHeights[eleKey] = (0, _itemUtil.getNodeHeight)(_this.itemElements[eleKey]);
        }
      }
    };

    _this.scrollTo = function (arg0) {
      (0, _raf.default)(function () {
        // Number top
        if (_typeof(arg0) === 'object') {
          var isVirtual = _this.state.isVirtual;
          var _this$props2 = _this.props,
              height = _this$props2.height,
              itemHeight = _this$props2.itemHeight,
              data = _this$props2.data;
          var _arg0$align = arg0.align,
              align = _arg0$align === void 0 ? 'auto' : _arg0$align;
          var index = 0;

          if ('index' in arg0) {
            index = arg0.index;
          } else if ('key' in arg0) {
            var key = arg0.key;
            index = data.findIndex(function (item) {
              return _this.getItemKey(item) === key;
            });
          }

          var visibleCount = Math.ceil(height / itemHeight);
          var item = data[index];

          if (item) {
            var clientHeight = _this.listRef.current.clientHeight;

            if (isVirtual) {
              // Calculate related data
              var _this$state = _this.state,
                  itemIndex = _this$state.itemIndex,
                  itemOffsetPtg = _this$state.itemOffsetPtg;
              var scrollTop = _this.listRef.current.scrollTop;
              var scrollPtg = (0, _itemUtil.getElementScrollPercentage)(_this.listRef.current);
              var relativeLocatedItemTop = (0, _itemUtil.getItemRelativeTop)({
                itemIndex: itemIndex,
                itemOffsetPtg: itemOffsetPtg,
                itemElementHeights: _this.itemElementHeights,
                scrollPtg: scrollPtg,
                clientHeight: clientHeight,
                getItemKey: _this.getIndexKey
              }); // We will force render related items to collect height for re-location

              _this.setState({
                startIndex: Math.max(0, index - visibleCount),
                endIndex: Math.min(data.length - 1, index + visibleCount)
              }, function () {
                _this.collectItemHeights(); // Calculate related top


                var relativeTop;
                var mergedAlgin = align;

                if (align === 'auto') {
                  var shouldChange = true; // Check if exist in the visible range

                  if (Math.abs(itemIndex - index) < visibleCount) {
                    var itemTop = relativeLocatedItemTop;

                    if (index < itemIndex) {
                      for (var i = index; i < itemIndex; i += 1) {
                        var eleKey = _this.getIndexKey(i);

                        itemTop -= _this.itemElementHeights[eleKey] || 0;
                      }
                    } else {
                      for (var _i = itemIndex; _i <= index; _i += 1) {
                        var _eleKey = _this.getIndexKey(_i);

                        itemTop += _this.itemElementHeights[_eleKey] || 0;
                      }
                    }

                    shouldChange = itemTop <= 0 || itemTop >= clientHeight;
                  }

                  if (shouldChange) {
                    // Out of range will fall back to position align
                    mergedAlgin = index < itemIndex ? 'top' : 'bottom';
                  } else {
                    var _getRangeIndex2 = (0, _itemUtil.getRangeIndex)(scrollPtg, data.length, visibleCount),
                        nextIndex = _getRangeIndex2.itemIndex,
                        newOffsetPtg = _getRangeIndex2.itemOffsetPtg,
                        startIndex = _getRangeIndex2.startIndex,
                        endIndex = _getRangeIndex2.endIndex;

                    _this.setState({
                      scrollTop: scrollTop,
                      itemIndex: nextIndex,
                      itemOffsetPtg: newOffsetPtg,
                      startIndex: startIndex,
                      endIndex: endIndex
                    });

                    return;
                  }
                } // Align with position should make scroll happen


                if (mergedAlgin === 'top') {
                  relativeTop = 0;
                } else if (mergedAlgin === 'bottom') {
                  var _eleKey2 = _this.getItemKey(item);

                  relativeTop = clientHeight - _this.itemElementHeights[_eleKey2] || 0;
                }

                _this.internalScrollTo({
                  itemIndex: index,
                  relativeTop: relativeTop
                });
              });
            } else {
              // Raw list without virtual scroll set position directly
              _this.collectItemHeights({
                startIndex: 0,
                endIndex: data.length - 1
              });

              var mergedAlgin = align; // Collection index item position

              var indexItemHeight = _this.itemElementHeights[_this.getIndexKey(index)];

              var itemTop = 0;

              for (var i = 0; i < index; i += 1) {
                var eleKey = _this.getIndexKey(i);

                itemTop += _this.itemElementHeights[eleKey] || 0;
              }

              var itemBottom = itemTop + indexItemHeight;

              if (mergedAlgin === 'auto') {
                if (itemTop < _this.listRef.current.scrollTop) {
                  mergedAlgin = 'top';
                } else if (itemBottom > _this.listRef.current.scrollTop + clientHeight) {
                  mergedAlgin = 'bottom';
                }
              }

              if (mergedAlgin === 'top') {
                _this.listRef.current.scrollTop = itemTop;
              } else if (mergedAlgin === 'bottom') {
                _this.listRef.current.scrollTop = itemTop - (clientHeight - indexItemHeight);
              }
            }
          }
        } else {
          _this.listRef.current.scrollTop = arg0;
        }
      });
    };
    /**
     * Phase 4: Render item and get all the visible items height
     */


    _this.renderChildren = function (list, startIndex, renderFunc) {
      var status = _this.state.status; // We should measure rendered item height

      return list.map(function (item, index) {
        var eleIndex = startIndex + index;
        var node = renderFunc(item, eleIndex, {
          style: status === 'MEASURE_START' ? {
            visibility: 'hidden'
          } : {}
        });

        var eleKey = _this.getIndexKey(eleIndex); // Pass `key` and `ref` for internal measure


        return React.cloneElement(node, {
          key: eleKey,
          ref: function ref(ele) {
            _this.itemElements[eleKey] = ele;
          }
        });
      });
    };

    _this.cachedProps = props;
    _this.state = {
      status: 'NONE',
      scrollTop: null,
      itemIndex: 0,
      itemOffsetPtg: 0,
      startIndex: 0,
      endIndex: 0,
      startItemTop: 0,
      isVirtual: (0, _itemUtil.requireVirtual)(props.height, props.itemHeight, props.data.length, props.virtual),
      itemCount: props.data.length
    };
    return _this;
  }

  _createClass(List, [{
    key: "componentDidMount",

    /**
     * Phase 1: Initial should sync with default scroll top
     */
    value: function componentDidMount() {
      if (this.listRef.current) {
        this.listRef.current.scrollTop = 0;
        this.onScroll(null);
      }
    }
    /**
     * Phase 4: Record used item height
     * Phase 5: Trigger re-render to use correct position
     */

  }, {
    key: "componentDidUpdate",
    value: function componentDidUpdate() {
      var _this2 = this;

      var status = this.state.status;
      var _this$props3 = this.props,
          data = _this$props3.data,
          height = _this$props3.height,
          itemHeight = _this$props3.itemHeight,
          disabled = _this$props3.disabled,
          onSkipRender = _this$props3.onSkipRender,
          virtual = _this$props3.virtual;
      var prevData = this.cachedProps.data || [];
      var changedItemIndex = null;

      if (prevData.length !== data.length) {
        var diff = (0, _algorithmUtil.findListDiffIndex)(prevData, data, this.getItemKey);
        changedItemIndex = diff ? diff.index : null;
      }

      if (disabled) {
        // Should trigger `onSkipRender` to tell that diff component is not render in the list
        if (data.length > prevData.length) {
          var _this$state2 = this.state,
              startIndex = _this$state2.startIndex,
              endIndex = _this$state2.endIndex;

          if (onSkipRender && (changedItemIndex === null || changedItemIndex < startIndex || endIndex < changedItemIndex)) {
            onSkipRender();
          }
        }

        return;
      }

      var isVirtual = (0, _itemUtil.requireVirtual)(height, itemHeight, data.length, virtual);
      var nextStatus = status;

      if (this.state.isVirtual !== isVirtual) {
        nextStatus = isVirtual ? 'SWITCH_TO_VIRTUAL' : 'SWITCH_TO_RAW';
        this.setState({
          isVirtual: isVirtual,
          status: nextStatus
        });
        /**
         * We will wait a tick to let list turn to virtual list.
         * And then use virtual list sync logic to adjust the scroll.
         */

        if (nextStatus === 'SWITCH_TO_VIRTUAL') {
          return;
        }
      }

      if (status === 'MEASURE_START') {
        var _this$state3 = this.state,
            _startIndex = _this$state3.startIndex,
            itemIndex = _this$state3.itemIndex,
            itemOffsetPtg = _this$state3.itemOffsetPtg;
        var scrollTop = this.listRef.current.scrollTop; // Record here since measure item height will get warning in `render`

        this.collectItemHeights(); // Calculate top visible item top offset

        var locatedItemTop = (0, _itemUtil.getItemAbsoluteTop)({
          itemIndex: itemIndex,
          itemOffsetPtg: itemOffsetPtg,
          itemElementHeights: this.itemElementHeights,
          scrollTop: scrollTop,
          scrollPtg: (0, _itemUtil.getElementScrollPercentage)(this.listRef.current),
          clientHeight: this.listRef.current.clientHeight,
          getItemKey: this.getIndexKey
        });
        var startItemTop = locatedItemTop;

        for (var index = itemIndex - 1; index >= _startIndex; index -= 1) {
          startItemTop -= this.itemElementHeights[this.getIndexKey(index)] || 0;
        }

        this.setState({
          status: 'MEASURE_DONE',
          startItemTop: startItemTop
        });
      }

      if (status === 'SWITCH_TO_RAW') {
        /**
         * After virtual list back to raw list,
         * we update the `scrollTop` to real top instead of percentage top.
         */
        var _this$state$cacheScro = this.state.cacheScroll,
            _itemIndex = _this$state$cacheScro.itemIndex,
            relativeTop = _this$state$cacheScro.relativeTop;
        var rawTop = relativeTop;

        for (var _index = 0; _index < _itemIndex; _index += 1) {
          rawTop -= this.itemElementHeights[this.getIndexKey(_index)] || 0;
        }

        this.lockScroll = true;
        this.listRef.current.scrollTop = -rawTop;
        this.setState({
          status: 'MEASURE_DONE',
          itemIndex: 0
        });
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            _this2.lockScroll = false;
          });
        });
      } else if (prevData.length !== data.length && changedItemIndex !== null && height) {
        /**
         * Re-calculate the item position since `data` length changed.
         * [IMPORTANT] We use relative position calculate here.
         */
        var originItemIndex = this.state.itemIndex;
        var _this$state4 = this.state,
            originItemOffsetPtg = _this$state4.itemOffsetPtg,
            originStartIndex = _this$state4.startIndex,
            originEndIndex = _this$state4.endIndex,
            originScrollTop = _this$state4.scrollTop; // 1. Refresh item heights

        this.collectItemHeights(); // 1. Get origin located item top

        var originLocatedItemRelativeTop;

        if (this.state.status === 'SWITCH_TO_VIRTUAL') {
          originItemIndex = 0;
          originLocatedItemRelativeTop = -this.state.scrollTop;
        } else {
          originLocatedItemRelativeTop = (0, _itemUtil.getItemRelativeTop)({
            itemIndex: originItemIndex,
            itemOffsetPtg: originItemOffsetPtg,
            itemElementHeights: this.itemElementHeights,
            scrollPtg: (0, _itemUtil.getScrollPercentage)({
              scrollTop: originScrollTop,
              scrollHeight: prevData.length * itemHeight,
              clientHeight: this.listRef.current.clientHeight
            }),
            clientHeight: this.listRef.current.clientHeight,
            getItemKey: function getItemKey(index) {
              return _this2.getIndexKey(index, _this2.cachedProps);
            }
          });
        } // 2. Find the compare item


        var originCompareItemIndex = changedItemIndex - 1; // Use next one since there are not more item before removed

        if (originCompareItemIndex < 0) {
          originCompareItemIndex = 0;
        } // 3. Find the compare item top


        var originCompareItemTop = (0, _itemUtil.getCompareItemRelativeTop)({
          locatedItemRelativeTop: originLocatedItemRelativeTop,
          locatedItemIndex: originItemIndex,
          compareItemIndex: originCompareItemIndex,
          startIndex: originStartIndex,
          endIndex: originEndIndex,
          getItemKey: function getItemKey(index) {
            return _this2.getIndexKey(index, _this2.cachedProps);
          },
          itemElementHeights: this.itemElementHeights
        });

        if (nextStatus === 'SWITCH_TO_RAW') {
          /**
           * We will record current measure relative item top and apply in raw list after list turned
           */
          this.setState({
            cacheScroll: {
              itemIndex: originCompareItemIndex,
              relativeTop: originCompareItemTop
            }
          });
        } else {
          this.internalScrollTo({
            itemIndex: originCompareItemIndex,
            relativeTop: originCompareItemTop
          });
        }
      } else if (nextStatus === 'SWITCH_TO_RAW') {
        // This is only trigger when height changes that all items can show in raw
        // Let's reset back to top
        this.setState({
          cacheScroll: {
            itemIndex: 0,
            relativeTop: 0
          }
        });
      }

      this.cachedProps = this.props;
    }
  }, {
    key: "internalScrollTo",
    value: function internalScrollTo(relativeScroll) {
      var _this3 = this;

      var compareItemIndex = relativeScroll.itemIndex,
          compareItemRelativeTop = relativeScroll.relativeTop;
      var originScrollTop = this.state.scrollTop;
      var _this$props4 = this.props,
          data = _this$props4.data,
          itemHeight = _this$props4.itemHeight,
          height = _this$props4.height; // 1. Find the best match compare item top

      var bestSimilarity = Number.MAX_VALUE;
      var bestScrollTop = null;
      var bestItemIndex = null;
      var bestItemOffsetPtg = null;
      var bestStartIndex = null;
      var bestEndIndex = null;
      var missSimilarity = 0;
      var scrollHeight = data.length * itemHeight;
      var clientHeight = this.listRef.current.clientHeight;
      var maxScrollTop = scrollHeight - clientHeight;

      for (var i = 0; i < maxScrollTop; i += 1) {
        var scrollTop = (0, _algorithmUtil.getIndexByStartLoc)(0, maxScrollTop, originScrollTop, i);
        var scrollPtg = (0, _itemUtil.getScrollPercentage)({
          scrollTop: scrollTop,
          scrollHeight: scrollHeight,
          clientHeight: clientHeight
        });
        var visibleCount = Math.ceil(height / itemHeight);

        var _getRangeIndex3 = (0, _itemUtil.getRangeIndex)(scrollPtg, data.length, visibleCount),
            itemIndex = _getRangeIndex3.itemIndex,
            itemOffsetPtg = _getRangeIndex3.itemOffsetPtg,
            startIndex = _getRangeIndex3.startIndex,
            endIndex = _getRangeIndex3.endIndex; // No need to check if compare item out of the index to save performance


        if (startIndex <= compareItemIndex && compareItemIndex <= endIndex) {
          // 1.1 Get measure located item relative top
          var locatedItemRelativeTop = (0, _itemUtil.getItemRelativeTop)({
            itemIndex: itemIndex,
            itemOffsetPtg: itemOffsetPtg,
            itemElementHeights: this.itemElementHeights,
            scrollPtg: scrollPtg,
            clientHeight: clientHeight,
            getItemKey: this.getIndexKey
          });
          var compareItemTop = (0, _itemUtil.getCompareItemRelativeTop)({
            locatedItemRelativeTop: locatedItemRelativeTop,
            locatedItemIndex: itemIndex,
            compareItemIndex: compareItemIndex,
            startIndex: startIndex,
            endIndex: endIndex,
            getItemKey: this.getIndexKey,
            itemElementHeights: this.itemElementHeights
          }); // 1.2 Find best match compare item top

          var similarity = Math.abs(compareItemTop - compareItemRelativeTop);

          if (similarity < bestSimilarity) {
            bestSimilarity = similarity;
            bestScrollTop = scrollTop;
            bestItemIndex = itemIndex;
            bestItemOffsetPtg = itemOffsetPtg;
            bestStartIndex = startIndex;
            bestEndIndex = endIndex;
            missSimilarity = 0;
          } else {
            missSimilarity += 1;
          }
        } // If keeping 10 times not match similarity,
        // check more scrollTop is meaningless.
        // Here boundary is set to 10.


        if (missSimilarity > 10) {
          break;
        }
      } // 2. Re-scroll if has best scroll match


      if (bestScrollTop !== null) {
        this.lockScroll = true;
        this.listRef.current.scrollTop = bestScrollTop;
        this.setState({
          status: 'MEASURE_START',
          scrollTop: bestScrollTop,
          itemIndex: bestItemIndex,
          itemOffsetPtg: bestItemOffsetPtg,
          startIndex: bestStartIndex,
          endIndex: bestEndIndex
        });
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            _this3.lockScroll = false;
          });
        });
      }
    }
  }, {
    key: "render",
    value: function render() {
      var _this$state5 = this.state,
          isVirtual = _this$state5.isVirtual,
          itemCount = _this$state5.itemCount;

      var _this$props5 = this.props,
          prefixCls = _this$props5.prefixCls,
          style = _this$props5.style,
          className = _this$props5.className,
          _this$props5$componen = _this$props5.component,
          Component = _this$props5$componen === void 0 ? 'div' : _this$props5$componen,
          height = _this$props5.height,
          itemHeight = _this$props5.itemHeight,
          _this$props5$fullHeig = _this$props5.fullHeight,
          fullHeight = _this$props5$fullHeig === void 0 ? true : _this$props5$fullHeig,
          data = _this$props5.data,
          children = _this$props5.children,
          itemKey = _this$props5.itemKey,
          onSkipRender = _this$props5.onSkipRender,
          disabled = _this$props5.disabled,
          virtual = _this$props5.virtual,
          restProps = _objectWithoutProperties(_this$props5, ["prefixCls", "style", "className", "component", "height", "itemHeight", "fullHeight", "data", "children", "itemKey", "onSkipRender", "disabled", "virtual"]);

      var mergedClassName = (0, _classnames.default)(prefixCls, className); // Render pure list if not set height or height is enough for all items

      if (!isVirtual) {
        /**
         * Virtual list switch is works on component updated.
         * We should double check here if need cut the content.
         */
        var shouldVirtual = (0, _itemUtil.requireVirtual)(height, itemHeight, data.length, virtual);
        return React.createElement(Component, Object.assign({
          style: height ? _objectSpread({}, style, _defineProperty({}, fullHeight ? 'height' : 'maxHeight', height), ScrollStyle) : style,
          className: mergedClassName
        }, restProps, {
          onScroll: this.onRawScroll,
          ref: this.listRef
        }), React.createElement(_Filler.default, {
          prefixCls: prefixCls,
          height: height
        }, this.renderChildren(shouldVirtual ? data.slice(0, Math.ceil(height / itemHeight)) : data, 0, children)));
      } // Use virtual list


      var mergedStyle = _objectSpread({}, style, {
        height: height
      }, ScrollStyle);

      var _this$state6 = this.state,
          status = _this$state6.status,
          startIndex = _this$state6.startIndex,
          endIndex = _this$state6.endIndex,
          startItemTop = _this$state6.startItemTop;
      var contentHeight = itemCount * itemHeight * ITEM_SCALE_RATE;
      return React.createElement(Component, Object.assign({
        style: mergedStyle,
        className: mergedClassName
      }, restProps, {
        onScroll: this.onScroll,
        ref: this.listRef
      }), React.createElement(_Filler.default, {
        prefixCls: prefixCls,
        height: contentHeight,
        offset: status === 'MEASURE_DONE' ? startItemTop : 0
      }, this.renderChildren(data.slice(startIndex, endIndex + 1), startIndex, children)));
    }
  }], [{
    key: "getDerivedStateFromProps",
    value: function getDerivedStateFromProps(nextProps) {
      if (!nextProps.disabled) {
        return {
          itemCount: nextProps.data.length
        };
      }

      return null;
    }
  }]);

  return List;
}(React.Component);

List.defaultProps = {
  itemHeight: 15,
  data: []
};
var _default = List;
exports.default = _default;