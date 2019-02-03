/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
class NotificationManager {
  constructor() {
    this.baseUrl = '';
    this.templates = {};
  }

  setBaseUrl(url) {
    this.baseUrl = url;
  }

  setTemplates(data) {
    this.templates = data;
  }

  setTimeUtils(timeUtils) {
    this.timeUtils = timeUtils;
  }

  getNotifications(name, data) {
    const that = this;
    $.getJSON(this.baseUrl, { a: 'getNotifications' }, (_data) => {
      if (_data.status === 'SUCCESS') {
        that.renderNotifications(_data.data[1], _data.data[0]);
      }
    });
  }

  clearPendingNotifications(name, data) {
    const that = this;
    $.getJSON(this.baseUrl, { a: 'clearNotifications' }, (_data) => {

    });
  }

  renderNotifications(notifications, unreadCount) {
    if (notifications.length === 0) {
      return;
    }

    let t = this.templates.notifications;
    if (unreadCount > 0) {
      t = t.replace('#_count_#', unreadCount);
      if (unreadCount > 1) {
        t = t.replace('#_header_#', `You have ${unreadCount} new notifications`);
      } else {
        t = t.replace('#_header_#', `You have ${unreadCount} new notification`);
      }
    } else {
      t = t.replace('#_count_#', '');
      t = t.replace('#_header_#', 'You have no new notifications');
    }

    let notificationStr = '';

    for (const index in notifications) {
      notificationStr += this.renderNotification(notifications[index]);
    }

    t = t.replace('#_notifications_#', notificationStr);

    const $obj = $(t);

    if (unreadCount === 0) {
      $obj.find('.label-danger').remove();
    }

    $obj.attr('id', 'notifications');
    const k = $('#notifications');
    k.replaceWith($obj);

    $('.navbar .menu').slimscroll({
      height: '320px',
      alwaysVisible: false,
      size: '3px',
    }).css('width', '100%');

    this.timeUtils.convertToRelativeTime($('.notificationTime'));
  }


  renderNotification(notification) {
    let t = this.templates.notification;
    t = t.replace('#_image_#', notification.image);

    try {
      const json = JSON.parse(notification.action);
      t = t.replace('#_url_#', this.baseUrl.replace('service.php', '?') + json.url);
    } catch (e) {
      t = t.replace('#_url_#', '');
    }

    t = t.replace('#_time_#', notification.time);
    t = t.replace('#_fromName_#', notification.type);
    t = t.replace('#_message_#', this.getLineBreakString(notification.message, 27));
    return t;
  }


  getLineBreakString(str, len) {
    let t = '';
    try {
      const arr = str.split(' ');
      let count = 0;
      for (let i = 0; i < arr.length; i++) {
        count += arr[i].length + 1;
        if (count > len) {
          t += `${arr[i]}<br/>`;
          count = 0;
        } else {
          t += `${arr[i]} `;
        }
      }
    } catch (e) {
      // Do nothing
    }
    return t;
  }
}

export default NotificationManager;
