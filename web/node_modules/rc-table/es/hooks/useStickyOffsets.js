import { useMemo } from 'react';
/**
 * Get sticky column offset width
 */

function useStickyOffsets(colWidths, columCount, direction) {
  var stickyOffsets = useMemo(function () {
    var leftOffsets = [];
    var rightOffsets = [];
    var left = 0;
    var right = 0;

    for (var start = 0; start < columCount; start += 1) {
      if (direction === 'rtl') {
        // Left offset
        rightOffsets[start] = right;
        right += colWidths[start] || 0; // Right offset

        var end = columCount - start - 1;
        leftOffsets[end] = left;
        left += colWidths[end] || 0;
      } else {
        // Left offset
        leftOffsets[start] = left;
        left += colWidths[start] || 0; // Right offset

        var _end = columCount - start - 1;

        rightOffsets[_end] = right;
        right += colWidths[_end] || 0;
      }
    }

    return {
      left: leftOffsets,
      right: rightOffsets
    };
  }, [colWidths, columCount, direction]);
  return stickyOffsets;
}

export default useStickyOffsets;