import React from 'react';
import { Button, Empty, Space, Spin } from 'antd';
import KudosCard from './KudosCard';
import { t } from '../../../../../../web/api/i18n';

/**
 * A page-able list of KudosCards. The parent owns the items and paging; this only
 * renders them and asks for more.
 */
export default function KudosFeed({
  items, loading, hasMore, onLoadMore, onDeleted, emptyText,
}) {
  if (loading && (!items || !items.length)) {
    return <div style={{ padding: 48, textAlign: 'center' }}><Spin /></div>;
  }
  if (!items || !items.length) {
    return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={emptyText || t('No kudos yet')} style={{ padding: 32 }} />;
  }
  return (
    <Space direction="vertical" size={16} style={{ width: '100%' }}>
      {items.map((k) => (
        <KudosCard key={k.id} kudos={k} onDeleted={onDeleted} />
      ))}
      {hasMore && (
        <div style={{ textAlign: 'center' }}>
          <Button onClick={onLoadMore} loading={loading}>{t('Show more')}</Button>
        </div>
      )}
    </Space>
  );
}
