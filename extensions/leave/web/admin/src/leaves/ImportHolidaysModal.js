/*
 * ImportHolidaysModal — auto-populate the Holidays list for a country + year.
 *
 * Public holidays come from data bundled with the leave module
 * (PublicHolidayProvider, sourced from Nager.Date, years 2022–2035) and are
 * served by the admin getStoredHolidays action — no external call at runtime.
 * The admin reviews them in a checklist (holidays already on the calendar are
 * flagged and skipped), then the selected ones are bulk-inserted.
 */
import React from 'react';
import ReactDOM from 'react-dom';
import {
  Modal, Form, Select, InputNumber, Button, Space, Table, Tag, Alert, Empty, Typography, message,
} from 'antd';
import { DownloadOutlined, SearchOutlined } from '@ant-design/icons';

const { Text } = Typography;

const MIN_YEAR = 2022;
const MAX_YEAR = 2035;
const clampYear = (y) => Math.min(MAX_YEAR, Math.max(MIN_YEAR, y));

class ImportHolidaysModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visible: true,
      countries: [],
      loadingCountries: true,
      country: undefined, // { value, label }
      year: clampYear(new Date().getFullYear()),
      loading: false,
      error: null,
      rows: null, // [{ key, date, name, exists }]
      selectedKeys: [],
      importing: false,
    };
  }

  componentDidMount() {
    this.loadCountries();
  }

  close() {
    this.setState({ visible: false });
    setTimeout(() => {
      const { container, adapter } = this.props;
      if (container) {
        ReactDOM.unmountComponentAtNode(container);
        if (container.parentNode) container.parentNode.removeChild(container);
      }
      if (adapter) adapter.importHolidaysContainer = null;
    }, 200);
  }

  loadCountries() {
    const { adapter } = this.props;
    adapter.getHolidayCountries().then((data) => {
      const list = (data && data.countries) || [];
      this.setState({
        countries: list.map((c) => ({ value: String(c.id), label: c.name })),
        loadingCountries: false,
      });
    }).catch(() => this.setState({ loadingCountries: false }));
  }

  async find() {
    const { adapter } = this.props;
    const { country, year } = this.state;
    if (!country) { message.warning('Please choose a country'); return; }
    if (!year) { message.warning('Please enter a year'); return; }

    this.setState({ loading: true, error: null, rows: null });
    const data = await adapter.getStoredHolidays(country.value, year);
    if (!data || data.error) {
      this.setState({ loading: false, error: 'Could not load holidays. Please try again.' });
      return;
    }
    const rows = (data.holidays || []).map((h) => ({
      key: `${h.date}|${h.name}`,
      date: h.date,
      name: h.name,
      exists: !!h.exists,
    }));
    const selectedKeys = rows.filter((r) => !r.exists).map((r) => r.key);
    this.setState({ rows, selectedKeys, loading: false });
  }

  async doImport() {
    const { adapter } = this.props;
    const { rows, selectedKeys, country } = this.state;
    const chosen = rows.filter((r) => selectedKeys.includes(r.key) && !r.exists)
      .map((r) => ({ date: r.date, name: r.name }));
    if (!chosen.length) { message.warning('Select at least one holiday to import'); return; }

    this.setState({ importing: true });
    const result = await adapter.importHolidays(country.value, chosen);
    this.setState({ importing: false });
    if (result && !result.error && typeof result.inserted !== 'undefined') {
      message.success(`Imported ${result.inserted} holiday${result.inserted === 1 ? '' : 's'}`
        + (result.skipped ? `, skipped ${result.skipped} already present` : ''));
      adapter.get([]);
      this.close();
    } else {
      message.error('Could not import holidays', 5);
    }
  }

  render() {
    const {
      visible, countries, loadingCountries, country, year, loading, error, rows, selectedKeys, importing,
    } = this.state;

    const columns = [
      { title: 'Date', dataIndex: 'date', width: 130 },
      {
        title: 'Holiday',
        dataIndex: 'name',
        render: (text, r) => (
          <span>
            {text}
            {r.exists ? <Tag color="default" style={{ marginLeft: 8 }}>Already added</Tag> : null}
          </span>
        ),
      },
    ];

    const selectableCount = rows ? rows.filter((r) => !r.exists).length : 0;
    const chosenCount = rows ? rows.filter((r) => selectedKeys.includes(r.key) && !r.exists).length : 0;

    return (
      <Modal
        open={visible}
        width={680}
        maskClosable={false}
        title="Import public holidays"
        onCancel={() => this.close()}
        footer={null}
      >
        <Text type="secondary">
          {`Pick a country and a year (${MIN_YEAR}–${MAX_YEAR}), review the public holidays, and import the ones you want.`}
        </Text>

        <Form layout="inline" style={{ margin: '16px 0' }}>
          <Form.Item label="Country" style={{ marginBottom: 8 }}>
            <Select
              showSearch
              loading={loadingCountries}
              style={{ width: 240 }}
              placeholder="Select a country"
              optionFilterProp="label"
              options={countries}
              value={country ? country.value : undefined}
              onChange={(val, opt) => this.setState({ country: { value: val, label: opt.label }, rows: null })}
            />
          </Form.Item>
          <Form.Item label="Year" style={{ marginBottom: 8 }}>
            <InputNumber
              min={MIN_YEAR}
              max={MAX_YEAR}
              value={year}
              onChange={(v) => this.setState({ year: v, rows: null })}
            />
          </Form.Item>
          <Form.Item style={{ marginBottom: 8 }}>
            <Button type="primary" icon={<SearchOutlined />} loading={loading} onClick={() => this.find()}>
              Find holidays
            </Button>
          </Form.Item>
        </Form>

        {error && <Alert type="warning" showIcon message={error} style={{ marginBottom: 12 }} />}

        {rows && (
          rows.length === 0 ? (
            <Empty description="No public holidays found for this country and year" />
          ) : (
            <>
              <Table
                size="small"
                rowKey="key"
                columns={columns}
                dataSource={rows}
                pagination={false}
                scroll={{ y: 320 }}
                rowSelection={{
                  selectedRowKeys: selectedKeys,
                  onChange: (keys) => this.setState({ selectedKeys: keys }),
                  getCheckboxProps: (r) => ({ disabled: r.exists }),
                }}
              />
              <Text type="secondary" style={{ display: 'block', marginTop: 8 }}>
                {`${selectableCount} new holiday${selectableCount === 1 ? '' : 's'} available`
                  + (rows.length - selectableCount ? `, ${rows.length - selectableCount} already on the calendar` : '')}
              </Text>
            </>
          )
        )}

        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 16 }}>
          <Space>
            <Button onClick={() => this.close()}>Cancel</Button>
            <Button
              type="primary"
              icon={<DownloadOutlined />}
              loading={importing}
              disabled={!chosenCount}
              onClick={() => this.doImport()}
            >
              {chosenCount ? `Import ${chosenCount} holiday${chosenCount === 1 ? '' : 's'}` : 'Import'}
            </Button>
          </Space>
        </div>
      </Modal>
    );
  }
}

export default ImportHolidaysModal;
