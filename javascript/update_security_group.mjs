const fs = require('fs')
const util = require('util')
const exec = util.promisify(require('child_process').exec)

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

/*
    JavaScript notes:
    Requires ES2020 with NodeJS v20+
*/

// Works for any IP list and any security group
const SECURITY_GROUP_NAME = process.argv[2]
const LOG_FILE = './secgroup.log'

// Clear existing log
fs.writeFileSync(LOG_FILE, '')

// Read new IP addresses from file
const newIps = new Set(fs.readFileSync('./ip_addresses', 'utf8').split('\n'))

// Initialize Maps for existing rules
const existingIngressIps = new Map()
const existingEgressIps = new Map()

// Function to process existing rules
async function processExistingRules() {
  console.log(`Processing existing rules for security group: ${SECURITY_GROUP_NAME}`)
  fs.appendFileSync(
    LOG_FILE,
    `Processing existing rules for security group: ${SECURITY_GROUP_NAME}\n`
  )

  const { stdout } = await exec(
    `openstack security group rule list --long "${SECURITY_GROUP_NAME}" --format csv --column "ID" --column "IP Range" --column "Direction" | tail -n +2 | sed 's/"//g'`
  )
  const lines = stdout.split('\n')

  for (const line of lines) {
    const [ruleId, ipRange, direction] = line.split(',')
    if (!ruleId) continue

    const ip = ipRange.split('/')[0]
    console.log(`Processing ${direction} rule for ${ip} : ${ruleId}`)
    fs.appendFileSync(LOG_FILE, `Processing ${direction} rule for ${ip} : ${ruleId}\n`)

    if (newIps.has(ip)) {
      fs.appendFileSync(LOG_FILE, `  Rule will not change\n`)
      if (direction === 'ingress') {
        existingIngressIps.set(ip, true)
      } else if (direction === 'egress') {
        existingEgressIps.set(ip, true)
      }
    } else {
      console.log(`Delete ${direction} rule for ${ip}`)
      fs.appendFileSync(LOG_FILE, `Delete ${direction} rule for ${ip}\n`)
      await exec(`openstack security group rule delete "${ruleId}"`)
    }
  }
}

// Function to add missing rules
async function addMissingRules() {
  for (const ip of newIps) {
    if (!existingIngressIps.has(ip)) {
      console.log(`Adding ingress rule for ${ip}`)
      fs.appendFileSync(LOG_FILE, `Adding ingress rule for ${ip}\n`)
      await exec(
        `openstack security group rule create --proto tcp --dst-port 1:65535 --remote-ip "${ip}" "${SECURITY_GROUP_NAME}"`
      )
    }
    if (!existingEgressIps.has(ip)) {
      console.log(`Adding egress rule for ${ip}`)
      fs.appendFileSync(LOG_FILE, `Adding egress rule for ${ip}\n`)
      await exec(
        `openstack security group rule create --proto tcp --dst-port 1:65535 --egress --remote-ip "${ip}" "${SECURITY_GROUP_NAME}"`
      )
    }
  }
}

// Main execution
;(async () => {
  await processExistingRules()
  await addMissingRules()
})()
