import React from 'react';
import InboxUserExtensionController from './controller';
import InboxUserModule from './module';
import InboxUserView from './view';

// Exposed so the SPA shell can render the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.InboxUserView = InboxUserView;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new InboxUserModule('InboxUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.inboxExtensionController = new InboxUserExtensionController(
    'user=inbox',
    data.controller_url,
  );
}

window.initInboxUser = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initInboxUser'] = true;
