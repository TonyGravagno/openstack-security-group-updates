# openstack-security-group-updates
Scripts to update OpenStack Security Groups based on a master IP list

See the [FAQ](FAQ.md) for more info.



## Requirements:
- `sudo apt update`
- Python3
- `sudo apt install python3-openstackclient`
- `source your-openstack-rc-file.sh`

You will probably need your password, which may expire after some period of time. Therefore it might be difficult or impossible to cron this process.

## Preparation

- Create a security group in OpenStack.
    - WPMU DEV members use "WPMU DEV".
    - Others, use any name, spaces are OK, like "ALLOW ACCESS".
- Save the current list of IP addresses in __./ip_addresses__
    - The file must be a simple EOL-delimited with one IP address per line.
    - WPMU DEV members see [WPMU DEV](#wpmu-dev).
    - Duplicated IP addresses do not hurt this script.
- chmod the script and the IP list, probably to 700, or 750, or 740

## Run the update

Use whichever tool you wish.

#### BASH

From the /bash folder:  
`./update_security_group.sh "YOUR GROUP NAME"`

#### PHP (Alpha / Untested)

From the /php folder:  
`php ./update_security_group.php "YOUR GROUP NAME"`

#### JavaScript (Alpha / Untested)

From the /javascript folder:   
`node ./update_security_group.mjs "YOUR GROUP NAME"`  
or  
`npm start`  

## Check the log

Check the file __./secgroup.log__ for the results

## Support

This is not intended to be a big project and there won't be much support. Please see the [FAQ](/FAQ.md).

## WPMU DEV

These instructions are specific to those who use WordPress plugins from WPMU DEV.

- Create a security group in OpenStack called "WPMU DEV".
- Get the current list of IP addresses:
    - All IPs are in this [Gist](https://gist.github.com/wpmu-docs/568114153e93eaf28f908a724b313b6f).
    - The [Documentation](https://wpmudev.com/docs/getting-started/wpmu-dev-ip-addresses/) breaks down the IP addresses by the plugin or other process that needs each one.
 
 Thanks to [Patrick Cohen](https://wpmudev.com/profile/pcwriter/) at WPMU DEV for his ongoing collaboration in keeping the IP addresses current, accurate, documented, and accessible. His gists are [here](https://gist.github.com/pcwriter), but see the [offical docs gists](https://gist.github.com/wpmu-docs) for current code/data.
