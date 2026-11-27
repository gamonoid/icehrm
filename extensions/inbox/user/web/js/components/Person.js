import React from 'react';
import { Avatar, Typography } from 'antd';
import { UserOutlined } from '@ant-design/icons';

const { Text } = Typography;

export function PersonAvatar({ person, size }) {
  return (
    <Avatar size={size || 32} src={person && person.image ? person.image : undefined} icon={<UserOutlined />}>
      {person && person.name ? person.name.charAt(0).toUpperCase() : null}
    </Avatar>
  );
}

/** Avatar + name with the job title in small type beneath. */
export default function Person({ person, size, nameStyle, muted }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0 }}>
      <PersonAvatar person={person} size={size || 34} />
      <div style={{ minWidth: 0, lineHeight: 1.25 }}>
        <div style={{ fontWeight: 600, fontSize: 14, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', ...(nameStyle || {}) }}>
          {person ? person.name : ''}
        </div>
        {person && person.job_title ? (
          <Text type={muted ? undefined : 'secondary'} style={{ fontSize: 11, display: 'block', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', ...(muted ? { color: 'inherit', opacity: 0.8 } : {}) }}>
            {person.job_title}
          </Text>
        ) : null}
      </div>
    </div>
  );
}
