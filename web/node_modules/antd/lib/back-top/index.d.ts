import * as React from 'react';
import { ConfigConsumerProps } from '../config-provider';
export interface BackTopProps {
    visibilityHeight?: number;
    onClick?: React.MouseEventHandler<HTMLElement>;
    target?: () => HTMLElement | Window | Document;
    prefixCls?: string;
    className?: string;
    style?: React.CSSProperties;
    visible?: boolean;
}
export default class BackTop extends React.Component<BackTopProps, any> {
    static defaultProps: {
        visibilityHeight: number;
    };
    state: {
        visible: boolean;
    };
    scrollEvent: any;
    node: HTMLDivElement;
    componentDidMount(): void;
    componentDidUpdate(prevProps: BackTopProps): void;
    componentWillUnmount(): void;
    bindScrollEvent(): void;
    getVisible(): boolean | undefined;
    getDefaultTarget: () => Document | (Window & typeof globalThis);
    saveDivRef: (node: HTMLDivElement) => void;
    scrollToTop: (e: React.MouseEvent<HTMLDivElement, MouseEvent>) => void;
    handleScroll(e: React.UIEvent<HTMLElement> | {
        target: any;
    }): void;
    renderChildren({ prefixCls }: {
        prefixCls: string;
    }): JSX.Element;
    renderBackTop: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
