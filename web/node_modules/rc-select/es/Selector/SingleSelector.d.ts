import React from 'react';
import { InnerSelectorProps } from '.';
interface SelectorProps extends InnerSelectorProps {
    inputElement: React.ReactElement;
    activeValue: string;
    backfill?: boolean;
}
declare const SingleSelector: React.FC<SelectorProps>;
export default SingleSelector;
