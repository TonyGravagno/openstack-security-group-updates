# Frequently Asked Questions

(Since this is a new project, these are only assumed/expected FAQs)

#### Why do I want this software?
Most of us have vendors and other service or software providers who need to connect into our systems, or we need to connect out to them. If they frequently change their own network of servers then you may need to change your firewall rules in response to their changes. This software makes that process easy.

#### What does this software do?
Save a list of IP addresses and run a script. Openstack commands are run to remove unused IP addresses from a security group, and add new rules for new IP addresses. This eliminates the need to manually check list updates and then manually update your rules.

#### What's OpenStack?
A complex platform for assembling and maintaining networks and systems. Typically used with cloud data centers and virtual private servers.

#### What is a Security Group?
A single security group has individual rules that define the inbound and outbound connections that are allowed for a system. One or more of these groups of rules is then applied to a system to define an overall security/access policy.

#### How do I create a security group?
Look it up. This software is for sysadmins.

#### What if I don't use OpenStack?
Then you don't need this. ;)

#### What if I don't know what this means?
Check with the people who maintain the security of your server hardware.

#### What if I want to change something?
Create an issue to ask about it. I may or may not make the change.  
I may suggest you find someone else to make custom changes. I may ask for some kind of compensation to make a custom change. The software is free - my time is not.

#### Are changes accepted?
I may or may not accept PRs depending on complexity and relevance to the intent of this project. If you create something that's significantly different or better, I will absolutely link it here if you wish.

#### Can I use this with Windows? MacOS?
I have no clue. Try it. I've written and tested this with Linux. It will probably work from WSL, maybe even GitBash. Since the your-openstack-rc-file.sh (see below) is a BASH script and defines Linux (POSIX) environment variables, you really can't use that for Windows from the default CMD but if you get your env vars defined with a .bat file there's a good chance this will all work.

##### What is your-openstack-rc-file.sh?
It is a script that contains all of the configuration data required to connect into your server. It exports environment variables for OS_AUTH_URL, OS_PROJECT_ID, and many others. Without these environment variables set, the OpenStack CLI will need to ask about these details. It's not 100% required, just convenient. If you're running OpenStack in a browser there should be a link to download this file. If not, then you will need to set your own environment variables - or create your own script to do so. The details for this are far outside of the scope of this project.