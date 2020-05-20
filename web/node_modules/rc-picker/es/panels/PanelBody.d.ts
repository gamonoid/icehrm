import * as React from 'react';
export interface PanelBodyProps<DateType> {
    prefixCls: string;
    disabledDate?: (date: DateType) => boolean;
    onSelect: (value: DateType) => void;
    headerCells?: React.ReactNode;
    rowNum: number;
    colNum: number;
    baseDate: DateType;
    getCellClassName: (date: DateType) => Record<string, boolean | undefined>;
    getCellDate: (date: DateType, offset: number) => DateType;
    getCellText: (date: DateType) => React.ReactNode;
    getCellNode?: (date: DateType) => React.ReactNode;
    titleCell?: (date: DateType) => string;
    prefixColumn?: (date: DateType) => React.ReactNode;
    rowClassName?: (date: DateType) => string;
}
export default function PanelBody<DateType>({ prefixCls, disabledDate, onSelect, rowNum, colNum, prefixColumn, rowClassName, baseDate, getCellClassName, getCellText, getCellNode, getCellDate, titleCell, headerCells, }: PanelBodyProps<DateType>): JSX.Element;
