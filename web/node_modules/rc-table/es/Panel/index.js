import * as React from 'react';

function Panel(_ref) {
  var className = _ref.className,
      children = _ref.children;
  return React.createElement("div", {
    className: className
  }, children);
}

export default Panel;