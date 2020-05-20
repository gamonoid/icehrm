import * as React from 'react';
import { UploadListProps, UploadFile, UploadListType } from './interface';
import { previewImage } from './utils';
import { ConfigConsumerProps } from '../config-provider';
export default class UploadList extends React.Component<UploadListProps, any> {
    static defaultProps: {
        listType: UploadListType;
        progressAttr: {
            strokeWidth: number;
            showInfo: boolean;
        };
        showRemoveIcon: boolean;
        showDownloadIcon: boolean;
        showPreviewIcon: boolean;
        previewFile: typeof previewImage;
    };
    componentDidUpdate(): void;
    handlePreview: (file: UploadFile<any>, e: React.SyntheticEvent<HTMLElement, Event>) => void;
    handleDownload: (file: UploadFile<any>) => void;
    handleClose: (file: UploadFile<any>) => void;
    handleIconRender: (file: UploadFile<any>) => {} | null | undefined;
    handleActionIconRender: (customIcon: React.ReactNode, callback: () => void, title?: string | undefined) => JSX.Element;
    renderUploadList: ({ getPrefixCls, direction }: ConfigConsumerProps) => JSX.Element;
    render(): JSX.Element;
}
