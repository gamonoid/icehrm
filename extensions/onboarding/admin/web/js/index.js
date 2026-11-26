import OnboardingAdminExtensionController from './controller';
import OnboardingAdminModule from './module';
import OnboardingAdminView from './view';
import OnboardingProfileSection from './components/ProfileSection';

// Exposed so the SPA shell renders the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.OnboardingAdminView = OnboardingAdminView;

// The "Onboarding" section of the admin employee profile. The employees module loads
// this bundle when the extension is installed (meta.json native.profileSections), so
// this file must have no other side effects at load time.
window.OnboardingProfileSection = OnboardingProfileSection;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new OnboardingAdminModule('OnboardingAdmin');
  window.modJsList.push(window.modJs);

  window.onboardingExtensionController = new OnboardingAdminExtensionController(
    'admin=onboarding',
    data.controller_url,
  );
}

window.initOnboardingAdmin = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {}).initOnboardingAdmin = true;
