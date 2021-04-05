import React, { useState, useRef, useEffect } from "react";
import { SketchPicker } from 'react-color';

function useComponentVisible(initialIsVisible) {
  const [isComponentVisible, setIsComponentVisible] = useState(initialIsVisible);
  const ref = useRef(null);

  const handleClickOutside = (event) => {
    if (ref.current && !ref.current.contains(event.target)) {
      setIsComponentVisible(false);
    }
  };

  useEffect(() => {
    document.addEventListener('click', handleClickOutside, true);
    return () => {
      document.removeEventListener('click', handleClickOutside, true);
    };
  });

  return { ref, isComponentVisible, setIsComponentVisible };
}

function IceColorPick(props) {
  const { value, onChange, readOnly } = props;

  const { ref, isComponentVisible, setIsComponentVisible } = useComponentVisible(true);

  const [color, setColor] = useState(value || '#FFF');
  const [showPicker, setShowPicker] = useState(false);

  useEffect(() => {
    if (!isComponentVisible) {
      setShowPicker(false);
    }
  }, [isComponentVisible])

  useEffect(() => {
    if (value) {
      setColor(value);
    }
    
  }, [value]);

  return <div className="colorpicker-container">
    <div
      className="colorpicker-preview"
      onClick={() => {
        if (!showPicker) {
          setIsComponentVisible(true);
        }
        setShowPicker(!showPicker);
      }}
      style={ { backgroundColor: color} }
    />
    <div ref={ref} className={`colorpicker-component ${(readOnly || !showPicker) ? 'hidden': ''}` }>
      <SketchPicker
        color={color}
        disableAlpha
        presetColors={[]}
        onChangeComplete={({ hex }) => {
          onChange(hex);
          setColor(hex);
        }}
      />
    </div>
  </div>;
} 

export default IceColorPick;
