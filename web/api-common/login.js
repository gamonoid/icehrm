$(document).ready(() => {
  $(window).keydown((event) => {
    if (event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
  });

  $('#password').keydown((event) => {
    if (event.keyCode == 13) {
      submitLogin();
      return false;
    }
  });
});

window.showForgotPassword = () => {
  $('#loginForm').hide();
  $('#requestPasswordChangeForm').show();
};

window.requestPasswordChange = () => {
  $('#requestPasswordChangeFormAlert').hide();
  const id = $('#usernameChange').val();
  if (id === '') {
    return false;
  }
  $.post('service.php', { a: 'rpc', id }, (data) => {
    if (data.status == 'SUCCESS') {
      $('#requestPasswordChangeFormAlert').show();
      $('#requestPasswordChangeFormAlert').html(data.message);
    } else {
      $('#requestPasswordChangeFormAlert').show();
      $('#requestPasswordChangeFormAlert').html(data.message);
    }
  }, 'json');
};

window.changePassword = (key) => {
  $('#newPasswordFormAlert').hide();
  const password = $('#password').val();

  const passwordValidation = function (str) {
    return str.length > 7;
  };


  if (!passwordValidation(password)) {
    $('#newPasswordFormAlert').show();
    $('#newPasswordFormAlert').html('Password should be longer than 7 characters');
    return;
  }


  $.post('service.php', {
    a: 'rsp', key, pwd: password, now: '1',
  }, (data) => {
    if (data.status == 'SUCCESS') {
      top.location.href = 'login.php?c=1';
    } else {
      $('#newPasswordFormAlert').show();
      $('#newPasswordFormAlert').html(data.message);
    }
  }, 'json');
};

window.submitLogin = () => {
  const username = $('#username').val();
  const password = $('#password').val();
  if (username === '' || password === '') {
    return false;
  }
  try {
    localStorage.clear();
  } catch (e) {}
  $('#loginForm').submit();
};

window.authGoogle = () => {
  window.location.href = `${window.location.href.split('login.php')[0]}login.php?google=1`;
};
