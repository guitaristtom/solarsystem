SolarSystem
-----------

The reason behind `SolarSystem` was that I was getting tired of remoting in to local machines (or getting up and checking them), and was wanting a central way to monitor temperatures and other resources of the machines on my network.

## Server Setup
You will be needing a main server (or VM) for keeping tabs on the "planets" (meaning computers) on your network (or external if you port forward). Since PHP is my preferred language, I coded the server side in PHP

### Dependencies:
- Composer
- Git `(if you want to git clone)`
- LAMP (Linux Apache MySQL PHP) stack

### Install dependencies:
- Run `composer install` from the root of the programs directory

## Client Setup
### Dependencies:
- Bash
- Cron `(if you want to automate it)`
- cURL
- lm-sensors `(for temperature)`

For every client that you'll have in the system, you'll need to download and place the tool on to, since `cURL` is needed to be installed anyways, here is how to download the file with `cURL`:
```sh
curl -O https://raw.githubusercontent.com/guitaristtom/solarsystem/master/client/solarsystem.sh
```

Or if you're like me and generally use `wget`, you can also use that:
```sh
wget https://raw.githubusercontent.com/guitaristtom/solarsystem/master/client/solarsystem.sh
```

Once that has completed, you'll need to go through the `Settings` area at the top of the file and configure it how you wish.

### Automation
I know personally, if I can set something up and forget it, then I generally do. The same can be done for the client side of SolarSystem (w00t). Cron is such an amazing tool. To open up Cron, you can get to it by opening up a terminal window, or SSH session to your machine and running `crontab -e`. Below are a couple examples of how to set it up:

Every minute:
```cron
* * * * *  bash /path/to/solarsystem.sh
```

Every 10 minutes:
```cron
*/10 * * * * bash /path/to/solarsystem.sh
```

Every 30 seconds:
```cron
* * * * * bash /path/to/solarsystem.sh
* * * * *  sleep 30; bash /path/to/solarsystem.sh
```