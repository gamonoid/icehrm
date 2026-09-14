import React from 'react';
import { Card, Row, Col, Button, Spin, Empty, Typography, Tag, Tabs, Avatar, Space, message, Alert } from 'antd';
import { ShoppingCartOutlined, AppstoreOutlined, LinkOutlined, UserOutlined, CheckCircleOutlined, DisconnectOutlined, ReloadOutlined, CloudDownloadOutlined, WarningOutlined, ExclamationCircleOutlined, CloseCircleOutlined } from '@ant-design/icons';

const { Meta } = Card;
const { Text, Title } = Typography;

class MarketplaceAdminExtensionView extends React.Component {
  constructor(props) {
    super(props);
    // Only the "My Purchases" tab is shown, so it is always the active tab
    // (Marketplace/Installed are hidden — see the tabItems in render()).
    const initialTab = 'my-extensions';

    this.state = {
      modules: [],
      loading: true,
      error: null,
      activeTab: initialTab,
      connected: false,
      connectionData: null,
      connectionLoading: true,
      // null until known; false shows the "Get IceHrm Pro" ad on My Purchases.
      isPro: null,
      coreVersion: null,
      myExtensions: [],
      myExtensionsLoading: false,
      myExtensionsLoaded: false,
      installedExtensions: [],
      installedExtensionsLoading: false,
      installedExtensionsLoaded: false,
      updatingExtension: null,
    };
  }

  componentDidMount() {
    this.fetchConnectionStatus();
    this.fetchModules();

    // If starting on my-extensions tab, fetch extensions after connection status is known
    if (this.state.activeTab === 'my-extensions') {
      // Also fetch installed extensions to show installed versions
      this.fetchInstalledExtensions();
      // Wait for connection status, then fetch if connected
      const checkAndFetch = () => {
        const { connected, connectionLoading, myExtensionsLoaded } = this.state;
        if (!connectionLoading && connected && !myExtensionsLoaded) {
          this.fetchMyExtensions();
        } else if (connectionLoading) {
          setTimeout(checkAndFetch, 100);
        }
      };
      checkAndFetch();
    }

    // If starting on installed tab, fetch installed extensions
    if (this.state.activeTab === 'installed') {
      this.fetchInstalledExtensions();
    }
  }

  fetchConnectionStatus = () => {
    const { controller } = this.props;

    if (!controller || !controller.getApiClient) {
      this.setState({ connectionLoading: false });
      return;
    }

    controller.getApiClient().get('marketplace/connection')
      .then((response) => {
        const data = response.data;
        this.setState({
          connected: data?.connected || false,
          connectionData: data?.data || null,
          coreVersion: data?.coreVersion || null,
          isPro: !!data?.isPro,
          connectionLoading: false,
        });
      })
      .catch((error) => {
        console.error('Failed to fetch connection status:', error);
        this.setState({ connectionLoading: false });
      });
  };

  handleConnect = () => {
    const { controller } = this.props;

    controller.getApiClient().get('marketplace/connect')
      .then((response) => {
        const authorizeUrl = response.data?.authorize_url;
        if (authorizeUrl) {
          window.location.href = authorizeUrl;
        }
      })
      .catch((error) => {
        console.error('Failed to get authorize URL:', error);
      });
  };

  handleDisconnect = () => {
    const { controller } = this.props;

    controller.getApiClient().post('marketplace/disconnect')
      .then((response) => {
        const data = response.data;
        this.setState({
          connected: false,
          connectionData: null,
          myExtensions: [],
          myExtensionsLoaded: false,
        });

        if (data?.success) {
          message.success(data.message || 'Disconnected successfully.');
        } else {
          message.warning(data?.message || 'Disconnected locally.');
        }
      })
      .catch((error) => {
        console.error('Failed to disconnect:', error);
        // Still clear local state even on error
        this.setState({
          connected: false,
          connectionData: null,
          myExtensions: [],
          myExtensionsLoaded: false,
        });
        message.warning('Disconnected locally. Could not reach server.');
      });
  };

