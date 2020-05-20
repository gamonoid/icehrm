import React from 'react';
export declare type RenderFunction = () => React.ReactNode;
export declare const getRenderPropValue: (propValue?: string | number | boolean | {} | React.ReactElement<any, string | ((props: any) => React.ReactElement<any, string | any | (new (props: any) => React.Component<any, any, any>)> | null) | (new (props: any) => React.Component<any, any, any>)> | React.ReactNodeArray | React.ReactPortal | RenderFunction | null | undefined) => React.ReactNode;
