/// <reference types="react" />
import { GenerateConfig } from '../../generate';
export interface YearHeaderProps<DateType> {
    prefixCls: string;
    viewDate: DateType;
    generateConfig: GenerateConfig<DateType>;
    onPrevDecades: () => void;
    onNextDecades: () => void;
}
declare function DecadeHeader<DateType>(props: YearHeaderProps<DateType>): JSX.Element;
export default DecadeHeader;
