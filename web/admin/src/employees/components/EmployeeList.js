import React from 'react';
import {List, Skeleton, Space, Input, Divider} from 'antd';
import LazyAvatar from '../../../../components/LazyAvatar';
const { Search } = Input;

class EmployeeList extends React.Component {

  state = {
    initLoading: true,
    loadingMore: false,
    selected:[],
    list:[],
    searchTerm:'',
    total:0,
    count:20,
    page:1,
    // Whether the server may still have more rows. Derived from batch fullness
    // (a short/empty batch = the end) rather than list.length >= total, since a
    // mismatched `total` would make that comparison keep fetching forever.
    hasMore: true,
    employee: null,
  };

  constructor(props) {
    super(props);
    this.setState({employee: props.element});
    // Synchronous in-flight guard: state updates are async, so a fast scroll
    // could otherwise fire the same page fetch twice.
    this.fetching = false;
  }

  setSelected = (value) => {
    this.setState({selected: value});
  }

  reloadList() {
    // Refresh after add/edit/photo upload etc. — restart the scroll window but
    // keep whatever the user searched for.
    this.fetchPage(1, true);
  }

  componentDidMount() {
    this.fetchPage(1, true);
  }

  componentWillUnmount() {
    if (this.sentinelObserver) {
      this.sentinelObserver.disconnect();
    }
  }

  // Load one batch of the directory. reset=true starts over (initial load,
  // search, reload); reset=false appends the next batch for infinite scroll.
  fetchPage = (page, reset, term = null) => {
    if (this.fetching) {
      return;
    }
    this.fetching = true;
    const search = term !== null ? term : (this.state.searchTerm || '');
    this.setState(reset ? { initLoading: true } : { loadingMore: true });

    this.props.apiClient
      .get(`employees&limit=${this.state.count}&page=${page}&search=${search}&filters=${this.buildFilterParameters()}`)
      .then((response) => {
        this.fetching = false;
        const data = response.data.data || [];
        const total = response.data.total || 0;
        this.setState((prev) => ({
          initLoading: false,
          loadingMore: false,
          total,
          page,
          // A full batch means there may be more; a short/empty one is the end.
          hasMore: data.length === prev.count,
          // Dedupe on append — a row added/removed between batch fetches can
          // shift page boundaries and repeat an employee.
          list: reset ? data : [...prev.list, ...data.filter((d) => !prev.list.some((p) => p.id === d.id))],
        }));
        if (reset) {
          this.setFirstInTheList(data);
          // Tell the parent whether the directory came back empty (e.g. a
          // manager with no subordinates) so the profile panel can show a
          // "no employees" message instead of an endless loading skeleton.
          if (this.props.onListLoaded) {
            this.props.onListLoaded(data.length === 0);
          }
        }
      })
      .catch(() => {
        this.fetching = false;
        this.setState({ initLoading: false, loadingMore: false });
      });
  };

  loadNextPage = () => {
    if (this.fetching || this.state.initLoading || !this.state.hasMore) {
      return;
    }
    this.fetchPage(this.state.page + 1, false);
  };

  // The sentinel sits under the list; when it scrolls near the viewport the
  // next batch is fetched. Ref callback so the observer follows (re)mounts.
  attachSentinel = (node) => {
    if (this.sentinelObserver) {
      this.sentinelObserver.disconnect();
      this.sentinelObserver = null;
    }
    if (node && typeof IntersectionObserver !== 'undefined') {
      this.sentinelObserver = new IntersectionObserver((entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          this.loadNextPage();
        }
      }, { rootMargin: '300px' });
      this.sentinelObserver.observe(node);
    }
  };

  setFirstInTheList = (data) => {
    // On mobile the list is a standalone view (tap an employee to open their
    // profile, Back to return), so don't auto-select — that would bounce the
    // user straight back into a profile.
    if (this.props.autoSelect === false) {
      return;
    }
    if (data.length > 0) {
      // Keep the employee the user is currently viewing selected across list
      // reloads (e.g. after a profile photo upload, add/edit, or filter change)
      // instead of always jumping back to the first row. Fall back to the first
      // row only when nothing is selected or the selection left the list.
      const sel = this.state.selected;
      const keep = (sel && sel.id) ? data.find((e) => e.id === sel.id) : null;
      const target = keep || data[0];
      this.setSelected(target);
      this.props.setEmployee(target);
      this.props.ice.reloadEmployee(target.id);
    }
  };

  onSearch = (value) => {
    const term = value == null ? '' : value;
    this.setState({searchTerm: term});
    this.fetchPage(1, true, term);
  }

  selectItem = (item) => {
    this.setSelected(item);
    this.props.setEmployee(item);
    this.props.ice.reloadEmployee(item.id);
    this.props.ice.slowScrollToTop();
  }

  buildFilterParameters = () => {
    let filters = this.props.ice.filter;
    if (null == filters || filters.length == []) {
      return '{"status":"Active"}';
    } else {
      filters['status'] = 'Active';
      return JSON.stringify(filters);
    }
  }

  renderItem = (item) => {
    const isSelected = this.state.selected && this.state.selected.id === item.id;
    return (
      <List.Item style={{padding: '10px', ...(isSelected ? {backgroundColor: 'rgba(140,140,140,0.18)'} : {})}}>
        <Skeleton avatar title={false} loading={item.loading} active>
          <List.Item.Meta
            onClick={() => this.selectItem(item)}
            avatar={<LazyAvatar src={item.image} name={item.first_name} />}
            title={item.first_name + ' ' + item.last_name}
            description={`${item.department?.display} | ${item.job_title?.display}`}
          />
        </Skeleton>
      </List.Item>
    );
  }

  render() {
    const allLoaded = !this.state.initLoading && !this.state.hasMore;
    return (
      <div style={{border: '1px solid rgba(140, 140, 140, 0.18)', padding: '10px'}}>
        <Space direction={'vertical'} style={{width:'100%', textAlign: 'Right'}}>
          <Search placeholder="Search by Name" onSearch={this.onSearch} enterButton />
        </Space>
        <Divider></Divider>
        {/* The directory scrolls inside its own viewport-capped panel, so the
            (infinitely growing) list never stretches the page taller than the
            screen — scrolling happens here, not on the whole layout. */}
        <div style={{ maxHeight: 'calc(100vh - 320px)', overflowY: 'auto' }}>
          <List
            className="demo-loadmore-list"
            loading={this.state.initLoading}
            itemLayout="horizontal"
            dataSource={this.state.list}
            renderItem={this.renderItem}
          />
          {this.state.loadingMore && (
            <div style={{ padding: '10px' }}>
              <Skeleton avatar title={false} paragraph={{ rows: 1 }} active />
            </div>
          )}
          {/* Infinite-scroll sentinel: entering the view loads the next batch. */}
          {!this.state.initLoading && !allLoaded && (
            <div ref={this.attachSentinel} style={{ height: 1 }} />
          )}
        </div>
      </div>
    );
  }

}
export default EmployeeList;
