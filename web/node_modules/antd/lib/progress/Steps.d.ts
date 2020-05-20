import * as React from 'react';
import { ProgressProps, ProgressSize } from './progress';
interface StepsProps extends ProgressProps {
    steps: number;
    size?: ProgressSize;
}
declare const Steps: React.FC<StepsProps>;
export default Steps;
