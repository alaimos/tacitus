# TACITuS
### Transcriptomic dAta Collector, InTegrator, and Selector

TACITuS is available at the following website http://tacitus.alaimos.com.

### Development environment setup

To setup your own TACITuS development environment follow these steps:

1. Run `git clone https://github.com/alaimos/tacitus.git` to clone the repository in a local directory
2. Enter tacitus directory
3. Copy vagrant configuration file.
    - Virtualbox: `cp puphpet/vbox.yaml puphpet/config.yaml`
    - VMWare: `cp puphpet/vmware.yaml puphpet/config.yaml`
4. Start vagrant with `vagrant up`
5. Run `vagrant ssh` to open an SSH session to the vagrant machine
6. Inside the vagrant machine execute `run.install` to finalize installation

**Important:** Processing queues are not active in development environment. 
They must be activated manually using the command `run.queue` each time you wish to 
process a job.
 
