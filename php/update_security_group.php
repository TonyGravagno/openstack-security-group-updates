<?php

// This script uses OpenStack v3 (Keystone) to update security group rules which
// allow inbound and outbound communications with WPMU DEV systems.
// Rules for unused IP addresses are removed.
// Rules for both Ingress and Egress are added.
// Existing correct rules are not changed.

// Tony Gravagno
// Script version : 0.1 : 2024/01/18 (Alpha / Untested)
// https://github.com/TonyGravagno/openstack-security-group-updates
// See ReadMe and FAQ for requirements, instructions, and other information.
// MIT License

// Works for any IP list and any security group
$SECURITY_GROUP_NAME = $argv[1];

// Logfile
$LOG_FILE = "./secgroup.log";
// Clear existing log
file_put_contents($LOG_FILE, "");

// Read new IP addresses from file
$new_ips = [];
$ip_list = file("./ip_addresses", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($ip_list as $ip) {
    $new_ips[$ip] = true;
}

// Initialize arrays for existing rules
$existing_ingress_ips = [];
$existing_egress_ips = [];

// Function to process existing rules
function process_existing_rules($SECURITY_GROUP_NAME, &$new_ips, &$existing_ingress_ips, &$existing_egress_ips, $LOG_FILE) {
    echo "Processing existing rules for security group: $SECURITY_GROUP_NAME\n";
    file_put_contents($LOG_FILE, "Processing existing rules for security group: $SECURITY_GROUP_NAME\n", FILE_APPEND);

    $cmd = "openstack security group rule list --long \"$SECURITY_GROUP_NAME\" --format csv --column \"ID\" --column \"IP Range\" --column \"Direction\" | tail -n +2 | sed 's/\"//g'";
    exec($cmd, $output);

    foreach ($output as $line) {
        [$rule_id, $ip_range, $direction] = explode(',', $line);
        $ip = explode('/', $ip_range)[0];

        echo "Processing $direction rule for $ip : $rule_id\n";
        file_put_contents($LOG_FILE, "Processing $direction rule for $ip : $rule_id\n", FILE_APPEND);

        if (isset($new_ips[$ip])) {
            echo "  Rule will not change\n";
            file_put_contents($LOG_FILE, "  Rule will not change\n", FILE_APPEND);

            if ($direction == "ingress") {
                $existing_ingress_ips[$ip] = true;
            }
            if ($direction == "egress") {
                $existing_egress_ips[$ip] = true;
            }
        } else {
            echo "Delete $direction rule for $ip\n";
            file_put_contents($LOG_FILE, "Delete $direction rule for $ip\n", FILE_APPEND);

            exec("openstack security group rule delete \"$rule_id\" >> \"$LOG_FILE\" 2>&1");
        }
    }
}

// Function to add missing rules
function add_missing_rules($SECURITY_GROUP_NAME, $new_ips, $existing_ingress_ips, $existing_egress_ips, $LOG_FILE) {
    foreach (array_keys($new_ips) as $ip) {
        if (!isset($existing_ingress_ips[$ip])) {
            echo "Adding ingress rule for $ip\n";
            file_put_contents($LOG_FILE, "Adding ingress rule for $ip\n", FILE_APPEND);

            exec("openstack security group rule create --proto tcp --dst-port 1:65535 --remote-ip \"$ip\" \"$SECURITY_GROUP_NAME\" >> \"$LOG_FILE\" 2>&1");
        }
        if (!isset($existing_egress_ips[$ip])) {
            echo "Adding egress rule for $ip\n";
            file_put_contents($LOG_FILE, "Adding egress rule for $ip\n", FILE_APPEND);

            exec("openstack security group rule create --proto tcp --dst-port 1:65535 --egress --remote-ip \"$ip\" \"$SECURITY_GROUP_NAME\" >> \"$LOG_FILE\" 2>&1");
        }
    }
}

// Main execution
process_existing_rules($SECURITY_GROUP_NAME, $new_ips, $existing_ingress_ips, $existing_egress_ips, $LOG_FILE);
add_missing_rules($SECURITY_GROUP_NAME, $new_ips, $existing_ingress_ips, $existing_egress_ips, $LOG_FILE);

?>
