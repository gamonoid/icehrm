/// <reference types="react" />
import { MonthCellRender } from './MonthBody';
import { PanelSharedProps } from '../../interface';
export interface MonthPanelProps<DateType> extends PanelSharedProps<DateType> {
    monthCellContentRender?: MonthCellRender<DateType>;
}
declare function MonthPanel<DateType>(props: MonthPanelProps<DateType>): JSX.Element;
export default MonthPanel;
