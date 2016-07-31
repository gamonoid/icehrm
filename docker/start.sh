#!/bin/bash
service nginx start
service php5-fpm start
service mysql start

if [ -d "/usr/share/nginx/www/" ]; then
    echo 'Already Installed'
else
    echo 'Installing IceHrm'
    cd /usr/share/nginx
    curl -s https://api.github.com/repos/gamonoid/icehrm/releases/latest | jq -r ".assets[] | select(.name) | .browser_download_url" | grep '.zip' | xargs wget
    ls | grep 'zip' | xargs unzip
    mv `ls -d */ | grep icehrm | head -1` www/
    rm *.zip

    echo 'Your MySQL root password : icehrmpwd'

    echo "Following will be needed during installation"
    echo "--------------------------------------------"
    echo 'IceHrm Database : icehrmdb'
    echo 'IceHrm User : icehrmuser'
    echo 'IceHrm User Password : icehrmuserpwd'
    echo 'IceHrm Database host : localhost'

    echo 'Please visit your docker installation url to begin installation.'

fi


/bin/bash