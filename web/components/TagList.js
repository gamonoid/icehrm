import React from 'react';
import {Skeleton, Tag} from 'antd';

class TagList extends React.Component {
  state = {
    tags: [],
    loading: true,
  }

  constructor(props) {
    super(props);
  }

  componentDidMount() {
    this.fetch();
  }

  componentDidUpdate(prevProps, prevState, snapshot) {
    if (prevProps.url !== this.props.url) {
      this.fetch();
    }
  }

  fetch() {
    this.setState({
      loading: true,
    });
    this.props.apiClient
      .get(this.props.url)
      .then((response) => {
        const tags = response.data.data.map(this.props.extractTag);
        this.setState({
          tags: tags,
          loading: false,
        });
      });
  }

  render() {

    return (
      <div style={{
        display: 'inline-block',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        width: '100%',
      }}>
        {this.state.loading &&
        <Skeleton active={true}/>
        }
        {!this.state.loading && this.state.tags.map((tag, index) =>
          this.props.render ? this.props.render(tag) : <div key={`p${index}`}><Tag color={this.props.color} key={index} style={{margin: '10px'}}>{tag}</Tag><br/></div>
        )}
      </div>
    );
  }

}

export default TagList;
