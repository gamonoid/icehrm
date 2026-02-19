import React from 'react';
import { Empty } from 'antd';

class KudosAdminExtensionView extends React.Component {
  render() {
    return (
      <Empty
        description={(
          <span>
            Stating point to build an awesome IceHrm extension.
          </span>
        )}
      />
    );
  }
}

export default KudosAdminExtensionView;
