import * as React from 'react';
import { GenerateConfig } from '../../generate';
import { Locale } from '../../interface';
export declare type DateRender<DateType> = (currentDate: DateType, today: DateType) => React.ReactNode;
export interface DateBodyPassProps<DateType> {
    dateRender?: DateRender<DateType>;
    disabledDate?: (date: DateType) => boolean;
    prefixColumn?: (date: DateType) => React.ReactNode;
    rowClassName?: (date: DateType) => string;
}
export interface DateBodyProps<DateType> extends DateBodyPassProps<DateType> {
    prefixCls: string;
    generateConfig: GenerateConfig<DateType>;
    value?: DateType | null;
    viewDate: DateType;
    locale: Locale;
    rowCount: number;
    onSelect: (value: DateType) => void;
}
declare function DateBody<DateType>(props: DateBodyProps<DateType>): JSX.Element;
export default DateBody;
