/// <reference types="react" />
import OriginList from './List';
declare class List<T> extends OriginList<T> {
    componentDidUpdate(): void;
    scrollTo: () => void;
    render(): JSX.Element;
}
export default List;
