import React from 'react';
import MarketplaceAdminExtensionController from './controller';
import MarketplaceAdminModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new MarketplaceAdminModule('MarketplaceAdmin');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.marketplaceExtensionController = new MarketplaceAdminExtensionController(
    'admin=marketplace',
    data.controller_url,
  );

  // Store app web URL for marketplace links
  window.marketplaceAppWebUrl = data.app_web_url;
}

window.initMarketplaceAdmin = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.MarketplaceAdminView = (require('./view').default || require('./view'));
