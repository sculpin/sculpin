# -*- mode: ruby -*-
# vi: set ft=ruby :

# This Vagrantfile contains some configuration option that you can
# tweak for your project.
# It then loads the "main" Vagrantfile from the submodule.

require 'yaml'

class CustomConfig
  # Those accessors will be used by the Vagrantfile
  #
  # A value of 'nil' indicates that the default value can be found in
  # Drifter's Vagrantfile. This is usually the case for values that
  # are common for most projects.
  #
  # If you need to have some additional logic to define some values
  # you can delete the 'attr_accessor' and provide your own method
  # to return the values.

  attr_accessor :box_name           # url of the lxc box
  attr_accessor :box_url            # name of the lxc box

  attr_accessor :project_name       # project name (currently unused by the Vagrant file)
  attr_accessor :hostname           # main hostname of the box
  attr_accessor :hostnames          # alternative hostnames (array)
  attr_accessor :box_ip             # IP of the box

  attr_accessor :ansible_local      # use 'ansible_local' provisionner ?
  attr_accessor :ansible_version    # the ansible version to use
  attr_accessor :playbook           # path to the playbook
  attr_accessor :extra_vars         # extra variables to pass to Ansible

  attr_accessor :forwarded_ports    # Port that need to be forwarded
  attr_accessor :synced_folder_type # Type of synced folder to use

  attr_accessor :cpus               # Virtual machine CPU's count use
  attr_accessor :memory             # Virtual machine memory size use (in MB)

  # Retrieve the values of 'virtualization/parameters.yml' so that
  # they can be used by Vagrant. If you need to change those values
  # prefer editing the parameters.yml file instead.
  def initialize
    parameters_file = ENV.fetch('VIRTUALIZATION_PARAMETERS_FILE', 'virtualization/parameters.yml')
    config = YAML::load(File.open(parameters_file))

    @box_name = config['box_name'] || nil
    @box_url  = config['box_url']  || nil

    @project_name = config['project_name'] || "example"
    @hostname     = config['hostname']     || "#{@project_name}.lo"
    @hostnames    = config['hostnames']    || nil
    @box_ip       = config['box_ip']       || nil

    @ansible_local = true
    @ansible_version = config['ansible_version'] || nil

    @playbook      = config['playbook'] || nil
    @extra_vars    = {}

    @forwarded_ports    = config['forwarded_ports'] || nil
    @synced_folder_type = config['synced_folder_type'] || "nfs"

    @memory             = config['memory']  || nil
    @cpus               = config['cpus']    || nil
  end

  # Getter that first check if the accessor exists on the class and if
  # the value is not null before returning it.
  # Otherwise fallback to the default if given or raise an error.
  def get(name, default = nil)
    if self.respond_to?(name) && ! self.send(name).nil?
      self.send(name)
    elsif default.nil?
      raise "[CONFIG ERROR] '#{name}' cannot be found and no default provided."
    else
      default
    end
  end
end

Dir.chdir File.expand_path(File.dirname(__FILE__))
load 'virtualization/drifter/Vagrantfile'
