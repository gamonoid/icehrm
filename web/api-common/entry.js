/* global timeUtils */
/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import NotificationManager from './Notifications';
import TimeUtils from './TimeUtils';

import RequestCache from './RequestCache';
import SocialShare from './SocialShare';

const Aes = require('./Aes');

window.RequestCache = RequestCache;
window.SocialShare = SocialShare;

window.setupTimeUtils = (diffHoursBetweenServerTimezoneWithGMT) => {
  const timeUtils = new TimeUtils();
  timeUtils.setServerGMToffset(diffHoursBetweenServerTimezoneWithGMT);

  return timeUtils;
};

window.setupNotifications = (baseUrl) => {
  const notificationManager = new NotificationManager();
  notificationManager.setBaseUrl(baseUrl);
  notificationManager.setTimeUtils(timeUtils);

  return notificationManager;
};
