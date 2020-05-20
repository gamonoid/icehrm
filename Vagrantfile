# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "thilinah/jessie64_isotope"
  config.vm.box_version = "0.0.1"
  config.vm.network "private_network", ip: "192.168.40.40"
  config.vm.synced_folder ".", "/vagrant", type: "nfs"

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
    vb.cpus = "2"
    vb.name = "icehrm.open"
  end


  config.vm.provision "shell", inline: <<-SHELL
    sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys AA8E81B4331F7F50
    sudo apt-get update
  SHELL

  config.vm.hostname = "icehrm.open"

  config.hostsupdater.aliases = [
    "app.icehrm-open.test",
    "clients.icehrm-open.test"
  ]

end
