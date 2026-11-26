import React from 'react';
import { Card, List, Tag, Typography, Badge, Empty } from 'antd';
import { CheckCircleOutlined, RightOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { followIntent, hasDestination } from '../intent';
import { ACCENT, chip, soft } from '../palette';
import { t } from '../../../../api/i18n';

const { Text } = Typography;

/**
 * Pending tasks (formerly "Needs you") — the cross-module action-item inbox ("Act"). Items come from the
 * server-side registry (Classes\HomeService::actionItems) already scoped to the
 * caller: admins see company-wide approvals, employees only their own work.
 */
function NeedsYou({ data }) {
  const items = (data && data.actionItems) || [];
  return (
    <Card
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.needsYou)}><CheckCircleOutlined /></span>
          {t('Pending tasks')}
          {items.length > 0 && (
            <Badge
              count={items.length}
              style={{ background: soft(ACCENT.needsYou, 0.9), color: '#fff' }}
            />
          )}
        </span>
      )}
      extra={<Text type="secondary" style={{ fontSize: 12 }}>{t('from every module')}</Text>}
      size="small"
      style={{ height: '100%' }}
    >
      {items.length === 0 ? (
        <Empty
          image={<CheckCircleOutlined style={{ fontSize: 34, color: '#52c41a' }} />}
          description={t("You're all caught up")}
        />
      ) : (
        <List
          size="small"
          dataSource={items}
          renderItem={(item) => (
            <List.Item
              style={{
                cursor: hasDestination(item.intent) ? 'pointer' : 'default',
                paddingLeft: 10,
                paddingRight: 6,
                borderLeft: `3px solid ${item.priority === 'high' ? '#f5222d' : ACCENT.needsYou}`,
                borderRadius: 6,
                marginBottom: 4,
                background: soft(item.priority === 'high' ? '#f5222d' : ACCENT.needsYou, 0.07),
              }}
              onClick={() => followIntent(item.intent)}
              actions={[
                item.count > 1
                  ? <Badge count={item.count} key="c" style={{ background: ACCENT.needsYou }} />
                  : null,
                item.priority === 'high' ? <Tag color="red" key="p">{t('urgent')}</Tag> : null,
                hasDestination(item.intent)
                  ? <RightOutlined key="go" style={{ color: '#999' }} />
                  : null,
              ].filter(Boolean)}
            >
              <List.Item.Meta
                title={<span style={{ fontWeight: 600 }}>{item.label}</span>}
                description={item.details || null}
              />
            </List.Item>
          )}
        />
      )}
    </Card>
  );
}

registerWidget({
  id: 'needs-you', order: 20, column: 'side', size: 'full', component: NeedsYou,
});

export default NeedsYou;
