<?php

#require_once('DB.php');

chdir('../../');
include_once("./include/auth.php");
$_SESSION['custom']=false;
include_once("./include/top_graph_header.php");
include_once("./include/config.php");

/* global vars */
$tableNames = array();
$PHP_SELF = $_SERVER['PHP_SELF'];
$set = array();
$keyCond = array();
$counters = array();
$time = gettimeofday();
$timeFormat = "Y-m-d H:i";
$fields = preg_split('/[, ]/', read_config_option("pmacct_fields"));
$optimize = read_config_option("pmacct_optimize");
if ($optimize != "on") $optimize = "off";
$sqlcond = false;
$ignored_tables = array("pmacct_tabs");

/* read Database configs */
$dbType = read_config_option("pmacct_dbType");
$dbUser = read_config_option("pmacct_dbUser");
$dbPass = read_config_option("pmacct_dbPass");
$dbHost = read_config_option("pmacct_dbHost");
$dbPort = read_config_option("pmacct_dbPort");
$dbName = read_config_option("pmacct_dbName");

/* connect to database */
#$db = DB::connect("${dbType}://${dbUser}:${dbPass}@${dbHost}:${dbPort}/${dbName}");
#if (DB::isError($db)) die($db->getMessage());
$db = pg_connect("host=".$dbHost." dbname=".$dbName." user=".$dbUser." password=".$dbPass." port=".$dbPort) or die('Could not connect: ' . pg_last_error());

/* load table names in $tableNames*/
getTableNames();
if (isset($_POST['table'])){
	$set['table'] = $_POST['table'];
}else{
	if (count($tableNames) > 0)
		$set['table'] = $tableNames[count($tableNames) - 1];
	else
		$set['table'] = null;
}
getKeyNames();

/* fill up $set with values from $_POST or defaults */
$set['start'] = ( isset($_POST['date1']) ? $_POST['date1']: date($timeFormat,$time['sec']-3600) );
$set['end'] = ( isset($_POST['date2']) ? $_POST['date2'] : date($timeFormat,$time['sec']) );
if ( isset($_POST['orderby']) && (in_array($_POST['orderby'],$counters) || $_POST['orderby'] == "None")){
	$set['orderby'] = $_POST['orderby'];
}else{
	if (count($counters) > 0)
		$set['orderby'] = reset($counters);
	else
		$set['orderby'] = '';
}
$set['sqlcond']['ip_src'] = ( isset($_POST['ipsrc']) ? $_POST['ipsrc'] : null );
$set['sqlcond']['ip_dst'] = ( isset($_POST['ipdst']) ? $_POST['ipdst'] : null );
$set['sqlcond']['port_src'] = ( isset($_POST['portsrc']) ? $_POST['portsrc'] : null );
$set['sqlcond']['port_dst'] = ( isset($_POST['portdst']) ? $_POST['portdst'] : null );
$set['sqlcond']['ip_proto'] = ( isset($_POST['ipproto']) ? $_POST['ipproto'] : null );
$set['sqlcond']['tcp_flags'] = ( isset($_POST['tcpflags']) ? $_POST['tcpflags'] : null );
$set['netdst'] = ( isset($_POST['netdst']) ? $_POST['netdst'] : null );
$set['netsrc'] = ( isset($_POST['netsrc']) ? $_POST['netsrc'] : null );
$set['usesum'] = ( isset($_POST['usesum']) ? $_POST['usesum'] : 0 );
$set['page'] = ( isset($_POST['page']) ? $_POST['page'] : 1 );
if ($set['page'] < 1 ) $set['page'] = 1;
$set['rows_page'] = ( isset($_POST['rowspage']) ? $_POST['rowspage'] : 50 );
$set['search'] = ( isset($_POST['search']) ? $_POST['search'] : 0);

