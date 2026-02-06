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

  if (view === 'admin') {
    $('#menu_module_Personal_Information').hide();
    $('#menu_module_My_Tasks').hide();
    $('#menu_module_Documents').hide();
    $('#menu_module_Training').hide();
    $('#menu_module_Performance').hide();
    $('#menu_module_Travel_Management').hide();
    $('#menu_module_Finance').hide();
    $('#menu_module_User_Reports').hide();
    $('#menu_module_My_Reports').hide();
    $('#menu_module_Collaboration').hide();
    $('#menu_module_About_You').hide();
    $('#menu_module_Travel').hide();
  } else {
    $('#menu_module_Personal_Information').show();
    $('#menu_module_My_Tasks').show();
    $('#menu_module_Documents').show();
    $('#menu_module_Training').show();
    $('#menu_module_Performance').show();
    $('#menu_module_Travel_Management').show();
    $('#menu_module_Finance').show();
    $('#menu_module_User_Reports').show();
    $('#menu_module_My_Reports').show();
    $('#menu_module_Collaboration').show();
    $('#menu_module_About_You').show();
    $('#menu_module_Travel').show();
  }

  if (view === 'user') {
    $('#menu_admin_Admin').hide();
    $('#menu_admin_Employees').hide();
    $('#menu_admin_Manage').hide();
    $('#menu_admin_Admin_Reports').hide();
    $('#menu_admin_Reports').hide();
    $('#menu_admin_System').hide();
    $('#menu_admin_Insights').hide();
    $('#menu_admin_Payroll').hide();
    $('#menu_admin_Recruitment').hide();
  } else {
    $('#menu_admin_Admin').show();
    $('#menu_admin_Employees').show();
    $('#menu_admin_Manage').show();
    $('#menu_admin_Admin_Reports').show();
    $('#menu_admin_Reports').show();
    $('#menu_admin_System').show();
    $('#menu_admin_Insights').show();
    $('#menu_admin_Payroll').show();
    $('#menu_admin_Recruitment').show();
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
