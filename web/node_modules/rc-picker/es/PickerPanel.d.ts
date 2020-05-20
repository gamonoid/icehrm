/**
 * Logic:
 *  When `mode` === `picker`,
 *  click will trigger `onSelect` (if value changed trigger `onChange` also).
 *  Panel change will not trigger `onSelect` but trigger `onPanelChange`
 */
import * as React from 'react';
import { SharedTimeProps } from './panels/TimePanel';
import { GenerateConfig } from './generate';
import { Locale, PanelMode, PickerMode, DisabledTime, OnPanelChange, Components } from './interface';
import { DateRender } from './panels/DatePanel/DateBody';
import { MonthCellRender } from './panels/MonthPanel/MonthBody';
export interface PickerPanelSharedProps<DateType> {
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    /** @deprecated Will be removed in next big version. Please use `mode` instead */
    mode?: PanelMode;
    tabIndex?: number;
    locale: Locale;
    generateConfig: GenerateConfig<DateType>;
    value?: DateType | null;
    defaultValue?: DateType;
    /** [Legacy] Set default display picker view date */
    pickerValue?: DateType;
    /** [Legacy] Set default display picker view date */
    defaultPickerValue?: DateType;
    disabledDate?: (date: DateType) => boolean;
    dateRender?: DateRender<DateType>;
    monthCellRender?: MonthCellRender<DateType>;
    renderExtraFooter?: (mode: PanelMode) => React.ReactNode;
    onSelect?: (value: DateType) => void;
    onChange?: (value: DateType) => void;
    onPanelChange?: OnPanelChange<DateType>;
    onMouseDown?: React.MouseEventHandler<HTMLDivElement>;
    onOk?: (date: DateType) => void;
    direction?: 'ltr' | 'rtl';
    /** @private This is internal usage. Do not use in your production env */
    hideHeader?: boolean;
    /** @private This is internal usage. Do not use in your production env */
    onPickerValueChange?: (date: DateType) => void;
    /** @private Internal usage. Do not use in your production env */
    components?: Components;
}
export interface PickerPanelBaseProps<DateType> extends PickerPanelSharedProps<DateType> {
    picker: Exclude<PickerMode, 'date' | 'time'>;
}
export interface PickerPanelDateProps<DateType> extends PickerPanelSharedProps<DateType> {
    picker?: 'date';
    showToday?: boolean;
    showTime?: boolean | SharedTimeProps<DateType>;
    disabledTime?: DisabledTime<DateType>;
}
export interface PickerPanelTimeProps<DateType> extends PickerPanelSharedProps<DateType>, SharedTimeProps<DateType> {
    picker: 'time';
}
export declare type PickerPanelProps<DateType> = PickerPanelBaseProps<DateType> | PickerPanelDateProps<DateType> | PickerPanelTimeProps<DateType>;
declare function PickerPanel<DateType>(props: PickerPanelProps<DateType>): JSX.Element;
export default PickerPanel;
