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

- Install Vagrant host updater plugin [https://github.com/cogitatio/vagrant-hostsupdater](https://github.com/cogitatio/vagrant-hostsupdater)


- Run vagrant up in icehrm root directory (this will download icehrm vagrant image which is  ~1 GB)

```
~ $ vagrant up
```

- Run vagrant ssh to login to the Virtual machine

```
~ $ vagrant ssh
```

- Setup the Vagrant box

```
~ $ sudo apt-get install ant
~ $ sudo chmod -R 777 /var/log/nginx
~ $ cd /vagrant
~ $ curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
~ $ sudo apt-get install -y nodejs
~ $ npm install
~ $ sudo npm install -g gulp-cli
```

- Make Changes to enable domain icehrm-open.test in Vagrant Box

```
~ $ sudo sed 's/server_name clients.app.dev/server_name clients.icehrm-open.test/g' /etc/nginx/sites-available/clients.app.com > /tmp/clients.app.com
~ $ sudo mv /tmp/clients.app.com /etc/nginx/sites-available/clients.app.com
~ $ sudo sed 's/server_name app.app.dev/server_name app.icehrm-open.test/g' /etc/nginx/sites-available/app.app.com > /tmp/app.app.com
~ $ sudo sed 's#root /vagrant/build/app#root /vagrant#g' /tmp/app.app.com > /tmp/mod.app.app.com
~ $ sudo mv /tmp/mod.app.app.com /etc/nginx/sites-available/app.app.com

~ $ sudo service php7.0-fpm restart
~ $ sudo service nginx restart
```

- Build Icehrm (your icehrm root directory is mapped to /vagrant/ directory in VM)

```
~ $ gulp
~ $ ant buildlocal
```

- Navigate to [http://clients.icehrm-open.text/dev](http://clients.icehrm-open.text/dev) to load icehrm from VM. (user:admin/pass:admin)

### Notes to Developers

- When ever you have done a change to JavaScript or CSS files in icehrm/web
```
~ $ cd /vagrant
~ $ gulp
```


- When ever you have done a change to icehrm/core/src
```
~ $ cd /vagrant
~ $ ant phpcs-ci
```




