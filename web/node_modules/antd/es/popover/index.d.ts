import * as React from 'react';
import { AbstractTooltipProps, TooltipPlacement } from '../tooltip';
import { ConfigConsumerProps } from '../config-provider';
import { RenderFunction } from '../_util/getRenderPropValue';
export interface PopoverProps extends AbstractTooltipProps {
    title?: React.ReactNode | RenderFunction;
    content?: React.ReactNode | RenderFunction;
}
export default class Popover extends React.Component<PopoverProps, {}> {
    static defaultProps: {
        placement: TooltipPlacement;
        transitionName: string;
        trigger: string;
        mouseEnterDelay: number;
        mouseLeaveDelay: number;
        overlayStyle: {};
    };
    private tooltip;
    getPopupDomNode(): any;
    getOverlay(prefixCls: string): JSX.Element;
    saveTooltip: (node: any) => void;
    renderPopover: ({ getPrefixCls }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
