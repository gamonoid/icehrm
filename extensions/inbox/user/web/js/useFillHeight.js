import { useEffect, useState } from 'react';

/**
 * Height that runs from the element's top to the bottom of the viewport, minus a
 * gap — measured, not assumed. The shell's header and any banners above the module
 * vary (update notices, switched-profile bar, zoom level), so a fixed
 * `calc(100vh - 64px)` clips the bottom of the page on some screens.
 */
export default function useFillHeight(ref, gap) {
  const [height, setHeight] = useState(null);
  useEffect(() => {
    const measure = () => {
      if (!ref.current) return;
      const top = ref.current.getBoundingClientRect().top;
      setHeight(Math.max(360, window.innerHeight - top - (gap || 16)));
    };
    measure();
    // Once more after layout settles (fonts, banners that mount a beat later).
    const t = setTimeout(measure, 250);
    window.addEventListener('resize', measure);
    return () => { clearTimeout(t); window.removeEventListener('resize', measure); };
  }, [ref, gap]);
  return height;
}