/* Validate POST parameters */
# Valid IP and NET fields
#validateInput($set['sqlcond']['ip_src'], "/^(?:\d{1,3}\.){3}\d{1,3}$/");
#validateInput($set['sqlcond']['ip_dst'], "/^(?:\d{1,3}\.){3}\d{1,3}$/");
validateInput($set['sqlcond']['ip_src'], "/^[\d:\.abcdef]*$/");
validateInput($set['sqlcond']['ip_dst'], "/^[\d:\.abcdef]*$/");
#validateInput($set['netsrc'], "/^(?:\d{1,3}\.){3}\d{1,3}\/\d{2}$/");
#validateInput($set['netdst'], "/^(?:\d{1,3}\.){3}\d{1,3}\/\d{2}$/");
validateInput($set['netsrc'], "/^[\d:\.abcdef\/]*$/");
validateInput($set['netdst'], "/^[\d:\.abcdef\/]*$/");
#Valid date
validateInput($set['start'], "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/");
validateInput($set['end'], "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/");
#Check for characters, _ and numbers
validateInput($set['table'], "/^[\w\d]*$/");
validateInput($set['orderby'], "/^[\w\d]*$/");
#Check numbers
input_validate_input_number($set['sqlcond']['port_src']);
input_validate_input_number($set['sqlcond']['port_dst']);
input_validate_input_number($set['sqlcond']['ip_proto']);
input_validate_input_number($set['sqlcond']['tcp_flags']);
input_validate_input_number($set['page']);
input_validate_input_number($set['rows_page']);

function validateInput($value, $pattern){
	if (!preg_match($pattern, $value) && ($value != ""))
		die_html_input_error();
}

function getTableNames() {
	global $db,$tableNames,$ignored_tables;
	$tableNames=array();
	$resul = pg_query($db, "select relname from pg_stat_user_tables order by relname") or die('Query failed: ' . pg_last_error());
	$tableNames = pg_fetch_all_columns($resul);
	$tableNames = array_diff($tableNames, $ignored_tables);
	#$tableNames = $db->getListOf('tables');
	#if (DB::isError($tableNames)) die("getTableNames(): " . $tableNames->getMessage());
}


function getKeyNames() {
	global $db,$set,$counters,$fields;
	$keyNames = array();
	$counterFields = array("bytes", "packets", "flows");
	$resul = pg_query($db, "SELECT * FROM " . dq($set['table']) . " LIMIT 0") or die('Query failed: ' . pg_last_error());
	$int_colums = pg_num_fields($resul);
	for ( $col = 0; $col < $int_colums; $col++ ) {
		array_push($keyNames , pg_field_name( $resul, $col));
	}
	#$q = $db->query("SELECT * FROM " . dq($set['table']) . " LIMIT 0");
	#if (DB::isError($q)) die("getKeyNames(): " . $q->getMessage());
	#$info = $db->tableInfo($q);
	#foreach ($info as $v) array_push($keyNames, $v['name']);
	if ($fields[0] == '*')
		$fields = $keyNames;
	else
		$fields = array_intersect($fields, $keyNames);
	$counters = array_intersect($counterFields, $fields);
	$fields = array_diff($fields, $counters);
}

function selectedOptions($select, $values) {
	foreach($values as $v) {
		echo "<option ";
		if ($v == $select) echo "selected=\"selected\"";
		echo ">$v</option>\n";
        }
}

function dq($string) {
	global $dbType;
	if ($dbType == "mysql")
		return(str_replace(" ", "_", $string));
	return("\"" . str_replace("\"", "\"\"", $string) . "\"");
}

function sq($string) {
	return("'" . str_replace("'", "''", $string) . "'");
}

