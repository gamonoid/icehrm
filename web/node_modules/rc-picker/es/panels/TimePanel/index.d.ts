/// <reference types="react" />
import { PanelSharedProps, DisabledTimes } from '../../interface';
export interface SharedTimeProps<DateType> extends DisabledTimes {
    format?: string;
    showHour?: boolean;
    showMinute?: boolean;
    showSecond?: boolean;
    use12Hours?: boolean;
    hourStep?: number;
    minuteStep?: number;
    secondStep?: number;
    hideDisabledOptions?: boolean;
    defaultValue?: DateType;
}
export interface TimePanelProps<DateType> extends PanelSharedProps<DateType>, SharedTimeProps<DateType> {
    format?: string;
    active?: boolean;
}
declare function TimePanel<DateType>(props: TimePanelProps<DateType>): JSX.Element;
export default TimePanel;
