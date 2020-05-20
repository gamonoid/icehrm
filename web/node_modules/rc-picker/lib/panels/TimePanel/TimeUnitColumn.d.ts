import * as React from 'react';
export interface Unit {
    label: React.ReactText;
    value: number;
    disabled?: boolean;
}
export interface TimeUnitColumnProps {
    prefixCls?: string;
    units?: Unit[];
    value?: number;
    active?: boolean;
    hideDisabledOptions?: boolean;
    onSelect?: (value: number) => void;
}
declare function TimeUnitColumn(props: TimeUnitColumnProps): JSX.Element;
export default TimeUnitColumn;
