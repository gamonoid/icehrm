<img src="web/images/logo-sq.png" align="right" />
IceHrm
===========
[![Build Status](https://travis-ci.org/gamonoid/icehrm.svg?branch=master)](https://travis-ci.org/gamonoid/icehrm)

IceHrm is a [HRM software](https://icehrm.com) which enable companies of all sizes to [manage HR activities](https://icehrm.com)
properly. 

IceHrm Mobile App (Beta)
------------------------

[Setup to IceHrm App with Your Account](https://icehrm.gitbook.io/icehrm/part-1/icehrm-mobile)

<a href="https://itunes.apple.com/gb/app/icehrm/id1450757357?mt=8" target="_blank">
<img width="200" src="https://s3.amazonaws.com/icehrm-public/images/appstore-icon.png">
</a>

<a href="https://play.google.com/store/apps/details?id=com.icehrm.mobile" target="_blank">
<img width="200" src="https://s3.amazonaws.com/icehrm-public/images/playstore-icon.png">
</a>


Useful Links
-------------
 * User Guide: [https://icehrm.gitbook.io/icehrm/](https://icehrm.gitbook.io/icehrm/)
 * IceHrm Cloud Hosting: [https://icehrm.com](https://icehrm.com)
 * IceHrm Documentation (Opensource and Commercial): [http://blog.icehrm.com](http://blog.icehrm.com)
 * IceHrm Blog: [https://icehrm.com/blog](http://icehrm.com/blog)
 * Purchase IceHrm Pro: [https://icehrm.com/modules.php](https://icehrm.com/modules.php)
 * Report Issues: [https://github.com/gamonoid/icehrm/issues](https://github.com/gamonoid/icehrm/issues)
 * Feature Requests: [https://bitbucket.org/thilina/icehrm-opensource/issues](https://bitbucket.org/thilina/icehrm-opensource/issues)
 * Community Support: [http://stackoverflow.com/search?q=icehrm](http://stackoverflow.com/search?q=icehrm)
 * IceHrm Opensource Blog: [http://icehrm.org](http://icehrm.org)

Installation
------------
 * Download the latest release https://github.com/gamonoid/icehrm/releases/latest

 * Copy the downloaded file to the path you want to install iCE Hrm in your server and extract.

 * Create a mysql DB for and user. Grant all on iCE Hrm DB to new DB user.

 * Visit iCE Hrm installation path in your browser.

 * During the installation form, fill in details appropriately.

 * Once the application is installed use the username = admin and password = admin to login to your system.

 Note: Please rename or delete the install folder (<ice hrm root>/app/install) since it could pose a security threat to your iCE Hrm instance.

Manual Installation
-------------------

[](https://thilinah.gitbooks.io/icehrm-guide/content/manual-installation.html)

Upgrade from Previous Versions to Latest Version
------------------------------------------------

Refer: [http://blog.icehrm.com/docs/upgrade/](http://blog.icehrm.com/docs/upgrade/)

Setup IceHrm Development Environment
------------------------------------

IceHrm development environment is packaged as a Vagrant box. I includes php7, nginx, phpunit and other
software required for running icehrm

Preparing development VM with Vagrant
-------------------------------------

- Clone icehrm from https://github.com/gamonoid/icehrm.git or download the source

- Install Vagrant [https://www.vagrantup.com/downloads.html](https://www.vagrantup.com/downloads.html)

- Run vagrant up in icehrm root directory (this will download icehrm vagrant image which is  ~1 GB)

```
~ $ vagrant up
```

- Run vagrant ssh to login to the Virtual machine

```
~ $ vagrant ssh
```

- Add following entries to the end of the host file to map icehrm domains to VagrantBox (on MacOS and Linux this is /etc/hosts | on windows this is Windows\System32\Drivers\etc\hosts)

```
192.168.40.40   app.icehrm-open.test
192.168.40.40   clients.icehrm-open.test
```

- Navigate to [http://clients.icehrm-open.test/dev](http://clients.icehrm-open.test/dev) to load icehrm from VM. (user:admin/pass:admin)

### Notes to Developers

- When ever you have done a change to JavaScript or CSS files in icehrm/web rebuild the frontend
```
~ $ cd /vagrant
~ $ gulp
```




