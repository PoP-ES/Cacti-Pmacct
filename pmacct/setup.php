<?php

function plugin_init_pmacct() {
	global $plugin_hooks;
	$plugin_hooks['config_arrays']['pmacct'] = 'pmacct_config_arrays';
	$plugin_hooks['draw_navigation_text']['pmacct'] = 'pmacct_draw_navigation_text';
	$plugin_hooks['config_settings']['pmacct'] = 'pmacct_config_settings';
	$plugin_hooks['top_header_tabs']['pmacct'] = 'pmacct_show_tab';
	$plugin_hooks['top_graph_header_tabs']['pmacct'] = 'pmacct_show_tab';
	$plugin_hooks['top_graph_refresh']['pmacct'] = 'pmacct_graph_refresh';
}

function pmacct_graph_refresh(){
        return '';
}

function pmacct_version () {
	return array( 'name' 	=> 'pmacctplugin',
			'version' 	=> '0.1',
			'longname'	=> 'pmacctPlugin',
			'author'	=> 'Sergio R Charpinel Junior',
			'homepage'	=> 'http://cactiusers.org',
			'email'	=> 'sergiocharpinel@gmail.com',
			'url'		=> 'http://cactiusers.org/cacti/versions.php'
			);
}

function pmacct_config_arrays () {
	global $user_auth_realms, $user_auth_realm_filenames, $menu;

	$user_auth_realms[68]='Pmacct Plugin';
	$user_auth_realm_filenames['pmacct.php'] = 68;
	$user_auth_realm_filenames['whois.php'] = 68;
	$user_auth_realm_filenames['search.php'] = 68;
	$user_auth_realm_filenames['cancel.php'] = 68;
}
function pmacct_draw_navigation_text ($nav) {
	$nav["pmacct.php:"] = array("title" => "Pmacct Plugin", "mapping" => "index.php:", "url" => "pmacct.php", "level" => "1");
	return $nav;
}

function pmacct_config_settings () {
	global $settings, $tabs;
	$tabs["pmacct"] = "Pmacct";
	$temp = array(
		"pmacct_database_header" => array(
		"friendly_name" => "Database Options",
		"method" => "spacer",
		),
		#	"pmacct_dbType" => array(
		#	"friendly_name" => "Database Type",
		#	"description" => "Database Type. Eg.: mysql, pgsql",
		#	"method" => "textbox",
		#	"max_length" => 255,
		#	"default" => "pgsql"
		#),
			"pmacct_dbUser" => array(
			"friendly_name" => "Database User",
			"description" => "Database Username",
			"method" => "textbox",
			"max_length" => 255,
			"default" => "pmacct"
		),
			"pmacct_dbPass" => array(
			"friendly_name" => "Database Password",
			"description" => "Database Password",
			"method" => "textbox_password",
			"max_length" => "255"
		),
			"pmacct_dbHost" => array(
			"friendly_name" => "Database Host",
			"description" => "Database Hostname",
			"method" => "textbox",
			"max_length" => 255,
			"default" => "localhost"
		),
			"pmacct_dbPort" => array(
			"friendly_name" => "Database Port",
			"description" => "Database Port",
			"method" => "textbox",
			"max_length" => 255,
			"default" => "5432"
		),
			"pmacct_dbName" => array(
			"friendly_name" => "Database Name",
			"description" => "Database Name",
			"method" => "textbox",
			"max_length" => 255,
			"default" => "pmacct"
		),
		"pmacct_header" => array(
		"friendly_name" => "Pmacct Options",
		"method" => "spacer",
		),
			"pmacct_fields" => array(
			"friendly_name" => "Fields",
			"description" => "Name of the Fields to search",
			"method" => "textbox",
			"max_length" => 255,
			"default" => "*"
		),
		"pmacct_options_header" => array(
		"friendly_name" => "Others Options",
		"method" => "spacer",
		),
			"pmacct_optimize" => array(
			"friendly_name" => "Show hints",
			"description" => "Show hints about low querys",
			"method" => "checkbox",
			"default" => "off"
		),
			"pmacct_whois" => array(
			"friendly_name" => "Whois",
			"description" => "Enable whois link",
			"method" => "checkbox",
			"default" => "on"
		),
	);
	if (isset($settings["pmacct"]))
		$settings["pmacct"] = array_merge($settings["pmacct"], $temp);
    	else
 	        $settings["pmacct"]=$temp;
}

function pmacct_show_tab () {
	global $config, $user_auth_realms, $user_auth_realm_filenames;
	$realm_id2 = 0;
	//make sure user has rights to tab
	if (isset($user_auth_realm_filenames{basename('pmacct.php')})) {
		$realm_id2 = $user_auth_realm_filenames{basename('pmacct.php')};
	}
	if ((db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id='" . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2))) {
		print '<a href="' . $config['url_path'] . 'plugins/pmacct/pmacct.php"><img src="' . $config['url_path'] . 'plugins/pmacct/images/tab_pmacct' . ((substr(basename($_SERVER["PHP_SELF"]),0,11) == "pmacct.php") ? "_down": "") . '.gif" alt="Pmacct" align="absmiddle" border="0"></a>';
	}
}
