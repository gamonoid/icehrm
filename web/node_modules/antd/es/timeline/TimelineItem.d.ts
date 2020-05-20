import * as React from 'react';
export interface TimeLineItemProps {
    prefixCls?: string;
    className?: string;
    color?: string;
    dot?: React.ReactNode;
    pending?: boolean;
    position?: string;
    style?: React.CSSProperties;
    label?: React.ReactNode;
}
declare const TimelineItem: React.FC<TimeLineItemProps>;
export default TimelineItem;
