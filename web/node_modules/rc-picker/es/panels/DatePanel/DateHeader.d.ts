/// <reference types="react" />
import { Locale } from '../../interface';
import { GenerateConfig } from '../../generate';
export interface DateHeaderProps<DateType> {
    prefixCls: string;
    viewDate: DateType;
    value?: DateType | null;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    onPrevYear: () => void;
    onNextYear: () => void;
    onPrevMonth: () => void;
    onNextMonth: () => void;
    onYearClick: () => void;
    onMonthClick: () => void;
}
declare function DateHeader<DateType>(props: DateHeaderProps<DateType>): JSX.Element;
export default DateHeader;
