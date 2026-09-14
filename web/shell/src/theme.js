// Material-UI-inspired theme for the antd-based shell: Roboto, the Material
// colour palette, Material "elevation" shadows and radii. Applied via antd's
// ConfigProvider so every component picks it up. Supports a light and a dark
// colour mode — buildTheme(mode) returns the ConfigProvider theme for either,
// switching antd's algorithm and the surface/background tokens.

import { theme as antdTheme } from 'antd';

export const MUI = {
  primary: '#1976d2',
  primaryDark: '#1565c0',
  success: '#2e7d32',
  warning: '#ed6c02',
  error: '#d32f2f',
  info: '#0288d1',
  bg: '#f4f6f8',
  paper: '#ffffff',
  text: 'rgba(0, 0, 0, 0.87)',
  textSecondary: 'rgba(0, 0, 0, 0.6)',
  divider: 'rgba(0, 0, 0, 0.12)',
};

// Dark-mode surface palette (Material dark: near-black layout, raised papers).
export const MUI_DARK = {
  bg: '#0f141b', // app/layout background (behind cards)
  paper: '#1a212b', // card / container surface
  elevated: '#222b36', // popovers, dropdowns, modals
  text: 'rgba(255, 255, 255, 0.92)',
  textSecondary: 'rgba(255, 255, 255, 0.78)',
  border: '#2a3441',
};

// The sidebar uses a fixed dark navy in BOTH modes (it reads as intentional
// chrome rather than following the body) — exported so the shell can reuse it.
export const SIDEBAR_BG = '#1a2233';

// Material elevation-1 style card shadow.
export const MUI_SHADOW = '0 2px 1px -1px rgba(0,0,0,0.08), 0 1px 3px 0 rgba(0,0,0,0.12), 0 1px 1px 0 rgba(0,0,0,0.06)';
// Slightly stronger (hover / elevation-3).
export const MUI_SHADOW_HOVER = '0 3px 5px -1px rgba(0,0,0,0.12), 0 5px 8px 0 rgba(0,0,0,0.08), 0 1px 14px 0 rgba(0,0,0,0.06)';

const FONT = '"Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif';

// Build the antd ConfigProvider theme for a colour mode ('light' | 'dark').
export function buildTheme(mode) {
  const dark = mode === 'dark';
  return {
    algorithm: dark ? antdTheme.darkAlgorithm : antdTheme.defaultAlgorithm,
    token: {
      colorPrimary: MUI.primary,
      colorInfo: MUI.primary,
      colorSuccess: MUI.success,
      colorWarning: MUI.warning,
      colorError: MUI.error,
      colorLink: MUI.primary,
      fontFamily: FONT,
      fontSize: 14,
      borderRadius: 8,
      wireframe: false,
      ...(dark
        ? {
          colorBgLayout: MUI_DARK.bg,
          colorBgContainer: MUI_DARK.paper,
          colorBgElevated: MUI_DARK.elevated,
          colorText: MUI_DARK.text,
          colorTextHeading: MUI_DARK.text,
          colorTextSecondary: MUI_DARK.textSecondary,
          colorBorderSecondary: MUI_DARK.border,
        }
        : {
          colorBgLayout: MUI.bg,
          colorText: MUI.text,
          colorTextHeading: MUI.text,
          colorTextSecondary: MUI.textSecondary,
          colorBorderSecondary: '#eceff1',
        }),
    },
    components: {
      Card: { borderRadiusLG: 12, paddingLG: 20 },
      Button: {
        fontWeight: 500,
        primaryShadow: 'none',
        defaultShadow: 'none',
        controlHeight: 36,
      },
      Table: dark
        ? {
          headerBg: '#222b36',
          headerSplitColor: 'transparent',
          rowHoverBg: '#222b36',
          cellPaddingBlock: 14,
        }
        : {
          headerBg: '#f7f9fb',
          headerColor: MUI.textSecondary,
          headerSplitColor: 'transparent',
          borderColor: '#eceff1',
          rowHoverBg: '#f3f6f9',
          cellPaddingBlock: 14,
        },
      Tabs: {
        itemSelectedColor: MUI.primary,
        inkBarColor: MUI.primary,
        titleFontSize: 14,
        horizontalItemGutter: 28,
      },
      Layout: {
        headerBg: dark ? MUI_DARK.paper : '#ffffff',
        bodyBg: dark ? MUI_DARK.bg : MUI.bg,
        headerHeight: 64,
      },
      Input: { controlHeight: 36 },
      Select: { controlHeight: 36 },
      // The sidebar Menu stays dark-themed in both modes.
      Menu: {
        darkItemBg: SIDEBAR_BG,
        darkSubMenuItemBg: '#161d2b',
        darkItemSelectedBg: MUI.primary,
        darkItemHoverBg: 'rgba(255,255,255,0.08)',
        itemBorderRadius: 8,
      },
    },
  };
}

// Backwards-compatible default (light) theme object.
export const MUI_THEME = buildTheme('light');
