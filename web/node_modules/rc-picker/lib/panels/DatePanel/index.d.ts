/// <reference types="react" />
import { DateBodyPassProps, DateRender } from './DateBody';
import { PanelSharedProps } from '../../interface';
import { KeyboardConfig } from '../../utils/uiUtil';
export interface DatePanelProps<DateType> extends PanelSharedProps<DateType>, DateBodyPassProps<DateType> {
    active?: boolean;
    dateRender?: DateRender<DateType>;
    panelName?: string;
    keyboardConfig?: KeyboardConfig;
}
declare function DatePanel<DateType>(props: DatePanelProps<DateType>): JSX.Element;
export default DatePanel;
