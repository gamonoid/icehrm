/// <reference types="react" />
import { GenerateConfig } from '../../generate';
import { Locale } from '../../interface';
export declare const QUARTER_COL_COUNT = 4;
export interface QuarterBodyProps<DateType> {
    prefixCls: string;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    value?: DateType | null;
    viewDate: DateType;
    disabledDate?: (date: DateType) => boolean;
    onSelect: (value: DateType) => void;
}
declare function QuarterBody<DateType>(props: QuarterBodyProps<DateType>): JSX.Element;
export default QuarterBody;
