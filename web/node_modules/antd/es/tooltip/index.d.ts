import * as React from 'react';
import { TooltipProps as RcTooltipProps } from 'rc-tooltip/lib/Tooltip';
import { BuildInPlacements } from 'rc-trigger/lib/interface';
import { AdjustOverflow, PlacementsConfig } from './placements';
import { ConfigConsumerProps } from '../config-provider';
export { AdjustOverflow, PlacementsConfig };
export declare type TooltipPlacement = 'top' | 'left' | 'right' | 'bottom' | 'topLeft' | 'topRight' | 'bottomLeft' | 'bottomRight' | 'leftTop' | 'leftBottom' | 'rightTop' | 'rightBottom';
export interface TooltipAlignConfig {
    points?: [string, string];
    offset?: [number | string, number | string];
    targetOffset?: [number | string, number | string];
    overflow?: {
        adjustX: boolean;
        adjustY: boolean;
    };
    useCssRight?: boolean;
    useCssBottom?: boolean;
    useCssTransform?: boolean;
}
export interface AbstractTooltipProps extends Partial<RcTooltipProps> {
    style?: React.CSSProperties;
    className?: string;
    placement?: TooltipPlacement;
    builtinPlacements?: BuildInPlacements;
    openClassName?: string;
    arrowPointAtCenter?: boolean;
    autoAdjustOverflow?: boolean | AdjustOverflow;
    getPopupContainer?: (triggerNode: HTMLElement) => HTMLElement;
}
export declare type RenderFunction = () => React.ReactNode;
export interface TooltipPropsWithOverlay extends AbstractTooltipProps {
    title?: React.ReactNode | RenderFunction;
    overlay: React.ReactNode | RenderFunction;
}
export interface TooltipPropsWithTitle extends AbstractTooltipProps {
    title: React.ReactNode | RenderFunction;
    overlay?: React.ReactNode | RenderFunction;
}
export declare type TooltipProps = TooltipPropsWithTitle | TooltipPropsWithOverlay;
declare class Tooltip extends React.Component<TooltipProps, any> {
    static defaultProps: {
        placement: TooltipPlacement;
        transitionName: string;
        mouseEnterDelay: number;
        mouseLeaveDelay: number;
        arrowPointAtCenter: boolean;
        autoAdjustOverflow: boolean;
    };
    static getDerivedStateFromProps(nextProps: TooltipProps): {
        visible: boolean | undefined;
    } | null;
    private tooltip;
    constructor(props: TooltipProps);
    onVisibleChange: (visible: boolean) => void;
    getPopupDomNode(): any;
    getPlacements(): BuildInPlacements;
    saveTooltip: (node: React.ForwardRefExoticComponent<RcTooltipProps & React.RefAttributes<unknown>>) => void;
    onPopupAlign: (domNode: HTMLElement, align: any) => void;
    isNoTitle(): boolean;
    getOverlay(): {};
    renderTooltip: ({ getPopupContainer: getContextPopupContainer, getPrefixCls, direction, }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
export default Tooltip;
