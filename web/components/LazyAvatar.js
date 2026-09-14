import React from 'react';
import { Avatar } from 'antd';

/**
 * Avatar that only loads its image once it scrolls near the viewport, so long
 * (infinitely scrolled) people lists don't fetch every profile image up front.
 * Shows the person's initial as the placeholder until then.
 */
class LazyAvatar extends React.Component {
  state = { visible: false };

  holderRef = React.createRef();

  componentDidMount() {
    if (typeof IntersectionObserver === 'undefined') {
      this.setState({ visible: true });
      return;
    }
    this.observer = new IntersectionObserver((entries) => {
      if (entries.some((e) => e.isIntersecting)) {
        this.setState({ visible: true });
        this.observer.disconnect();
        this.observer = null;
      }
    }, { rootMargin: '200px' });
    if (this.holderRef.current) {
      this.observer.observe(this.holderRef.current);
    }
  }

  componentWillUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  }

  render() {
    const { src, name } = this.props;
    return (
      <span ref={this.holderRef} style={{ display: 'inline-block' }}>
        <Avatar src={this.state.visible && src ? src : undefined}>
          {(name || ' ').trim().charAt(0).toUpperCase()}
        </Avatar>
      </span>
    );
  }
}

export default LazyAvatar;
