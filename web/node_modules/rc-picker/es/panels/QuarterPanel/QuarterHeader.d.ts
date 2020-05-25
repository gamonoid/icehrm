/// <reference types="react" />
import { Locale } from '../../interface';
import { GenerateConfig } from '../../generate';
export interface QuarterHeaderProps<DateType> {
    prefixCls: string;
    viewDate: DateType;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    onPrevYear: () => void;
    onNextYear: () => void;
    onYearClick: () => void;
}
declare function QuarterHeader<DateType>(props: QuarterHeaderProps<DateType>): JSX.Element;
export default QuarterHeader;
