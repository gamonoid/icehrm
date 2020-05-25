import * as React from 'react';
import { SizeType } from '../config-provider/SizeContext';
export interface SpaceProps {
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    size?: SizeType | number;
    direction?: 'horizontal' | 'vertical';
}
declare const Space: React.FC<SpaceProps>;
export default Space;
