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

window.authMicrosoft = () => {
  window.location.href = `${window.location.href.split('login.php')[0]}login.php?microsoft=1`;
};

window.showLoginWithCode = () => {
  $('#loginForm').hide();
  $('#requestPasswordChangeForm').hide();
  $('#loginWithCodeForm').show();
  $('#loginCodeFormAlert').hide();
  $('#enterCodeSection').hide();
  $('#requestCodeBtn').show();
};

window.requestLoginCode = () => {
  $('#loginCodeFormAlert').hide();
  const email = $('#codeEmail').val().trim();

  // Validate email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (email === '' || !emailRegex.test(email)) {
    $('#loginCodeFormAlert').show();
    $('#loginCodeFormAlert').html('Please enter a valid email address');
    return false;
  }

  // Disable button and show loading state
  const $btn = $('#requestCodeBtn button');
  const originalText = $btn.text();
  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:8px"></i>Sending...');

  $.post('service.php', { a: 'rlc', email }, (data) => {
    $('#loginCodeFormAlert').show();
    $('#loginCodeFormAlert').html(data.message);
    if (data.status === 'SUCCESS') {
      $('#requestCodeBtn').hide();
      $('#enterCodeSection').show();
    } else {
      // Re-enable button on error
      $btn.prop('disabled', false).html(originalText);
    }
  }, 'json').fail(() => {
    // Re-enable button on failure
    $('#loginCodeFormAlert').show();
    $('#loginCodeFormAlert').html('An error occurred. Please try again.');
    $btn.prop('disabled', false).html(originalText);
  });
};

window.submitLoginWithCode = () => {
  const email = $('#codeEmail').val().trim();
  const code = $('#loginCode').val().trim();
  if (email === '' || code === '') {
    $('#loginCodeFormAlert').show();
    $('#loginCodeFormAlert').html('Please enter both email and code');
    return false;
  }
  // Use the main login form to submit with code as password
  $('#username').val(email);
  $('#password').val(code);
  try {
    localStorage.clear();
  } catch (e) {}
  $('#loginForm').submit();
};