  fetchMyExtensions = (forceRefresh = false) => {
    const { controller } = this.props;
    const { connected } = this.state;

    if (!connected) {
      return;
    }

    this.setState({ myExtensionsLoading: true });

    const url = forceRefresh ? 'marketplace/my-extensions/refresh' : 'marketplace/my-extensions';

    controller.getApiClient().get(url)
      .then((response) => {
        const extensions = Array.isArray(response.data) ? response.data : [];
        this.setState({
          myExtensions: extensions,
          myExtensionsLoading: false,
          myExtensionsLoaded: true,
        });
        if (forceRefresh) {
          message.success('Extensions refreshed');
        }
      })
      .catch((error) => {
        console.error('Failed to fetch my extensions:', error);
        this.setState({
          myExtensionsLoading: false,
          myExtensionsLoaded: true,
        });
        if (forceRefresh) {
          message.error('Failed to refresh extensions');
        }
      });
  };

  fetchInstalledExtensions = () => {
    const { controller } = this.props;

    if (!controller || !controller.getApiClient) {
      this.setState({ installedExtensionsLoading: false });
      return;
    }

    this.setState({ installedExtensionsLoading: true });

    controller.getApiClient().get('marketplace/installed-extensions')
      .then((response) => {
        const extensions = Array.isArray(response.data) ? response.data : [];
        this.setState({
          installedExtensions: extensions,
          installedExtensionsLoading: false,
          installedExtensionsLoaded: true,
        });
      })
      .catch((error) => {
        console.error('Failed to fetch installed extensions:', error);
        this.setState({
          installedExtensionsLoading: false,
          installedExtensionsLoaded: true,
        });
      });
  };

  // Remove leading 'v' from version string if present
  normalizeVersion = (versionString) => {
    if (!versionString) return '';
    return versionString.replace(/^v/i, '');
  };

  // Convert version string to number for comparison (e.g., "1.2.3" or "v1.2.3" -> 10203)
  parseVersion = (versionString) => {
    if (!versionString) return 0;
    // Remove leading 'v' if present
    const normalized = this.normalizeVersion(versionString);
    const parts = normalized.split('.').map(p => parseInt(p, 10) || 0);
    // Pad to 3 parts
    while (parts.length < 3) parts.push(0);
    // Convert to number: major * 10000 + minor * 100 + patch
    return parts[0] * 10000 + parts[1] * 100 + parts[2];
  };

  // Format version for display (always with 'v' prefix, no double 'v')
  formatVersion = (versionString) => {
    if (!versionString) return 'unknown';
    const normalized = this.normalizeVersion(versionString);
    return `v${normalized}`;
  };

  // Get module details from marketplace modules by matching file name
  getModuleByFile = (folderName) => {
    const { modules } = this.state;
    if (!modules || modules.length === 0) return null;

    // Match module.file (without .zip) with extension folder name
    return modules.find(module => {
      if (!module.file) return false;
      const fileName = module.file.replace('.zip', '');
      return fileName === folderName;
    });
  };

  fetchModules = () => {
    const { controller } = this.props;

    if (!controller || !controller.getApiClient) {
      this.setState({ error: 'API client not available', loading: false });
      return;
    }

    this.setState({ loading: true, error: null });

    controller.getApiClient().get('marketplace/modules')
      .then((response) => {
        // response.data is directly the array of modules
        const modules = Array.isArray(response.data) ? response.data : (response.data?.data || []);
        this.setState({ modules, loading: false });
      })
      .catch((error) => {
        console.error('Failed to fetch modules:', error);
        this.setState({
          error: 'Failed to load marketplace modules',
          loading: false
        });
      });
  };

  handleBuyNow = (moduleCode) => {
    const { appWebUrl } = this.props;
    const url = `${appWebUrl}/module/${moduleCode}`;
    window.open(url, '_blank');
  };

  handleRenewLicense = (licenseKey) => {
    const { appWebUrl } = this.props;
    const url = `${appWebUrl}/renew-license/${licenseKey}`;
    window.open(url, '_blank');
  };

