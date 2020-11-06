Vagrant.configure("2") do |config|
    config.vm.box = "icehrm/icehrm"
    config.vm.box_version = "1.0.0"
    config.vm.network "private_network", ip: "192.168.10.12"
    config.vm.synced_folder ".", "/vagrant", type: "nfs"

    config.vm.provider "virtualbox" do |vb|
      vb.memory = "1024"
      vb.cpus = "2"
      vb.name = "icehrm-os.test"
    end

    config.vm.provision "shell", inline: <<-SHELL
    	sudo rm /etc/nginx/ssl/icehrm.*
        sudo ln -s /vagrant/deployment/vagrant/ssl/icehrm.crt /etc/nginx/ssl/icehrm.crt
        sudo ln -s /vagrant/deployment/vagrant/ssl/icehrm.key /etc/nginx/ssl/icehrm.key

        sudo rm /etc/nginx/sites-enabled/default
        sudo ln -s /vagrant/deployment/vagrant/sites-available/default /etc/nginx/sites-enabled/default

        sudo service nginx restart
        sudo chmod 755 -R /var/log
    SHELL

    config.vm.hostname = "icehrm.os"

    config.hostsupdater.aliases = [
        "icehrm.os"
     ]
end

