import React from 'react';
export default function getIcons({ suffixIcon, clearIcon, menuItemSelectedIcon, removeIcon, loading, multiple, }: {
    suffixIcon?: React.ReactNode;
    clearIcon?: React.ReactNode;
    menuItemSelectedIcon?: React.ReactNode;
    removeIcon?: React.ReactNode;
    loading?: boolean;
    multiple?: boolean;
}): {
    clearIcon: React.ReactNode;
    suffixIcon: {} | null;
    itemIcon: {} | null;
    removeIcon: {} | null;
};
