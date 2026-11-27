import OnboardingUserExtensionController from './controller';
import OnboardingUserModule from './module';
import OnboardingUserView from './view';

// Exposed so the SPA shell renders the view natively inside its own React tree.
window.OnboardingUserView = OnboardingUserView;

function init(data) {
  window.modJsList = [];
  window.modJs = new OnboardingUserModule('OnboardingUser');
  window.modJsList.push(window.modJs);

  window.onboardingUserExtensionController = new OnboardingUserExtensionController(
    'user=onboarding',
    data.controller_url,
  );
}

window.initOnboardingUser = init;

// IceHrmPro marker (see the admin bundle).
(window.iceProModules = window.iceProModules || {}).initOnboardingUser = true;
