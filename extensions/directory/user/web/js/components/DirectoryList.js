import React, { useEffect, useState, useRef, useCallback } from 'react';
import { List, Skeleton, Space, Input, Divider } from 'antd';
import LazyAvatar from '../../../../../../web/components/LazyAvatar';
const { Search } = Input;

const BATCH_SIZE = 20;

const DirectoryList = (props) => {
  const [initLoading, setInitLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [selected, setSelected] = useState([]);
  const [list, setList] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  // Whether the server may still have more rows. Derived from batch fullness
  // (a short/empty batch = the end) rather than list.length >= total, because a
  // mismatched `total` (e.g. counting rows the listing filters out) would make
  // that comparison never terminate and keep fetching forever.
  const [hasMore, setHasMore] = useState(true);
  // Synchronous in-flight guard: state updates are async, so a fast scroll
  // could otherwise fire the same batch fetch twice.
  const fetching = useRef(false);
  const observerRef = useRef(null);

  const setFirstInTheList = (data) => {
    if (data.length > 0) {
      props.setEmployee(data[0]);
    }
  };

  // Load one batch. reset=true starts over (initial load / search); reset=false
  // appends the next batch for infinite scroll.
  const fetchPage = (nextPage, reset, term = null) => {
    if (fetching.current) {
      return;
    }
    fetching.current = true;
    const search = term !== null ? term : (searchTerm || '');
    if (reset) {
      setInitLoading(true);
    } else {
      setLoadingMore(true);
    }

    props.apiClient
      .post('staff-directory', { page: nextPage, limit: BATCH_SIZE, search })
      .then((response) => {
        fetching.current = false;
        const data = response.data.data || [];
        setTotal(response.data.total || 0);
        setPage(nextPage);
        setInitLoading(false);
        setLoadingMore(false);
        // A full batch means there may be more; a short/empty one is the end.
        setHasMore(data.length === BATCH_SIZE);
        setList((prev) => (reset
          ? data
          // Dedupe on append — a row added/removed between batch fetches can
          // shift page boundaries and repeat an entry.
          : [...prev, ...data.filter((d) => !prev.some((p) => p.id === d.id))]));
        if (reset) {
          setFirstInTheList(data);
        }
      })
      .catch(() => {
        fetching.current = false;
        setInitLoading(false);
        setLoadingMore(false);
      });
  };

  useEffect(() => {
    fetchPage(1, true);
  }, []);

  useEffect(() => () => {
    if (observerRef.current) {
      observerRef.current.disconnect();
    }
  }, []);

  const loadNextPage = () => {
    if (fetching.current || !hasMore) {
      return;
    }
    fetchPage(page + 1, false);
  };

  // The sentinel sits under the list; when it scrolls near view the next batch
  // is fetched. Ref callback so the observer follows (re)mounts.
  const attachSentinel = useCallback((node) => {
    if (observerRef.current) {
      observerRef.current.disconnect();
      observerRef.current = null;
    }
    if (node && typeof IntersectionObserver !== 'undefined') {
      observerRef.current = new IntersectionObserver((entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          loadNextPage();
        }
      }, { rootMargin: '300px' });
      observerRef.current.observe(node);
    }
  }, [page, hasMore, searchTerm]);

  const onSearch = (value) => {
    const term = value == null ? '' : value;
    setSearchTerm(term);
    fetchPage(1, true, term);
  };

  const selectItem = (item) => {
    setSelected(item);
    props.setEmployee(item);
  };

  // Theme-aware selected highlight: the hardcoded light #EEE made the (white)
  // text unreadable in the SPA shell's dark mode.
  const dark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';
  const selectedBg = dark ? 'rgba(22, 119, 255, 0.22)' : '#EEE';

  const renderItem = (item) => {
    const isSelected = selected && selected.id === item.id;
    return (
      <List.Item style={{ padding: '10px', ...(isSelected ? { backgroundColor: selectedBg } : {}) }}>
        <Skeleton avatar title={false} loading={item.loading} active>
          <List.Item.Meta
            onClick={() => selectItem(item)}
            avatar={<LazyAvatar src={item.image} name={item.first_name} />}
            title={item.first_name + ' ' + item.last_name}
            description={`${props.ice.gt('Department')}: ${item.department?.display} | ${props.ice.gt('Designation')}: ${item.job_title?.display}`}
          />
        </Skeleton>
      </List.Item>
    );
  };

  const allLoaded = !initLoading && !hasMore;

  return (
    <div style={{border: '1px solid rgba(0, 0, 0, 0.05)', padding: '10px'}}>
      <Space direction={'vertical'} style={{width:'100%', textAlign: 'Right'}}>
        <Search placeholder="Search by Name" onSearch={onSearch} enterButton />
      </Space>
      <Divider></Divider>
      {/* The directory scrolls inside its own viewport-capped panel, so the
          (infinitely growing) list never stretches the page taller than the
          screen — scrolling happens here, not on the whole layout. */}
      <div style={{ maxHeight: 'calc(100vh - 320px)', overflowY: 'auto' }}>
        <List
          className="demo-loadmore-list"
          loading={initLoading}
          itemLayout="horizontal"
          dataSource={list}
          renderItem={renderItem}
        />
        {loadingMore && (
          <div style={{ padding: '10px' }}>
            <Skeleton avatar title={false} paragraph={{ rows: 1 }} active />
          </div>
        )}
        {/* Infinite-scroll sentinel: entering the view loads the next batch. */}
        {!initLoading && !allLoaded && (
          <div ref={attachSentinel} style={{ height: 1 }} />
        )}
      </div>
    </div>
  );
};
export default DirectoryList;