/* Create the SQL query */
function createSQL(){
	global $set, $counters, $fields, $sqlcond;
	$timeField = "stamp_inserted";

	$page = $set['page'];
	$per_row = $set['rows_page'];
	$sql = "SELECT ";
	$i = 0;
	# Find select fields
	$sel_fields = "";
	# if SUM, Remove timeField from fields
	if (isset($set['usesum']) && $set['usesum'] == 1){
		$aux = array($timeField);
		$fields = array_diff($fields, $aux);
	}
	# Make the select fields
	foreach ($fields as $v) {
		$sel_fields .= $v;
		if ($i != (count($fields) - 1))
			$sel_fields .= ", ";
		$i++;
	}
	$sql .= $sel_fields;
	# Check for sum()
        if (isset($set['usesum']) && $set['usesum'] == 1){
		foreach($counters as $v) $sql .= ",SUM(" . dq($v) . ")";
	}else{
		foreach($counters as $v) $sql .= "," .$v;
	}
        $sql .= " FROM " . dq($set['table']);
        $sql .= " WHERE " . dq($timeField) . ">=" . sq($set['start']);
	$sql .= " AND " . dq($timeField) . "<" . sq($set['end']);
	# Check for sql cond fields
        if (isset($set['sqlcond'])){
		foreach($set['sqlcond'] as $i => $value){
			if ($value != null){
				$sql .= " AND ( " . dq($i) . " = " . sq($value) . " )";
				$sqlcond = true;
			}
		}
	}
	# Calculando somente para um .0
	if (isset($set['netsrc']) AND ($set['netsrc'] != "")){
		$sqlcond = true;
		$sql .= " AND ( ip_src << inet '".$set['netsrc']."' )";
		#$netsrc = preg_split('/[.\/]/', $set['netsrc']);
		#$prefix = "";
		# Find prefix ex. 192.168.1 for 192.168.1.0/24
		#$prefix .= $netsrc[0] . "." . $netsrc[1] . "." . $netsrc[2] . ".";
		#$subnum = pow(2 ,(32 - $netsrc[4])) - 1 + $netsrc[3];
		#$sql .= "'" . $prefix . $netsrc[3] . "') AND ( ip_src < '" . $prefix . $subnum . "')";
	}if (isset($set['netdst']) AND ($set['netdst'] != "")){
		$sqlcond = true;
		$sql .= " AND ( ip_dst << inet '".$set['netdst']."' )";
		#$sql .= " AND ( ip_dst > '";
		#$netdst = preg_split('/[.\/]/', $set['netdst']);
		#$prefix = "";
		# Find prefix ex. 192.168.1 for 192.168.1.0/24
		#$prefix .= $netdst[0] . "." . $netdst[1] . "." . $netdst[2] . ".";
		#$subnum = pow(2 ,(32 - $netdst[4])) - 1 + $netdst[3];
		#$sql .=  $prefix . $netdst[3] . "') AND ( ip_dst < '" . $prefix . $subnum . "')";
	}

	if (isset($set['usesum']) && $set['usesum'] == 1) $sql .= " GROUP BY " . $sel_fields;
	if (isset($set['orderby']) && $set['orderby'] != "" && $set['orderby'] != "None"){
		if (isset($set['usesum']) && $set['usesum'] == 1){
			$sql .= " ORDER BY SUM(" . dq($set['orderby']) . ") DESC";
		}else{
			$sql .= " ORDER BY " . $set['orderby'] . " DESC";
		}
	}
	// SET LIMIT
	$start = ($page-1)*$per_row;
	$sql .= " LIMIT $per_row OFFSET $start";
	return $sql;
}	

function validateIPv6($IP) 
{ 
    // fast exit for localhost 
    if (strlen($IP) < 3) 
        return $IP == '::'; 

    // Check if part is in IPv4 format 
    if (strpos($IP, '.')) 
    { 
        $lastcolon = strrpos($IP, ':'); 
        if (!($lastcolon && validateIPv4(substr($IP, $lastcolon + 1)))) 
            return false; 

        // replace IPv4 part with dummy 
        $IP = substr($IP, 0, $lastcolon) . ':0:0'; 
    } 

    // check uncompressed 
    if (strpos($IP, '::') === false) 
    { 
        return preg_match('/^(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}$/i', $IP); 
    } 

    // check colon-count for compressed format 
    if (substr_count($IP, ':') < 8) 
    { 
        return preg_match('/^(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?$/i', $IP); 
    } 

    return false; 
} 

function is_ip($ip){
	#if(filter_var($ip, FILTER_VALIDATE_IP))
	if (preg_match("/^(?:\d{1,3}\.){3}\d{1,3}$/", $ip) || validateIPv6($ip))
		return true;
	return false;
}

$in_progress = 0;
# Register a function to be called at shutdown:
function halted()
{
	global $db, $in_progress;
	if ($in_progress) {
		pg_cancel_query($db);
    	}
}
register_shutdown_function('halted');

