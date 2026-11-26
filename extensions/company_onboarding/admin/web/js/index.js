import CompanyOnboardingAdminExtensionController from './controller';
import CompanyOnboardingAdminModule from './module';
import CompanyOnboardingAdminView from './view';

// Exposed so the SPA shell renders the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.CompanyOnboardingAdminView = CompanyOnboardingAdminView;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new CompanyOnboardingAdminModule('CompanyOnboardingAdmin');
  window.modJsList.push(window.modJs);

  window.companyOnboardingExtensionController = new CompanyOnboardingAdminExtensionController(
    'admin=company_onboarding',
    data.controller_url,
  );
}

window.initCompanyOnboardingAdmin = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {}).initCompanyOnboardingAdmin = true;
