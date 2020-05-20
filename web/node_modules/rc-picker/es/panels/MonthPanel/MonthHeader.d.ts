/// <reference types="react" />
import { Locale } from '../../interface';
import { GenerateConfig } from '../../generate';
export interface MonthHeaderProps<DateType> {
    prefixCls: string;
    viewDate: DateType;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    onPrevYear: () => void;
    onNextYear: () => void;
    onYearClick: () => void;
}
declare function MonthHeader<DateType>(props: MonthHeaderProps<DateType>): JSX.Element;
export default MonthHeader;
