Vagrant.configure("2") do |config|
    config.vm.box = "icehrm/icehrm"
    config.vm.box_version = "1.0.0"
    config.vm.network "private_network", ip: "192.168.10.12"
    config.vm.synced_folder ".", "/vagrant", type: "nfs"
    config.vm.synced_folder "./deployment/vagrant/sites-available", "/etc/nginx/sites-enabled", type: "nfs"
    config.vm.synced_folder "./deployment/vagrant/ssl", "/etc/nginx/ssl", type: "nfs"

    config.vm.provider "virtualbox" do |vb|
      vb.memory = "1024"
      vb.cpus = "2"
      vb.name = "icehrm-os.test"
    end

    config.vm.provision "shell", inline: <<-SHELL
		sudo service nginx restart
    SHELL

    config.vm.hostname = "icehrm.test"

    config.hostsupdater.aliases = [
        "icehrm.test"
     ]
end

