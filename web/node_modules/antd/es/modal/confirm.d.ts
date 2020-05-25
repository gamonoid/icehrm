import { ModalFuncProps } from './Modal';
export declare type ModalFunc = (props: ModalFuncProps) => {
    destroy: () => void;
    update: (newConfig: ModalFuncProps) => void;
};
export interface ModalStaticFunctions {
    info: ModalFunc;
    success: ModalFunc;
    error: ModalFunc;
    warn: ModalFunc;
    warning: ModalFunc;
    confirm: ModalFunc;
}
export default function confirm(config: ModalFuncProps): {
    destroy: (...args: any[]) => void;
    update: (newConfig: ModalFuncProps) => void;
};
export declare function withWarn(props: ModalFuncProps): ModalFuncProps;
export declare function withInfo(props: ModalFuncProps): ModalFuncProps;
export declare function withSuccess(props: ModalFuncProps): ModalFuncProps;
export declare function withError(props: ModalFuncProps): ModalFuncProps;
export declare function withConfirm(props: ModalFuncProps): ModalFuncProps;
