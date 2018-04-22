IceHrm
===========
[![Build Status](https://travis-ci.org/gamonoid/icehrm.svg?branch=master)](https://travis-ci.org/gamonoid/icehrm)

IceHrm is a [HRM software](https://icehrm.com) which enable companies of all sizes to [manage HR activities](https://icehrm.com)
properly. 

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

- Install ant build in your VM

```
~ $ sudo apt-get install ant
```

- Build Icehrm (your icehrm root directory is mapped to /vagrant/ directory in VM)

```
~ $ cd /vagrant
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


