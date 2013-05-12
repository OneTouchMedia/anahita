<?php

$_GET['root']    = $root    = getenv('OPENSHIFT_REPO_DIR').'/php';
$_GET['anahita'] = $anahita = getenv('OPENSHIFT_REPO_DIR').'/anahita';
function pl($text="")
{
	if ( is_array($text) ) 
	{
		foreach($text as $key => $value) {
			$text[$key] = $key.' : '.$value;
		}
		$text = implode($text , "\n");		
	}
	print $text."\n";
}
if ( true || !file_exists($root.'/configuration.php') ) 
{
	pl();	
	pl("copying files....");
	@exec("cp -rf $anahita/code/vendors/joomla/* $root");
	pl("symlinking files....");	
	exec("php $anahita/scripts/symlink.php $root"); 
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	define( '_JEXEC', 1 );
	define('JPATH_BASE', $root );
	define( 'DS', DIRECTORY_SEPARATOR );
	require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
	// System includes
	require_once( JPATH_LIBRARIES		.DS.'joomla'.DS.'import.php');
	// Joomla! library imports;
	jimport( 'joomla.application.menu' );
	jimport( 'joomla.user.user');
	jimport( 'joomla.environment.uri' );
	jimport( 'joomla.html.html' );
	jimport( 'joomla.utilities.utility' );
	jimport( 'joomla.event.event');
	jimport( 'joomla.event.dispatcher');
	jimport( 'joomla.language.language');
	jimport( 'joomla.utilities.string' );
	extract($_GET);  
	$host = getenv('OPENSHIFT_MYSQL_DB_HOST');
	$port = getenv('OPENSHIFT_MYSQL_DB_PORT');
	$user = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
	$password = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
	$database	 = getenv('OPENSHIFT_APP_NAME').'_production';
	$prefix 	 = 'jos_';
	$errors		 = array();
	$sql_files   = array("$root/installation/sql/mysql/schema.sql","$root/installation/sql/mysql/install.sql");	
	include_once ("$root/installation/installer/helper.php");
	pl("connect to database");
	pl(array(	
		'host' => $host,
		'username' => $user,
		'password' => $password,
		'database' => $database
	));
	$db = JInstallationHelper::getDBO('mysqli',$host,$user,$password,$database,$prefix,false);
	if ( $db instanceof JException ) 
	{
		pl("****************************************************");	
		pl($db->toString());
		pl("****************************************************");	
		exit(1);
	} 
	if ( $db->select($database) ) {
		JInstallationHelper::deleteDatabase($db, $database, $prefix, $errors);		
	} 	
	JInstallationHelper::createDatabase($db, $database,true);
	$db->select($database);
	array_walk($sql_files, function($file) use($db) {
		JInstallationHelper::populateDatabase(&$db, $file, $errors);
	});
	while(!($admin_email = readline('Enter your email : ')));
	while(!($admin_pass  = readline('Enter a password : ')));	
	date_default_timezone_set('GMT');
	$vars = array(
		'DBhostname' => $host,
		'DBuserName' => $user,
		'DBpassword' => $password,
		'DBname' 	 => $database,
		'DBPrefix'   => $prefix,
		'adminEmail' => $admin_email,
		'adminPassword' => $admin_pass 
	);
	if ( !JInstallationHelper::createAdminUser($vars) ) 
	{
		pl("****************************************************");	
		pl("Counldn't create an admin user. Make sure you have entered a correct email");
		pl("****************************************************");	
		exit(1);	
	}
	write_config(array(
		'db_host'       => $host,
		'db_username'   => $user,
		'db_password'   => $password,
		'db_name' 	    => $database,
		'db_prefix'     => $prefix, 
		'secret'		=> JUserHelper::genRandomPassword(32)
	));
	exec("rm -rf $root/installation");
	pl("****************************************");
	pl("Congradulation you're done. Try logging with the following credentials");
	pl(array(
		'username' => 'admin',
		'password' => $admin_pass
	));
} else 
{

}

function write_config($config)
{
	extract($config);
	$cache 			 = 'file';
	$session_handler = 'database';
	if ( function_exists('apc_exists') ) 
	{
		$cache = 'apc';
		$session_handler = 'apc';
	}
	$config = <<<EOF
<?php	
class JConfig {

	/* Site Settings */
	var \$offline = '0';
	var \$offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';
	var \$sitename = 'Anahita';			// Name of Anahita site
	var \$editor = 'tinymce';
	var \$list_limit = '20';
	var \$legacy = '0';

	/* Database Settings */
	var \$dbtype = 'mysqli';					// Normally mysql
	var \$host = '$db_host';				// This is normally set to localhost
	var \$user = '$db_username';							// MySQL username
	var \$password = '$db_password';						// MySQL password
	var \$db = '$db_name';							// MySQL database name
	var \$dbprefix = '$db_prefix';				// Do not change unless you need to!

	/* Server Settings */
	var \$secret = '$secret'; 		//Change this to something more secure
	var \$gzip = '0';
	var \$error_reporting = '-1';
	var \$helpurl = 'http://help.joomla.org';
	var \$xmlrpc_server = '1';
	var \$ftp_host = '';
	var \$ftp_port = '';
	var \$ftp_user = '';
	var \$ftp_pass = '';
	var \$ftp_root = '';
	var \$ftp_enable = '';
	var \$tmp_path	= '/tmp';
	var \$log_path	= '/var/logs';
	var \$offset = '0';
	var \$live_site = ''; 					// Optional, Full url to Joomla install.
	var \$force_ssl = 0;		//Force areas of the site to be SSL ONLY.  0 = None, 1 = Administrator, 2 = Both Site and Administrator

	/* Session settings */
	var \$lifetime = '15';					// Session time
	var \$session_handler = '$session_handler';

	/* Mail Settings */
	var \$mailer = 'mail';
	var \$mailfrom = '';
	var \$fromname = '';
	var \$sendmail = '/usr/sbin/sendmail';
	var \$smtpauth = '0';
	var \$smtpuser = '';
	var \$smtppass = '';
	var \$smtphost = 'localhost';

	/* Cache Settings */
	var \$caching = '1';
	var \$cachetime = '15';
	var \$cache_handler = '$cache';

	/* Debug Settings */
	var \$debug      = '0';
	var \$debug_db 	= '0';
	var \$debug_lang = '0';

	/* Meta Settings */
	var \$MetaDesc = 'Anahita� is a remarkable open source platform and framework for developing various social networking services';
	var \$MetaKeys = 'Anahita';
	var \$MetaTitle = '1';
	var \$MetaAuthor = '1';

	/* SEO Settings */
	var \$sef = '0';
	var \$sef_rewrite = '0';
	var \$sef_suffix = '';

	/* Feed Settings */
	var \$feed_limit   = 10;
	var \$feed_email   = 'author';
}
?>
EOF;
file_put_contents(JPATH_BASE.'/configuration.php', $config);
}