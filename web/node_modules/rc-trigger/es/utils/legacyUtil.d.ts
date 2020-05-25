import { MotionType, AnimationType, TransitionNameType } from '../interface';
interface GetMotionProps {
    motion: MotionType;
    animation: AnimationType;
    transitionName: TransitionNameType;
    prefixCls: string;
}
export declare function getMotion({ prefixCls, motion, animation, transitionName, }: GetMotionProps): MotionType;
export {};
