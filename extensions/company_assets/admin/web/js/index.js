import React from 'react';
import CompanyAssetsModule from './module';

function init(data) {
  window.modJsList = [];
  window.modJs = new CompanyAssetsModule('CompanyAssets');
  window.modJsList.push(window.modJs);
}

window.initCompany_assetsAdmin = init;

// SPA native mount: expose the view for in-tree (themed) rendering.
window.CompanyAssetsView = (require('./view').default || require('./view'));
