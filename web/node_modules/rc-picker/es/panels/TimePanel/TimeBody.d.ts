import * as React from 'react';
import { GenerateConfig } from '../../generate';
import { Locale, OnSelect } from '../../interface';
import { SharedTimeProps } from '.';
export interface BodyOperationRef {
    onUpDown: (diff: number) => void;
}
export interface TimeBodyProps<DateType> extends SharedTimeProps<DateType> {
    prefixCls: string;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    value?: DateType | null;
    onSelect: OnSelect<DateType>;
    activeColumnIndex: number;
    operationRef: React.MutableRefObject<BodyOperationRef | undefined>;
}
declare function TimeBody<DateType>(props: TimeBodyProps<DateType>): JSX.Element;
export default TimeBody;
