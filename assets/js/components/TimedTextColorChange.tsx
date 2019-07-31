import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';

interface TimedTextColorChangeProps {
  colorClass: string;
  time?: number;
  children?: any;
}
export const TimedTextColorChange = ({ colorClass, time = 1000, children }: TimedTextColorChangeProps) => {
  const [color, setColor] = useState('');
  const updateTimer = useRef(1);

  useEffect(() => {
    if (updateTimer.current) {
      updateTimer.current = 0;
      return;
    }

    (() => {
      setColor(colorClass);
      updateTimer.current = window.setTimeout(() => {
        setColor('');
        updateTimer.current = 0;
      }, time);
    })();

    return () => {
      clearTimeout(updateTimer.current);
    };
  }, [colorClass, children, time]);

  return <span className={color}>{children}</span>;
};

TimedTextColorChange.propTypes = {
  colorClass: PropTypes.string.isRequired,
  time: PropTypes.number,
  children: PropTypes.node,
};