function print_pmacct_table() {
	global $counters,$set,$db,$fields,$in_progress;

	// CHECK REFRESH
	if ($set['search'] != 1)
		return;

	$page = $set['page'];
	$per_row = $set['rows_page'];
	$whois = read_config_option("pmacct_whois");

	// DO THE SEARCH
	$sql = createSQL();
	pg_send_query($db, $sql);
	$in_progress = 1;
	# Now we loop waiting for the query to complete or the user to cancel.
	# Display a message to the user telling how long it has been.
	flush();
	while (pg_connection_busy($db)) {
    		sleep(2);
		# The print is here just to make it work.
		print " ";
		flush();
	}
	# All done, and it took $delta seconds.

	# Don't let the shutdown handler try to cancel the query:
	$in_progress = 0;
	$resul = pg_get_result($db);
	$total = pg_num_rows($resul);
	#$q = $db->query($sql);
	#if (DB::isError($q)) die("printTable(): " . $q->getMessage());
	#$total = $q->numRows();
	if ($total != 0)
		$total_rows = intval($total / $per_row) +1;
	else
		$total_rows = 1;

	// PRINT NUM ROWS BAR	
	print '<br><table align="center" width="100%" cellpadding=1 cellspacing=0 border=0 bgcolor="#00438C"><tr><td>';
	print html_nav_bar_pmacct('00438C', 20, $page, $per_row, $total);
	print "<tr bgcolor='#6d88ad' >";

	// PRINT TABLE FIELDS
	foreach ($fields as $value) print "<td style='padding: 4px; margin: 4px;'><font color='#FFFFFF'><b>" . $value . "</b></font></td>";
	foreach ($counters as $value) print "<td style='padding: 4px; margin: 4px;'><font color='#FFFFFF'><b>" . $value . "</b></font></td>";

	// PRINT ROWS
	$bg = "#E7E9F2";
	while ($row=pg_fetch_row($resul)) {
	#while ($q->fetchInto($row)) {
		if ($bg == '#E7E9F2')
			$bg = '#F5F5F5';
		else
			$bg = '#E7E9F2';
		print "<tr bgcolor='$bg' >";
		foreach($row as $v){
			if ( ($whois == "on") && (is_ip($v))){
				//TODO AUMENTAR TAMANHO DA JANELA
				print '<td><a href="javascript:void(0);" onclick="return overlib(\'&lt;iframe height=270 src=/cacti/plugins/pmacct/whois.php?ip='.$v.' &gt;\', STICKY, CLOSECLICK, CAPTION, \'Whois '.$v.'\', VAUTO, HAUTO, OFFSETY, -100, FGCOLOR, \'#F5F5F5\');" onmouseout="return nd();">'.$v.'</a></td>';
			}else{
				echo '<td>'.$v.'</td>';
			}
		}
		print "</tr>";
	}
	if ($total == 0)
		print "<tr bgcolor='$bg'><td style='padding: 4px; margin: 4px;' colspan=11><center>There are no Hosts to display!</center></td></tr>";

	print html_nav_bar_pmacct('00438C', 10, $page, $per_row, $total);
}

function html_nav_bar_pmacct($background_color, $colspan, $current_page, $rows_per_page, $total_rows) {
?>
<tr bgcolor='#<?php print $background_color;?>' class='noprint'>
<td colspan='<?php print $colspan;?>'>
<table width='100%' cellspacing='0' cellpadding='3' border='0'>
<tr>
<td align='left' class='textHeaderDark'>
<strong>&lt;&lt; <?php if ($current_page > 1) { print "<a class='linkOverDark' href='javascript:void(1);' onClick='previousPage();'>"; } print "Previous"; if ($current_page > 1) { print "</a>"; } ?></strong>
</td>
<td align='center' class='textHeaderDark'>
	Showing Rows <?php print (($rows_per_page*($current_page-1))+1);?> to <?php print ($rows_per_page*$current_page);?>
</td>
<td align='right' class='textHeaderDark'>
<strong><?php if ($rows_per_page <= $total_rows) { print '<a class="linkOverDark" href="javascript:void(1);" onClick="nextPage();">'; } print "Next"; if ($rows_per_page <= $total_rows) { print "</a>"; } ?> &gt;&gt;</strong>
</td>
</tr>
</table>
</td>
</tr>
<?php
}

?>
