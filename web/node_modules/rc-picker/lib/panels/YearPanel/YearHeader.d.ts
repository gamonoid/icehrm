/// <reference types="react" />
import { GenerateConfig } from '../../generate';
export interface YearHeaderProps<DateType> {
    prefixCls: string;
    viewDate: DateType;
    value?: DateType | null;
    generateConfig: GenerateConfig<DateType>;
    onPrevDecade: () => void;
    onNextDecade: () => void;
    onDecadeClick: () => void;
}
declare function YearHeader<DateType>(props: YearHeaderProps<DateType>): JSX.Element;
export default YearHeader;
