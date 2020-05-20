import * as React from 'react';
export default function usePickerInput({ open, isClickOutside, triggerOpen, forwardKeyDown, blurToCancel, onSubmit, onCancel, onFocus, onBlur, }: {
    open: boolean;
    isClickOutside: (clickElement: EventTarget | null) => boolean;
    triggerOpen: (open: boolean) => void;
    forwardKeyDown: (e: React.KeyboardEvent<HTMLInputElement>) => boolean;
    blurToCancel?: boolean;
    onSubmit: () => void | boolean;
    onCancel: () => void;
    onFocus?: React.FocusEventHandler<HTMLInputElement>;
    onBlur?: React.FocusEventHandler<HTMLInputElement>;
}): [React.DOMAttributes<HTMLInputElement>, {
    focused: boolean;
    typing: boolean;
}];
