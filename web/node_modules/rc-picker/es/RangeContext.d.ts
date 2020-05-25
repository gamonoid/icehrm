import * as React from 'react';
import { NullableDateType, RangeValue } from './interface';
export interface RangeContextProps {
    /**
     * Set displayed range value style.
     * Panel only has one value, this is only style effect.
     */
    rangedValue?: [NullableDateType<any>, NullableDateType<any>] | null;
    hoverRangedValue?: RangeValue<any>;
    inRange?: boolean;
    panelPosition?: 'left' | 'right' | false;
}
declare const RangeContext: React.Context<RangeContextProps>;
export default RangeContext;
