IceHrm
===========
[![Build Status](https://travis-ci.org/gamonoid/icehrm.svg?branch=master)](https://travis-ci.org/gamonoid/icehrm)

IceHrm is a [HRM software](https://icehrm.com) which enable companies of all sizes to [manage HR activities](https://icehrm.com)
properly. 

IceHrm is Backed by Glacies
-----------------
[Glacies](http://glacies.de)

Useful Links
-------------
 * IceHrm Opensource Blog: [http://icehrm.org](http://icehrm.org)
 * IceHrm Cloud Hosting: [https://icehrm.com](https://icehrm.com)
 * IceHrm Documentation (Opensource and Commercial): [http://blog.icehrm.com](http://blog.icehrm.com)
 * IceHrm Blog: [https://icehrm.com/blog](http://icehrm.com/blog)
 * Purchase IceHrm Pro: [https://icehrm.com/modules.php](https://icehrm.com/modules.php)
 * Report Issues: [https://github.com/gamonoid/icehrm/issues](https://github.com/gamonoid/icehrm/issues)
 * Feature Requests: [https://bitbucket.org/thilina/icehrm-opensource/issues](https://bitbucket.org/thilina/icehrm-opensource/issues)
 * Community Support: [http://stackoverflow.com/search?q=icehrm](http://stackoverflow.com/search?q=icehrm)

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
~ $ sudo sed 's/server_name clients.app.dev/server_name clients.icehrm-open.test/g' /etc/nginx/sites-available/clients.app.com > /tmp/clients.app.com
~ $ sudo mv /tmp/clients.app.com /etc/nginx/sites-available/clients.app.com
~ $ sudo sed 's/server_name app.app.dev/server_name app.icehrm-open.test/g' /etc/nginx/sites-available/app.app.com > /tmp/app.app.com
~ $ sudo sed 's#root /vagrant/build/app#root /vagrant#g' /tmp/app.app.com > /tmp/mod.app.app.com
~ $ sudo mv /tmp/mod.app.app.com /etc/nginx/sites-available/app.app.com
~ $ npm install
~ $ sudo npm install -g gulp-cli
~ $ sudo service php7.0-fpm restart
~ $ sudo service nginx restart
```

- Build Icehrm (your icehrm root directory is mapped to /vagrant/ directory in VM)

```
~ $ gulp
~ $ ant buildlocal
```

- Execute table creation scripts
```
~ $ mysql -udev -pdev dev < /vagrant/core-ext/scripts/icehrmdb.sql
~ $ mysql -udev -pdev dev < /vagrant/core-ext/scripts/icehrm_master_data.sql
~ $ mysql -udev -pdev dev < /vagrant/core-ext/scripts/icehrm_sample_data.sql
```

- Navigate to [http://clients.app.dev/dev](http://clients.app.dev/dev) to load icehrm from VM. (user:admin/pass:admin)

- Unit testing

```
~ $ cd /vagrant
~ $ phpunit
```


