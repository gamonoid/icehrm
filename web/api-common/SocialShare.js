/* eslint-disable no-restricted-globals */
/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
const SocialShare = {
  facebook: (url) => {
    const w = 700;
    const h = 500;
    const left = (screen.width / 2) - (w / 2);
    const top = (screen.height / 2) - (h / 2);

    url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;

    window.open(url, 'Share on Facebook', `width=${w},height=${h},left=${left},top=${top}`);
    return false;
  },

  google: (url) => {
    const w = 500;
    const h = 500;
    const left = (screen.width / 2) - (w / 2);
    const top = (screen.height / 2) - (h / 2);

    url = `https://plus.google.com/share?url=${encodeURIComponent(url)}`;

    window.open(url, 'Share on Google', `width=${w},height=${h},left=${left},top=${top}`);
    return false;
  },

  linkedin: (url) => {
    const w = 500;
    const h = 500;
    const left = (screen.width / 2) - (w / 2);
    const top = (screen.height / 2) - (h / 2);

    url = `https://www.linkedin.com/cws/share?url=${encodeURIComponent(url)}`;

    window.open(url, 'Share on Linked in', `width=${w},height=${h},left=${left},top=${top}`);
    return false;
  },

  twitter(url, msg) {
    window.open(`http://twitter.com/share?text=${escape(msg)}&url=${escape(url)}`, 'popup', 'width=550,height=260,scrollbars=yes,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=200,top=200');
    return false;
  },
};

export default SocialShare;
