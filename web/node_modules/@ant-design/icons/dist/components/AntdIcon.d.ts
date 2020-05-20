import * as React from 'react';
import { IconDefinition } from '@ant-design/icons-svg/lib/types';
import { IconBaseProps } from './Icon';
import { getTwoToneColor, TwoToneColor, setTwoToneColor } from './twoTonePrimaryColor';
export interface AntdIconProps extends IconBaseProps {
    twoToneColor?: TwoToneColor;
}
export interface IconComponentProps extends AntdIconProps {
    icon: IconDefinition;
}
interface IconBaseComponent<Props> extends React.ForwardRefExoticComponent<Props & React.RefAttributes<HTMLSpanElement>> {
    getTwoToneColor: typeof getTwoToneColor;
    setTwoToneColor: typeof setTwoToneColor;
}
declare const Icon: IconBaseComponent<IconComponentProps>;
export default Icon;
