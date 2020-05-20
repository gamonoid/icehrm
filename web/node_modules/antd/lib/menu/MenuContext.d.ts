/// <reference types="react" />
export declare type MenuTheme = 'light' | 'dark';
export interface MenuContextProps {
    inlineCollapsed: boolean;
    antdMenuTheme?: MenuTheme;
    direction?: 'ltr' | 'rtl';
}
declare const MenuContext: import("react").Context<MenuContextProps>;
export default MenuContext;
