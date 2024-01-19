#!/bin/bash

# This script uses OpenStack v3 (Keystone) to update security group rules which
#    allow inbound and outbound communications with WPMU DEV systems.
# Rules for unused IP addresses are removed.
# Rules for both Ingress and Egress are added.
# Existing correct rules are not changed.

# Tony Gravagno
# Script version : 1.0 : 2024/01/18
# https://github.com/TonyGravagno/openstack-security-group-updates
# See ReadMe and FAQ for requirements, instructions, and other information.
# MIT License

#############################################################################

# Works for any IP list and any security group
SECURITY_GROUP_NAME="$1"

# Logfile is not linked to  security group name because names can have spaces which could be confusing
LOG_FILE="./secgroup.log"
# Clear existing log
cat /dev/null > "$LOG_FILE"

# Read new IP addresses from file
declare -A new_ips
while IFS= read -r ip; do
    new_ips["$ip"]=1
done < "./ip_addresses"

# Initialize arrays for existing rules
declare -A existing_ingress_ips
declare -A existing_egress_ips

# Function to process existing rules
process_existing_rules() {
    echo "Processing existing rules for security group: $SECURITY_GROUP_NAME" | tee -a "$LOG_FILE"
    while IFS=, read -r rule_id ip_range direction; do
        ip="${ip_range%%/*}" # Extracts IP from CIDR notation
        echo "Processing $direction rule for $ip : $rule_id" | tee -a "$LOG_FILE"
        if [[ ${new_ips["$ip"]} ]]; then
            # Add to appropriate list if IP is in new IPs
            echo "  Rule will not change" | tee -a "$LOG_FILE"
            [[ "$direction" == "ingress" ]] && existing_ingress_ips["$ip"]=1
            [[ "$direction" == "egress" ]] && existing_egress_ips["$ip"]=1
        else
            # Delete rule if IP not in new IPs
            echo "Delete $direction rule for $ip" | tee -a "$LOG_FILE"
            openstack security group rule delete "$rule_id" >> "$LOG_FILE" 2>&1
        fi
        # A call is made to OpenStack to retrieve the list in comma-delimited columns (CSV)
        # Each row of the list is then parsed into fields and processed
    done < <(openstack security group rule list --long "$SECURITY_GROUP_NAME" --format csv --column "ID" --column "IP Range" --column "Direction" | tail -n +2 | sed 's/"//g')
}

# Function to add missing rules
add_missing_rules() {
    for ip in "${!new_ips[@]}"; do
        if [[ ! ${existing_ingress_ips["$ip"]} ]]; then
            echo "Adding ingress rule for $ip" | tee -a "$LOG_FILE"
            openstack security group rule create --proto tcp --dst-port 1:65535 --remote-ip "$ip" "$SECURITY_GROUP_NAME" >> "$LOG_FILE" 2>&1
        fi
        if [[ ! ${existing_egress_ips["$ip"]} ]]; then
            echo "Adding egress rule for $ip" | tee -a "$LOG_FILE"
            openstack security group rule create --proto tcp --dst-port 1:65535 --egress --remote-ip "$ip" "$SECURITY_GROUP_NAME" >> "$LOG_FILE" 2>&1
        fi
    done
}

# Main execution
process_existing_rules
add_missing_rules