/// <reference types="react" />
import { Locale } from '../../interface';
import { GenerateConfig } from '../../generate';
export interface TimeHeaderProps<DateType> {
    prefixCls: string;
    value?: DateType | null;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    format: string;
}
declare function TimeHeader<DateType>(props: TimeHeaderProps<DateType>): JSX.Element;
export default TimeHeader;
