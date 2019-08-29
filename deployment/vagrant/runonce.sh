#!/bin/bash

if [ ! -f ~/runonce ]
then

  sudo apt-get install ant
  sudo chmod -R 777 /var/log/nginx
  cd /vagrant
  curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
  sudo apt-get install -y nodejs
  npm install
  sudo npm install -g gulp-cli

  sudo sed 's/server_name clients.app.dev/server_name clients.icehrm-open.test/g' /etc/nginx/sites-available/clients.app.com > /tmp/clients.app.com
  sudo mv /tmp/clients.app.com /etc/nginx/sites-available/clients.app.com
  sudo sed 's/server_name app.app.dev/server_name app.icehrm-open.test/g' /etc/nginx/sites-available/app.app.com > /tmp/app.app.com
  sudo sed 's#root /vagrant/build/app#root /vagrant#g' /tmp/app.app.com > /tmp/mod.app.app.com
  sudo mv /tmp/mod.app.app.com /etc/nginx/sites-available/app.app.com
  sudo sed 's#xdebug.remote_host=192.168.30.1#xdebug.remote_host=192.168.40.1#g' /etc/php/7.0/fpm/conf.d/90-app.ini > /tmp/mod.90-app.ini
  sudo mv /tmp/mod.90-app.ini /etc/php/7.0/fpm/conf.d/90-app.ini

  sudo service php7.0-fpm restart
  sudo service nginx restart

  touch ~/runonce
fi
