/// <reference types="react" />
import { PanelMode, PickerMode } from '../interface';
export declare function scrollTo(element: HTMLElement, to: number, duration: number): void;
export interface KeyboardConfig {
    onLeftRight?: ((diff: number) => void) | null;
    onCtrlLeftRight?: ((diff: number) => void) | null;
    onUpDown?: ((diff: number) => void) | null;
    onPageUpDown?: ((diff: number) => void) | null;
    onEnter?: (() => void) | null;
}
export declare function createKeyDownHandler(event: React.KeyboardEvent<HTMLElement>, { onLeftRight, onCtrlLeftRight, onUpDown, onPageUpDown, onEnter }: KeyboardConfig): boolean;
export declare function getDefaultFormat(format: string | string[] | undefined, picker: PickerMode | undefined, showTime: boolean | object | undefined, use12Hours: boolean | undefined): string | string[];
export declare function getInputSize(picker: PickerMode | undefined, format: string): number;
declare type ClickEventHandler = (event: MouseEvent) => void;
export declare function addGlobalMouseDownEvent(callback: ClickEventHandler): () => void;
export declare const PickerModeMap: Record<PickerMode, ((next: PanelMode) => PanelMode) | null>;
export declare function elementsContains(elements: (HTMLElement | undefined | null)[], target: HTMLElement): boolean;
export {};
