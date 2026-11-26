import React from 'react';
import { Card, List, Avatar, Button, Typography, Empty, Badge, Tag } from 'antd';
import { GiftOutlined, StarOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { ACCENT, chip, personColor, initials, soft } from '../palette';
import { t, tp } from '../../../../api/i18n';

const { Text } = Typography;

function when(days) {
  if (days === 0) return t('today');
  if (days === 1) return t('tomorrow');
  return tp(days, 'in {count} day', 'in {count} days');
}

/**
 * Birthdays & anniversaries ("Glance"), with "Send wishes" opening the Kudos
 * composer pre-filled with that person — shown only when Kudos is available.
 *
 * Shows real profile pictures (the server sends each person's avatar, falling
 * back to their generated initials image); a coloured initials circle covers the
 * case where someone has no picture at all.
 */
function Celebrations({ data, ctx }) {
  const items = ((data && data.celebrations) || []).slice(0, 6);
  const kudosApp = (ctx.apps || []).find((a) => a.name === 'kudos');
  const canKudos = !!kudosApp;

  return (
    <Card
      size="small"
      style={{ height: '100%' }}
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.birthday)}><GiftOutlined /></span>
          {t('Birthdays & anniversaries')}
        </span>
      )}
    >
      {items.length === 0 ? (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={t('Nothing coming up')} />
      ) : (
        <List
          size="small"
          dataSource={items}
          renderItem={(c) => {
            const isBirthday = (c.type || '').toLowerCase().indexOf('birth') !== -1;
            const accent = isBirthday ? ACCENT.birthday : ACCENT.anniversary;
            const today = c.days === 0;
            return (
              <List.Item
                style={{
                  paddingLeft: 6,
                  paddingRight: 6,
                  borderRadius: 8,
                  background: today ? soft(accent, 0.1) : undefined,
                }}
                // The side rail is narrow: a fixed-width action column squeezed the
                // name and broke words mid-syllable, so the button is compact and
                // never shrinks while the text truncates instead.
                actions={canKudos ? [
                  <Button
                    key="wish"
                    size="small"
                    type="text"
                    icon={<GiftOutlined />}
                    title={t('Send wishes to {name}', { name: c.name })}
                    style={{ color: accent, flex: '0 0 auto' }}
                    onClick={() => openIntent({
                      module: kudosApp.key, action: 'compose', params: { recipient: c.id, name: c.name },
                    })}
                  />,
                ] : []}
              >
                <List.Item.Meta
                  avatar={(
                    <Badge
                      count={(
                        <span style={{
                          background: accent,
                          color: '#fff',
                          borderRadius: '50%',
                          width: 16,
                          height: 16,
                          display: 'inline-flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          fontSize: 9,
                        }}
                        >
                          {isBirthday ? <GiftOutlined /> : <StarOutlined />}
                        </span>
                      )}
                      offset={[-2, 30]}
                    >
                      <Avatar
                        size={36}
                        src={c.image || undefined}
                        style={c.image ? undefined : { background: personColor(c.name) }}
                      >
                        {c.image ? null : initials(c.name)}
                      </Avatar>
                    </Badge>
                  )}
                  title={(
                    <span style={{
                      display: 'flex', alignItems: 'center', gap: 6, minWidth: 0,
                    }}
                    >
                      <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {c.name}
                      </span>
                      {today && (
                        <Tag
                          bordered={false}
                          style={{
                            margin: 0, color: accent, background: soft(accent), flex: '0 0 auto',
                          }}
                        >
                          {t('today')}
                        </Tag>
                      )}
                    </span>
                  )}
                  description={(
                    <Text type="secondary" style={{ fontSize: 12, whiteSpace: 'nowrap' }}>
                      {isBirthday
                        ? t('Birthday')
                        : (c.years ? t('{years}y anniversary', { years: c.years }) : t('Anniversary'))}
                      {typeof c.days === 'number' && !today ? ` · ${when(c.days)}` : ''}
                    </Text>
                  )}
                />
              </List.Item>
            );
          }}
        />
      )}
    </Card>
  );
}

registerWidget({
  id: 'celebrations', order: 50, column: 'side', size: 'full', component: Celebrations,
});

export default Celebrations;