  renderModuleCard = (module) => {
    const hasDiscount = module.discount_percent > 0;

    return (
      <Col xs={24} sm={12} md={12} lg={8} xl={6} xxl={6} key={module.id}>
        <Card
          hoverable
          style={{ marginBottom: 16, height: '100%', transition: 'box-shadow 0.2s', border: '1px solid #d9d9d9' }}
          bodyStyle={{ padding: 16 }}
          className="marketplace-card"
          cover={
            <div style={{ borderBottom: '1px solid #d9d9d9', position: 'relative' }}>
              <img
                alt={module.name}
                src={module.imageUrl}
                style={{ height: 160, width: '100%', objectFit: 'cover', display: 'block' }}
              />
              {hasDiscount && (
                <div style={{
                  position: 'absolute',
                  top: 8,
                  left: 8,
                  backgroundColor: '#ff4d4f',
                  color: '#fff',
                  padding: '4px 8px',
                  borderRadius: 4,
                  fontSize: 12,
                  fontWeight: 'bold',
                }}>
                  {module.discount_percent}% OFF
                </div>
              )}
            </div>
          }
        >
          <Meta
            title={
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span>{module.name}</span>
                <div>
                  {hasDiscount && (
                    <Text delete type="secondary" style={{ marginRight: 8, fontSize: 12 }}>
                      ${module.original_price}
                    </Text>
                  )}
                  <Tag color="green">${module.price}</Tag>
                </div>
              </div>
            }
            description={
              <div>
                <Text type="secondary" style={{
                  display: '-webkit-box',
                  WebkitLineClamp: 3,
                  WebkitBoxOrient: 'vertical',
                  overflow: 'hidden',
                  minHeight: 66
                }}>
                  {module.description}
                </Text>
                <div style={{ marginTop: 12, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Tag>{this.formatVersion(module.version)}</Tag>
                  <Button
                    type="primary"
                    icon={<ShoppingCartOutlined />}
                    onClick={() => this.handleBuyNow(module.code)}
                    style={{ backgroundColor: '#346CB0', borderColor: '#346CB0' }}
                  >
                    Buy Now
                  </Button>
                </div>
              </div>
            }
          />
        </Card>
      </Col>
    );
  };

  handleTabChange = (activeTab) => {
    this.setState({ activeTab });

    // Fetch my extensions when switching to that tab (if connected and not loaded yet)
    if (activeTab === 'my-extensions') {
      const { connected, myExtensionsLoaded, installedExtensionsLoaded } = this.state;
      if (connected && !myExtensionsLoaded) {
        this.fetchMyExtensions();
      }
      // Also fetch installed extensions to show installed versions
      if (!installedExtensionsLoaded) {
        this.fetchInstalledExtensions();
      }
    }

    // Fetch installed extensions when switching to that tab
    if (activeTab === 'installed') {
      const { installedExtensionsLoaded } = this.state;
      if (!installedExtensionsLoaded) {
        this.fetchInstalledExtensions();
      }
    }
  };

  renderMarketplaceTab = () => {
    const { modules, loading, error } = this.state;

    if (loading) {
      return (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
          <div style={{ marginTop: 16 }}>Loading marketplace modules...</div>
        </div>
      );
    }

    if (error) {
      return (
        <Empty
          description={error}
          image={Empty.PRESENTED_IMAGE_SIMPLE}
        />
      );
    }

    if (!modules || modules.length === 0) {
      return (
        <Empty
          description="No modules available in the marketplace"
          image={Empty.PRESENTED_IMAGE_SIMPLE}
        />
      );
    }

    // Sort modules by discount_percent descending (bigger discounts first)
    const sortedModules = [...modules].sort((a, b) => (b.discount_percent || 0) - (a.discount_percent || 0));

    return (
      <Row gutter={[16, 16]}>
        {sortedModules.map(this.renderModuleCard)}
      </Row>
    );
  };

  // Get installed version for a purchased extension by matching directory
  getInstalledVersionForPurchase = (extension) => {
    const { installedExtensions } = this.state;
    if (!installedExtensions || installedExtensions.length === 0) return null;

    const directory = extension.directory || '';

    // Try to find matching installed extension by directory/folder name
    const installed = installedExtensions.find(ext => {
      if (!ext || !ext.folder) return false;
      return ext.folder.toLowerCase() === directory.toLowerCase();
    });

    return installed?.version || null;
  };

  renderExtensionCard = (extension) => {
    const { updatingExtension, installedExtensionsLoaded } = this.state;
    const module = extension.module || {};
    const isExpired = extension.is_expired;
    const isInstalling = updatingExtension === extension.directory;

    // Use installed version from version.json instead of API response
    const installedVersion = this.getInstalledVersionForPurchase(extension);
    const displayVersion = installedVersion || extension.version;
    const isInstalled = !!installedVersion;
    // Installing/updating extensions is not allowed under My Purchases — this is a
    // view-only list of purchased extensions (the Install/Update flows live on the
    // hidden Marketplace/Installed tabs).
    const isIceHrmCore = extension.directory === 'icehrm';
    const isIceHrmPro = (extension.directory || '').toLowerCase() === 'icehrmpro'
      || ((module.code || '').toLowerCase() === 'icehrmpro')
      || ((module.name || '').toLowerCase() === 'icehrmpro');
    const showInstallButton = false;

    return (
      <Col xs={24} key={extension.id}>
        <Card
          hoverable
          style={{
            marginBottom: 16,
            transition: 'box-shadow 0.2s',
            border: '1px solid #d9d9d9',
            opacity: isExpired ? 0.7 : 1,
          }}
          bodyStyle={{ padding: 0 }}
          className="marketplace-card"
        >
          <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center' }}>
            <div style={{
              padding: 16,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <Avatar
                src={module.image_url}
                size={80}
                style={{
                  border: '1px solid #d9d9d9',
                }}
              />
            </div>
            <div style={{ flex: 1, padding: 16, paddingLeft: 0 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                <Title level={5} style={{ margin: 0 }}>{module.label || module.name}</Title>
                {showInstallButton && (
                  <Button
                    type="primary"
                    size="small"
                    icon={<CloudDownloadOutlined />}
                    onClick={() => this.handleInstall(extension)}
                    loading={isInstalling}
                    disabled={isInstalling}
                  >
                    {isInstalling ? 'Installing...' : 'Install'}
                  </Button>
                )}
              </div>
              <Text type="secondary" style={{
                display: '-webkit-box',
                WebkitLineClamp: 2,
                WebkitBoxOrient: 'vertical',
                overflow: 'hidden',
              }}>
                {module.description}
              </Text>
              <div style={{ marginTop: 12 }}>
                <Tag color="geekblue">{this.formatVersion(displayVersion)}</Tag>
                {isInstalled && (
                  <Tag color="green" style={{ marginLeft: 4 }}>Installed</Tag>
                )}
                {extension.expiry_date && (
                  <Text type="secondary" style={{ fontSize: 12, marginLeft: 8 }}>
                    Expires: {extension.expiry_date.split(' ')[0]}
                  </Text>
                )}
              </div>
              {isIceHrmPro && extension.users !== undefined && extension.users !== null && extension.users !== '' && (
                <div style={{ marginTop: 8 }}>
                  <Text type="secondary" style={{ fontSize: 12 }}>
                    Allowed employees: <Text strong style={{ fontSize: 12 }}>{extension.users}</Text>
                  </Text>
                </div>
              )}
              {extension.license_key && (
                <div style={{ marginTop: 8 }}>
                  <Text type="secondary" style={{ fontSize: 12 }}>
                    License: <Text code copyable style={{ fontSize: 12 }}>{extension.license_key}</Text>
                  </Text>
                </div>
              )}
            </div>
          </div>
        </Card>
      </Col>
    );
  };

  // Ad shown on non-Pro (IS_ICEHRM_PRO=false) builds, pointing users to purchase
  // IceHrm Pro. No pricing info by design.
  renderProAd = () => {
    if (this.state.isPro !== false) {
      return null;
    }
    return (
      <Alert
        type="info"
        showIcon
        icon={<AppstoreOutlined />}
        style={{ marginBottom: 16 }}
        message="Get IceHrm Pro"
        description="Unlock Leave Management, Recruitment, Expenses, Performance, Payroll, Insights and more — all in one subscription."
        action={(
          <Button
            type="primary"
            href="https://icehrm.com/purchase-icehrmpro"
            target="_blank"
            rel="noopener noreferrer"
          >
            Purchase IceHrm Pro
          </Button>
        )}
      />
    );
  };

  renderMyExtensionsTab = () => {
    const { connected, connectionLoading, myExtensions, myExtensionsLoading } = this.state;

    let body;
    if (connectionLoading) {
      body = (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
          <div style={{ marginTop: 16 }}>Loading...</div>
        </div>
      );
    } else if (!connected) {
      body = (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Empty
            image={Empty.PRESENTED_IMAGE_SIMPLE}
            description={
              <span>
                Connect your IceHrm installation to access your purchased extensions
              </span>
            }
          >
            <Button
              type="primary"
              icon={<LinkOutlined />}
              onClick={this.handleConnect}
              size="large"
            >
              Connect to IceHrm
            </Button>
          </Empty>
        </div>
      );
    } else if (myExtensionsLoading) {
      body = (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
          <div style={{ marginTop: 16 }}>Loading your extensions...</div>
        </div>
      );
    } else {
      // Only the IceHrmPro subscription is shown; the individual extension
      // purchases it covers (returned as is_virtual entries) and the "icehrm"
      // core entry are hidden. The subscription is identified by its directory /
      // module code / module name of "icehrmpro".
      const proSubscriptions = myExtensions.filter((ext) => {
        const dir = (ext.directory || '').toLowerCase();
        const code = ((ext.module && ext.module.code) || '').toLowerCase();
        const name = ((ext.module && ext.module.name) || '').toLowerCase();
        return dir === 'icehrmpro' || code === 'icehrmpro' || name === 'icehrmpro';
      });
      body = proSubscriptions.length === 0 ? (
        <Empty
          description="No IceHrmPro subscription found"
          image={Empty.PRESENTED_IMAGE_SIMPLE}
        />
      ) : (
        <Row gutter={[16, 16]}>
          {proSubscriptions.map(this.renderExtensionCard)}
        </Row>
      );
    }

    return (
      <div>
        {this.renderProAd()}
        {connected && (
          <div style={{ marginBottom: 16, textAlign: 'right' }}>
            <Button
              icon={<ReloadOutlined />}
              onClick={() => this.fetchMyExtensions(true)}
              loading={myExtensionsLoading}
            >
              Refresh
            </Button>
          </div>
        )}
        {body}
      </div>
    );
  };

  // Get license status tag based on extension license status
  getLicenseStatusTag = (licenseStatus) => {
    if (!licenseStatus) return null;

    switch (licenseStatus) {
      case 'Active':
        return <Tag color="success" icon={<CheckCircleOutlined />}>Active</Tag>;
      case 'Expired':
        return <Tag color="error" icon={<CloseCircleOutlined />}>Expired</Tag>;
      case 'No License':
        return <Tag color="default" icon={<ExclamationCircleOutlined />}>No License</Tag>;
      default:
        return null;
    }
  };

  renderInstalledExtensionCard = (extension) => {
    // Guard against null/undefined extensions
    if (!extension || !extension.folder) {
      return null;
    }

    const module = this.getModuleByFile(extension.folder);

    // Skip if module not found in marketplace
    if (!module) {
      return null;
    }

    const { updatingExtension } = this.state;
    const isUpdating = updatingExtension === extension.folder;
    const installedVersion = extension.version;
    const marketplaceVersion = module.version;
    const isGroup = extension.isGroup === true;
    const licenseStatus = extension.licenseStatus;
    const licenseKey = extension.licenseKey;

    // Check if update is available
    let updateAvailable = false;
    if (installedVersion && marketplaceVersion) {
      const installedNum = this.parseVersion(installedVersion);
      const marketplaceNum = this.parseVersion(marketplaceVersion);
      updateAvailable = marketplaceNum > installedNum;
    }

    // Get description - use extension's own description for groups, or module description
    const description = isGroup && extension.description
      ? extension.description
      : module.description;

    // Get display name - use extension name for groups, or module name
    const displayName = isGroup ? extension.name : module.name;

    // Determine card border color based on license status
    let borderColor = '1px solid #d9d9d9';
    if (licenseStatus === 'Expired') {
      borderColor = '1px solid #ff4d4f';
    } else if (licenseStatus === 'No License') {
      borderColor = '1px solid #d9d9d9';
    } else if (updateAvailable) {
      borderColor = '1px solid #faad14';
    }

    return (
      <Col xs={24} key={extension.folder}>
        <Card
          hoverable
          style={{
            marginBottom: 16,
            transition: 'box-shadow 0.2s',
            border: borderColor,
          }}
          bodyStyle={{ padding: 0 }}
          className="marketplace-card"
        >
          <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center' }}>
            <div style={{
              padding: 16,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <Avatar
                src={module.imageUrl}
                size={80}
                style={{
                  border: '1px solid #d9d9d9',
                }}
              />
            </div>
            <div style={{ flex: 1, padding: 16, paddingLeft: 0 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Title level={5} style={{ margin: 0 }}>{displayName}</Title>
                  {isGroup && <Tag color="blue">Bundle</Tag>}
                </div>
                <Space>
                  <Tag color="geekblue">{this.formatVersion(installedVersion)}</Tag>
                  {this.getLicenseStatusTag(licenseStatus)}
                  {updateAvailable && (
                    <Tag color="warning" icon={<WarningOutlined />}>Update Available</Tag>
                  )}
                </Space>
              </div>
              {description && (
                <Text type="secondary" style={{
                  display: '-webkit-box',
                  WebkitLineClamp: 2,
                  WebkitBoxOrient: 'vertical',
                  overflow: 'hidden',
                }}>
                  {description}
                </Text>
              )}
              {isGroup && extension.extensions && extension.extensions.length > 0 && (
                <div style={{ marginTop: 8 }}>
                  <Text type="secondary" style={{ fontSize: 12 }}>Includes: </Text>
                  {extension.extensions.map((ext) => (
                    <Tag key={ext} style={{ marginBottom: 4 }}>{ext}</Tag>
                  ))}
                </div>
              )}
              <div style={{ marginTop: 12, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                  {updateAvailable && (
                    <Text type="warning" style={{ fontSize: 12 }}>
                      New version {this.formatVersion(marketplaceVersion)} available
                    </Text>
                  )}
                  {extension.releaseDate && !updateAvailable && licenseStatus === 'Active' && (
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      Released: {extension.releaseDate}
                    </Text>
                  )}
                  {licenseStatus === 'Expired' && (
                    <Text type="danger" style={{ fontSize: 12 }}>
                      Your license has expired. Please renew to continue using this extension.
                    </Text>
                  )}
                  {licenseStatus === 'No License' && (
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      No license found for this extension.
                    </Text>
                  )}
                </div>
                <Space>
                  {licenseStatus === 'No License' && module.code && (
                    <Button
                      type="default"
                      size="small"
                      icon={<ShoppingCartOutlined />}
                      onClick={() => this.handleBuyNow(module.code)}
                    >
                      Purchase License
                    </Button>
                  )}
                  {licenseStatus === 'Expired' && licenseKey && (
                    <Button
                      type="default"
                      size="small"
                      icon={<ShoppingCartOutlined />}
                      onClick={() => this.handleRenewLicense(licenseKey)}
                    >
                      Renew License
                    </Button>
                  )}
                  {updateAvailable && licenseStatus === 'Active' && (
                    <Button
                      type="primary"
                      size="small"
                      icon={<CloudDownloadOutlined />}
                      onClick={() => this.handleUpdate(extension)}
                      loading={isUpdating}
                      disabled={isUpdating}
                    >
                      {isUpdating ? 'Updating...' : 'Update'}
                    </Button>
                  )}
                </Space>
              </div>
            </div>
          </div>
        </Card>
      </Col>
    );
  };

  // Find license key for an installed extension from myExtensions
  getLicenseKeyForExtension = (extensionFolder) => {
    const { myExtensions } = this.state;
    if (!myExtensions || myExtensions.length === 0) return null;

    // Match by directory attribute (case insensitive)
    const purchase = myExtensions.find(ext => {
      const directory = ext.directory || '';
      return directory.toLowerCase() === extensionFolder.toLowerCase();
    });

    return purchase?.license_key || null;
  };

  // Shared function for installing/updating extensions
  installOrUpdateExtension = async (extensionName, licenseKey, displayName, isInstall = false) => {
    const { controller } = this.props;

    // Set loading state
    this.setState({ updatingExtension: extensionName });

    try {
      const response = await controller.getApiClient().post('marketplace/update-extension', {
        extensionName: extensionName,
        licenseKey: licenseKey,
      });

      // Check for error in response
      if (response.data?.error) {
        const errorMsg = response.data.error[0]?.[0]?.message || (isInstall ? 'Install failed' : 'Update failed');
        message.error(errorMsg, 5);
        return;
      }

      message.success(`${displayName} ${isInstall ? 'installed' : 'updated'} successfully!`);

      // Refresh installed extensions to show new version
      this.setState({ installedExtensionsLoaded: false });
      this.fetchInstalledExtensions();
    } catch (error) {
      console.error(`Failed to ${isInstall ? 'install' : 'update'} extension:`, error);
      const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || `Failed to ${isInstall ? 'install' : 'update'} extension. Please try again.`;
      message.error(errorMsg, 5);
    } finally {
      this.setState({ updatingExtension: null });
    }
  };

  handleUpdate = async (extension) => {
    const { connected, myExtensionsLoaded } = this.state;

    // Ensure we're connected and have purchased extensions loaded
    if (!connected) {
      message.error('Please connect to IceHrm first to update extensions');
      return;
    }

    // If my extensions haven't been loaded yet, fetch them first
    if (!myExtensionsLoaded) {
      message.info('Loading purchase information...');
      await new Promise((resolve) => {
        this.fetchMyExtensions();
        const checkLoaded = () => {
          if (this.state.myExtensionsLoaded) {
            resolve();
          } else {
            setTimeout(checkLoaded, 100);
          }
        };
        checkLoaded();
      });
    }

    // Get the license key for this extension
    const licenseKey = this.getLicenseKeyForExtension(extension.folder);
    if (!licenseKey) {
      message.error('Could not find license key for this extension. Please make sure you have purchased it.');
      return;
    }

    await this.installOrUpdateExtension(extension.folder, licenseKey, extension.name, false);
  };

  // Handle install from My Purchases tab (license key is already available)
  handleInstall = async (extension) => {
    const module = extension.module || {};
    const displayName = module.label || module.name || extension.directory;
    await this.installOrUpdateExtension(extension.directory, extension.license_key, displayName, true);
  };

  // Get the icehrm core module from marketplace modules
  getIceHrmCoreModule = () => {
    const { modules } = this.state;
    if (!modules || modules.length === 0) return null;
    return modules.find(m => m.file && m.file.replace('.zip', '') === 'icehrm');
  };

  renderIceHrmCoreCard = () => {
    const { coreVersion } = this.state;
    const module = this.getIceHrmCoreModule();
    if (!module) return null;

    // Use installed core version from config.base.php, fallback to module version
    const installedVersion = coreVersion || module.version;
    const marketplaceVersion = module.version;

    // Check if update is available
    let updateAvailable = false;
    if (installedVersion && marketplaceVersion) {
      const installedNum = this.parseVersion(installedVersion);
      const marketplaceNum = this.parseVersion(marketplaceVersion);
      updateAvailable = marketplaceNum > installedNum;
    }

    return (
      <Col xs={24} key="icehrm-core">
        <Card
          hoverable
          style={{
            marginBottom: 16,
            transition: 'box-shadow 0.2s',
            border: updateAvailable ? '1px solid #faad14' : '1px solid #1890ff',
          }}
          bodyStyle={{ padding: 0 }}
          className="marketplace-card"
        >
          <div style={{ display: 'flex', flexDirection: 'row', alignItems: 'center' }}>
            <div style={{
              padding: 16,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}>
              <Avatar
                src={module.imageUrl}
                size={80}
                style={{
                  border: '1px solid #d9d9d9',
                }}
              />
            </div>
            <div style={{ flex: 1, padding: 16, paddingLeft: 0 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Title level={5} style={{ margin: 0 }}>{module.name}</Title>
                  <Tag color="blue">Core</Tag>
                </div>
                <Space>
                  <Tag color="geekblue">{this.formatVersion(installedVersion)}</Tag>
                  {updateAvailable && (
                    <Tag color="warning" icon={<WarningOutlined />}>Update Available</Tag>
                  )}
                </Space>
              </div>
              <Text type="secondary" style={{
                display: '-webkit-box',
                WebkitLineClamp: 2,
                WebkitBoxOrient: 'vertical',
                overflow: 'hidden',
              }}>
                {module.description}
              </Text>
              {updateAvailable && (
                <Alert
                  type="info"
                  showIcon
                  style={{ marginTop: 12 }}
                  message={
                    <span>
                      New version <strong>{this.formatVersion(marketplaceVersion)}</strong> is available!
                    </span>
                  }
                  description={
                    <div style={{ marginTop: 8 }}>
                      <Text>To update IceHrm, run the following command in your IceHrm root directory:</Text>
                      <div style={{
                        backgroundColor: '#f5f5f5',
                        padding: '8px 12px',
                        borderRadius: 4,
                        marginTop: 8,
                        fontFamily: 'monospace',
                        fontSize: 13,
                      }}>
                        <Text code copyable style={{ backgroundColor: 'transparent' }}>npx icehrm-update .</Text>
                      </div>
                      <div style={{ marginTop: 12 }}>
                        <Text type="secondary" style={{ fontSize: 12 }}>
                          <strong>Steps:</strong>
                        </Text>
                        <ol style={{ margin: '4px 0 0 0', paddingLeft: 20, fontSize: 12, color: 'rgba(0,0,0,0.45)' }}>
                          <li>Open a terminal and navigate to your IceHrm installation directory</li>
                          <li>Run: <Text code style={{ fontSize: 11 }}>npx icehrm-update .</Text></li>
                          <li>Follow the prompts to complete the update</li>
                          <li>Clear your browser cache and refresh the page</li>
                        </ol>
                      </div>
                    </div>
                  }
                />
              )}
            </div>
          </div>
        </Card>
      </Col>
    );
  };

  renderInstalledTab = () => {
    const { installedExtensions, installedExtensionsLoading, loading } = this.state;

    if (installedExtensionsLoading || loading) {
      return (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
          <div style={{ marginTop: 16 }}>Loading installed extensions...</div>
        </div>
      );
    }

    // Filter out any invalid extensions
    const validExtensions = (installedExtensions || []).filter(ext => ext && ext.folder);

    // Check for any updates available
    const extensionsWithUpdates = validExtensions.filter(ext => {
      const module = this.getModuleByFile(ext.folder);
      if (!module || !ext.version || !module.version) return false;
      return this.parseVersion(module.version) > this.parseVersion(ext.version);
    });

    const iceHrmCoreModule = this.getIceHrmCoreModule();

    return (
      <div>
        {extensionsWithUpdates.length > 0 && (
          <Alert
            type="warning"
            showIcon
            message={`${extensionsWithUpdates.length} extension${extensionsWithUpdates.length > 1 ? 's' : ''} have updates available`}
            style={{ marginBottom: 16 }}
          />
        )}
        <div style={{ marginBottom: 16 }}>
          <Button
            icon={<ReloadOutlined />}
            onClick={() => {
              this.setState({ installedExtensionsLoaded: false });
              this.fetchInstalledExtensions();
            }}
            loading={installedExtensionsLoading}
          >
            Refresh
          </Button>
        </div>
        <Row gutter={[16, 16]}>
          {iceHrmCoreModule && this.renderIceHrmCoreCard()}
          {validExtensions.map(this.renderInstalledExtensionCard)}
        </Row>
      </div>
    );
  };

  renderConnectionIndicator = () => {
    const { connected, connectionData, connectionLoading } = this.state;

    if (connectionLoading || !connected || !connectionData) {
      return null;
    }

    // The banner text uses antd <Text> which adapts to the theme (light text in
    // dark mode); keep the surface dark in dark mode so that text stays legible
    // instead of vanishing on a hardcoded light-green background.
    const isDark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';

    return (
      <div style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'flex-end',
        marginBottom: 16,
        padding: '8px 16px',
        backgroundColor: isDark ? 'rgba(82, 196, 26, 0.12)' : '#f6ffed',
        border: isDark ? '1px solid rgba(82, 196, 26, 0.35)' : '1px solid #b7eb8f',
        borderRadius: 6,
      }}>
        <Space>
          <CheckCircleOutlined style={{ color: '#52c41a' }} />
          <Text type="secondary">Connected as:</Text>
          <Avatar size="small" icon={<UserOutlined />} />
          <Text strong>{connectionData.client_name}</Text>
          <Text type="secondary">({connectionData.client_email})</Text>
          <Button
            type="text"
            size="small"
            icon={<DisconnectOutlined />}
            onClick={this.handleDisconnect}
            danger
          >
            Disconnect
          </Button>
        </Space>
      </div>
    );
  };

  render() {
    const { activeTab } = this.state;

    // Only the "My Purchases" tab is surfaced; the Marketplace (browse/buy) and
    // Installed tabs are intentionally hidden. The render methods are kept so the
    // tabs can be reinstated by adding their entries back here.
    const tabItems = [
      {
        key: 'my-extensions',
        label: (
          <span>
            <AppstoreOutlined style={{ marginRight: 8 }} />
            My Purchases
          </span>
        ),
        children: this.renderMyExtensionsTab(),
      },
    ];

    return (
      <div style={{ padding: 16 }}>
        <style>
          {`
            .marketplace-card:hover {
              box-shadow: 0 2px 8px rgba(0, 0, 0, 0.09) !important;
            }
          `}
        </style>
        {this.renderConnectionIndicator()}
        <Tabs
          activeKey={activeTab}
          onChange={this.handleTabChange}
          items={tabItems}
        />
      </div>
    );
  }
}

export default MarketplaceAdminExtensionView;
