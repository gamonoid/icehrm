import React from 'react';
import ReactDOM from 'react-dom';
import UserViewSwitch from '../components/UserViewSwitch';


window.handleViewChange = (view, userLevel) => {
  if (userLevel !== 'Admin' && userLevel !== 'Manager') {
    $('.skeletonSideMenu').hide();
    $('.skeletonContent').hide();
    $('.skeletonTabs').hide();
    $('.sidebar').show();
    return;
  }
  if (view == null) {
    view = localStorage.getItem('user-view') === 'user' ? 'user' : 'admin';
  }

  // Data-driven, drift-proof: hide ALL admin menu groups in Employee view and ALL
  // user menu groups in Admin view, by id prefix. The previous version hid a
  // hardcoded list of group ids, which drifted — e.g. #menu_admin_Marketplace was
  // never added, so the admin-only Marketplace leaked into Employee view. Using a
  // prefix selector means any current/future group is handled automatically.
  // (Admin menu groups render as #menu_admin_*, user groups as #menu_module_*.)
  if (view === 'admin') {
    $('[id^="menu_module_"]').hide();
    $('[id^="menu_admin_"]').show();
  } else {
    $('[id^="menu_module_"]').show();
    $('[id^="menu_admin_"]').hide();
  }

  $('.skeletonSideMenu').hide();
  $('.skeletonContent').hide();
  $('.skeletonTabs').hide();
  $('.sidebar').show();
};

window.showUserViewSwitch = (userLevel) => {
  if (userLevel !== 'Admin' && userLevel !== 'Manager') {
    return;
  }
  ReactDOM.render(
    <UserViewSwitch userLevel={userLevel} />,
    document.getElementById('UserViewSwitch'),
  );
};
