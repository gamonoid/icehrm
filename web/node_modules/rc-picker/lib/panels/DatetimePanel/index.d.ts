/// <reference types="react" />
import { DatePanelProps } from '../DatePanel';
import { SharedTimeProps } from '../TimePanel';
import { DisabledTime } from '../../interface';
export interface DatetimePanelProps<DateType> extends Omit<DatePanelProps<DateType>, 'disabledHours' | 'disabledMinutes' | 'disabledSeconds'> {
    disabledTime?: DisabledTime<DateType>;
    showTime?: boolean | SharedTimeProps<DateType>;
    defaultValue?: DateType;
}
declare function DatetimePanel<DateType>(props: DatetimePanelProps<DateType>): JSX.Element;
export default DatetimePanel;
