<?php
/**
 * This is the configuration file for the Propagator application.  All of your
 * environment specific settings will go here, and there should not be any need
 * edit other files.
 *
 * @author Shlomi Noach <snoach@outbrain.com>
 * @license Apache 2.0 license.  See LICENSE document for more info
 * @created 2013-10-25
 *
 **/


$conf['db'] = array(
	'host'	=> '127.0.0.1',
	'port'	=> 3306,
	'db'	=> 'propagator',
	'user'	=> 'test_propagator',
	'password' => 'qpzm1234'
);

$conf['default_action'] = 'about';

// Default user. Propagator is assumed to work with ldap, so credentials are passed to PHP via htaccess.
// The only alternative to ldap at this stage is to simply auto-assign a login
$conf['default_login'] = 'gromit';

//
// Accounts with DBA privileges: mark deployments as "manually deployed", restart deployments, view topologies
$conf['dbas'] = array('gromit', 'penguin');
$conf['blocked'] = array('badboy');
$conf['restrict_credentials_input_to_dbas'] = true;

// By default production deployments are 'manual', such that the user has to explicitly click the "reload" button
// so as to deploy. Change to 'automatic' in you have great faith
$conf['instance_type_deployment'] = array(
		'production' 	=> 'manual',
		'build' 		=> 'automatic',
		'qa' 			=> 'automatic',
		'dev' 			=> 'automatic'
);

// Deployments to these environments will require a DBA's approval (see $conf['dbas']). 
// For a DBA user it is meaningless. For other users this means they will need to make a request
// to a DBA.
// When commented or when array is empty two-step approval is inactive.
//$conf['two_step_approval_environments'] = array(
//		'production', 
//		'build'
//);

//
// Should script deployment history be visible to all users? If 'false' then only to 'dbas' group (see above);
$conf['history_visible_to_all'] = true;

// patterns to highlight on topology view. Patterns are matched against host names.
// Each matching pattern gets its own color via css. Search propagator.css for span.palette-*
$conf['instance_topology_pattern_colorify'] = array (
	"/-1[0-9]{4}-/",
	"/-2[0-9]{4}-/",
	"/-3[0-9]{4}-/",
	"/-4[0-9]{4}-/",
	"/localhost/"
);

// Directory where event listeners are located
// $conf['event_listener_dir'] = './listeners/';

// Assigning event listeners to events/hooks. Below is a sample list of event
// handlers. Uncomment and change this list to add in your own listeners
//$conf['event_listeners'] = array(
//    array(
//        'event' => array('new_script', redeploy_script'),
//        'class' => 'new_script',
//        'file'  => 'new_script.php',
//    ),
//    array(
//        'event' => 'execute_script',
//        'class' => 'execute_script',
//        'file'  => 'execute_script.php',
//    ),
//);

$conf['pt-slave-find'] = '';

$conf['mysqldiff'] = '/home/snoach/dev/outbrain/trunk/production/tools/mysql-utilities/mysqldiff --difftype=sql --force --changes-for=server2 --skip-table-options ';



// Choose how propagator gets credentials to deployment servers (MySQL, Hive, ...)
// If empty/undefined, then user is prompted to enter credentials. These must apply on any server the user wishes
// to deploy to (though the user is allowed to resubmit credentials and execute on particular servers as she pleases)
//
// Otherwise, provide path to passwords file. This file will list host credentials in plaintext, so file's
// permissions/ACL should be as strict as possible, ideally only readable by the apache user (or whichever user is running
// the PHP code).
// Make sure you understand the impact of having plaintext credentials!
// Even if credentials file is provided, the user is allowed to override by submiting his own credentials.
//include "propagator-hosts.conf.php";


/**
 * end of configuration settings
 */
?>