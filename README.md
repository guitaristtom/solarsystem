SolarSystem
-----------

The reason behind `SolarSystem` was that I was getting tired of remoting in to local machines (or getting up and checking them), and was wanting a central way to monitor temperatures and other resources of the machines on my network.

## Server Setup
You will be needing a main server (or VM) for keeping tabs on the "planets" (meaning computers) on your network (or external if you port forward)

### Dependencies:
- Composer
- Git `(if you want to git clone)`
- LAMP (Linux Apache MySQL PHP) stack

### Install dependencies:
- Run `composer install` from the root of the programs directory

## Client Setup
### Dependencies:
- cURL
- lm-sensors `(for temperature)`