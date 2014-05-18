# -*- mode: ruby -*-
# vi: set ft=ruby :

# Config Github Settings
github_username = "fideloper"
github_repo     = "Vaprobash"
github_branch   = "master"

# Server Configuration

hostname        = "hostbase.dev"

# Server Configuration
server_ip             = "192.168.33.10"
server_memory         = "1536" # MB

# Languages and Packages
ruby_version          = "latest" # Choose what ruby version should be installed (will also be the default version)
ruby_gems             = [        # List any Ruby Gems that you want to install
]
composer_packages     = [        # List any global Composer packages that you want to install
  #"phpunit/phpunit:4.0.*",
  #"codeception/codeception=*",
  #"phpspec/phpspec:2.0.*@dev",
  #"squizlabs/php_codesniffer:1.5.*",
]

public_folder         = "/vagrant/public"

Vagrant.configure("2") do |config|

  # Set server to Ubuntu 12.04
  config.vm.box = "ubuntu/precise64"

  config.vm.box_url = "http://files.vagrantup.com/precise64.box"
  # If using VMWare Fusion Provider:
  # config.vm.box_url = "http://files.vagrantup.com/precise64_vmware.box"

  # Create a hostname, don't forget to put it to the `hosts` file
  config.vm.hostname = hostname

  # Create a static IP
  #config.vm.network :private_network, ip: server_ip


  if Vagrant::Util::Platform.windows?
    config.vm.synced_folder ".", "/vagrant", type: "smb"
  else
    # Use NFS for the shared folder
    config.vm.synced_folder ".", "/vagrant",
      id: "core",
      :nfs => !Vagrant::Util::Platform.windows?,
      :mount_options => ['nolock,vers=3,udp,noatime']
  end

  # If using VirtualBox
  config.vm.provider :virtualbox do |vb, override|

    vb.customize ["modifyvm", :id, "--memory", server_memory]
    vb.customize ["modifyvm", :id, "--cpus", 4]

    # Set the timesync threshold to 10 seconds, instead of the default 20 minutes.
    # If the clock gets more than 15 minutes out of sync (due to your laptop going
    # to sleep for instance, then some 3rd party services will reject requests.
    vb.customize ["guestproperty", "set", :id, "/VirtualBox/GuestAdd/VBoxService/--timesync-set-threshold", 10000]

    # try to fix slow network?
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]

    override.vm.network :private_network, ip: server_ip
  end
  
  # If using VMWare Fusion
  config.vm.provider :vmware_fusion do |vf, override|
    vf.vmx["memsize"] = server_memory
    vf.vmx["numvcpus"] = "4"

    override.vm.box = "precise64_vmware"
    override.vm.box_url = "http://files.vagrantup.com/precise64_vmware.box"
  end

  # If using VMWare Workstation
  config.vm.provider :vmware_workstation do |vw, override|
     vw.vmx["memsize"] = server_memory
     vw.vmx["numvcpus"] = "4"

     override.vm.box = "precise64_vmware"
     override.vm.box_url = "http://files.vagrantup.com/precise64_vmware.box"
     override.vm.network :private_network, ip: '192.168.206.10'
  end


  ####
  # Provisioning
  ##########

  # Base Packages
  config.vm.provision "shell", path: "provisioning/base.sh"

  # PHP
  config.vm.provision "shell", path: "provisioning/php.sh"

  # Apache Base
  config.vm.provision "shell", path: "provisioning/apache.sh", args: [server_ip, public_folder, hostname]

  # Nginx Base
  # config.vm.provision "shell", path: "provisioning/nginx.sh", args: [server_ip, public_folder, hostname]

  # Couchbase
  config.vm.provision "shell", path: "provisioning/couchbase.sh"

  # Elasticsearch
  config.vm.provision "shell", path: "provisioning/elasticsearch.sh"

  # ElasticHQ
  # Admin for: Elasticsearch
  # Works on: Apache2, Nginx
  config.vm.provision "shell", path: "provisioning/elastichq.sh"

  # Couchbase Elasticsearch Connector
  config.vm.provision "shell", path: "provisioning/cbes-config.sh"

  # Composer
  config.vm.provision "shell", path: "https://raw.githubusercontent.com/#{github_username}/#{github_repo}/#{github_branch}/scripts/composer.sh", privileged: false, args: composer_packages.join(" ")

  # WRAP-UP
  config.vm.provision "shell", path: "provisioning/wrap-up.sh"

end
