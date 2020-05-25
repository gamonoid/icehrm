function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

import findDOMNode from "rc-util/es/Dom/findDOMNode";
/**
 * Our algorithm have additional one ghost item
 * whose index as `data.length` to simplify the calculation
 */

export var GHOST_ITEM_KEY = '__rc_ghost_item__';
/**
 * Get location item and its align percentage with the scroll percentage.
 * We should measure current scroll position to decide which item is the location item.
 * And then fill the top count and bottom count with the base of location item.
 *
 * `total` should be the real count instead of `total - 1` in calculation.
 */

function getLocationItem(scrollPtg, total) {
  var itemIndex = Math.floor(scrollPtg * total);
  var itemTopPtg = itemIndex / total;
  var itemBottomPtg = (itemIndex + 1) / total;
  var itemOffsetPtg = (scrollPtg - itemTopPtg) / (itemBottomPtg - itemTopPtg);
  return {
    index: itemIndex,
    offsetPtg: itemOffsetPtg
  };
}
/**
 * Safari has the elasticity effect which provides negative `scrollTop` value.
 * We should ignore it since will make scroll animation shake.
 */


export function alignScrollTop(scrollTop, scrollRange) {
  if (scrollTop < 0) {
    return 0;
  }

  if (scrollTop >= scrollRange) {
    return scrollRange;
  }

  return scrollTop;
}
export function getScrollPercentage(_ref) {
  var scrollTop = _ref.scrollTop,
      scrollHeight = _ref.scrollHeight,
      clientHeight = _ref.clientHeight;

  if (scrollHeight <= clientHeight) {
    return 0;
  }

  var scrollRange = scrollHeight - clientHeight;
  var alignedScrollTop = alignScrollTop(scrollTop, scrollRange);
  var scrollTopPtg = alignedScrollTop / scrollRange;
  return scrollTopPtg;
}
export function getElementScrollPercentage(element) {
  if (!element) {
    return 0;
  }

  return getScrollPercentage(element);
}
/**
 * Get node `offsetHeight`. We prefer node is a dom element directly.
 * But if not provided, downgrade to `findDOMNode` to get the real dom element.
 */

export function getNodeHeight(node) {
  var element = findDOMNode(node);
  return element ? element.offsetHeight : 0;
}
/**
 * Get display items start, end, located item index. This is pure math calculation
 */

export function getRangeIndex(scrollPtg, itemCount, visibleCount) {
  var _getLocationItem = getLocationItem(scrollPtg, itemCount),
      index = _getLocationItem.index,
      offsetPtg = _getLocationItem.offsetPtg;

  var beforeCount = Math.ceil(scrollPtg * visibleCount);
  var afterCount = Math.ceil((1 - scrollPtg) * visibleCount);
  return {
    itemIndex: index,
    itemOffsetPtg: offsetPtg,
    startIndex: Math.max(0, index - beforeCount),
    endIndex: Math.min(itemCount - 1, index + afterCount)
  };
}
/**
 * Calculate the located item related top with current window height
 */

export function getItemRelativeTop(_ref2) {
  var itemIndex = _ref2.itemIndex,
      itemOffsetPtg = _ref2.itemOffsetPtg,
      itemElementHeights = _ref2.itemElementHeights,
      scrollPtg = _ref2.scrollPtg,
      clientHeight = _ref2.clientHeight,
      getItemKey = _ref2.getItemKey;
  var locatedItemHeight = itemElementHeights[getItemKey(itemIndex)] || 0;
  var locatedItemTop = scrollPtg * clientHeight;
  var locatedItemOffset = itemOffsetPtg * locatedItemHeight;
  return Math.floor(locatedItemTop - locatedItemOffset);
}
/**
 * Calculate the located item absolute top with whole scroll height
 */

export function getItemAbsoluteTop(_ref3) {
  var scrollTop = _ref3.scrollTop,
      rest = _objectWithoutProperties(_ref3, ["scrollTop"]);

  return scrollTop + getItemRelativeTop(rest);
}
export function getCompareItemRelativeTop(_ref4) {
  var locatedItemRelativeTop = _ref4.locatedItemRelativeTop,
      locatedItemIndex = _ref4.locatedItemIndex,
      compareItemIndex = _ref4.compareItemIndex,
      startIndex = _ref4.startIndex,
      endIndex = _ref4.endIndex,
      getItemKey = _ref4.getItemKey,
      itemElementHeights = _ref4.itemElementHeights;
  var originCompareItemTop = locatedItemRelativeTop;
  var compareItemKey = getItemKey(compareItemIndex);

  if (compareItemIndex <= locatedItemIndex) {
    for (var index = locatedItemIndex; index >= startIndex; index -= 1) {
      var key = getItemKey(index);

      if (key === compareItemKey) {
        break;
      }

      var prevItemKey = getItemKey(index - 1);
      originCompareItemTop -= itemElementHeights[prevItemKey] || 0;
    }
  } else {
    for (var _index = locatedItemIndex; _index <= endIndex; _index += 1) {
      var _key = getItemKey(_index);

      if (_key === compareItemKey) {
        break;
      }

      originCompareItemTop += itemElementHeights[_key] || 0;
    }
  }

  return originCompareItemTop;
}
export function requireVirtual(height, itemHeight, count, virtual) {
  return virtual !== false && typeof height === 'number' && count * itemHeight > height;
}