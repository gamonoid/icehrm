<img src="web/images/logo-sq.png" align="right" />

IceHrm
===========
[![Build Status](https://travis-ci.org/gamonoid/icehrm.svg?branch=master)](https://travis-ci.org/gamonoid/icehrm)


IceHrm is a [HRM software](https://icehrm.com) which enable companies of all sizes to [manage HR activities](https://icehrm.com)
properly.
- [IceHrm Demo](https://icehrm.com/icehrm-demo) 
- Feature rich version of IceHrm (IceHrmPro) is available at [https://icehrm.com/purchase-icehrmpro](https://icehrm.com/purchase-icehrmpro)

Getting started
---------------

The easiest way to run IceHrm is using docker
- Install docker on Mac, Windows or Linux [https://docs.docker.com/get-docker/](https://docs.docker.com/get-docker/)

For Linux you need to install docker compose separately here [https://docs.docker.com/compose/install/](https://docs.docker.com/compose/install/)


```
$ git clone https://github.com/gamonoid/icehrm.git
$ cd icehrm
$ touch app/data/icehrm.log (or create the file manually)
$ docker-compose -f docker-compose-prod.yaml up -d
```

- Visit [http://localhost:8070/](http://localhost:8070/) to load icehrm

You can find database and app data under `icehrm/docker/production` 

When you want to kill the docker containers

```
docker-compose -f docker-compose-prod.yaml down
```

For setting up your development environment watch [https://www.youtube.com/watch?v=sz8OV_ON6S8](https://www.youtube.com/watch?v=sz8OV_ON6S8)


Refer [docker documentation](https://docs.docker.com/develop/dev-best-practices/) for best practices 


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

Installation without Docker
---------------------------
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

IceHrm uses docker to setup development environment


- Clone icehrm from https://github.com/gamonoid/icehrm.git or download the source

```
$ git clone https://github.com/gamonoid/icehrm.git
$ cd icehrm
$ docker-compose up
```
- Navigate to [http://localhost:8080](http://localhost:8080) to load icehrm. (user:admin/pass:admin)

- Make some changes and the changes will be reflected on the above url 

- Run e2e (cypress) tests

```
docker-compose -f docker-compose-testing.yaml up --exit-code-from cypress
```

- When you are ready to push your changes to production, make sure to build the production images
```
$ docker-compose -f docker-compose-prod.yaml up -d --build
```

### Building frontend assets

- When ever you have done a change to JavaScript or CSS files in icehrm/web you need to rebuild the frontend

- First make sure you have all the dependencies (just doing this once is enough)
```
$ cd icehrm/web
$ npm install
$ cd ..
$ npm install
```

- Then run gulp
```
$ gulp
```

- If you have only changed an admin module and you know which module it is
```
$ gulp admin-js --memployees
```
