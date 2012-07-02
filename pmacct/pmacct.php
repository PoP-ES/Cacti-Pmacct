<?php

require_once('functions.inc.php');
$_SESSION["sess_current_date1"] = date($timeFormat,$time['sec']-3600) ;
$_SESSION["sess_current_date2"] = date($timeFormat,$time['sec']) ;
$_SESSION["sess_current_timespan"] = 2;
$_SESSION["sess_current_timeshift"] = 1;

?>
<html>
<head>
<script type="text/javascript" src="overlib.js"></script>
<script type="text/javascript" src="time.js"></script>
<script type="text/javascript"> 
function searchquery(){
	document.getElementById('search').value = 1;
	document.getElementById('page').value = 1;
}
function cancelquery(){
	if(navigator.appName == "Microsoft Internet Explorer")
	{
		window.document.execCommand('Stop');
	}else{
		window.stop();
	}
}
function nextPage(){
	page = document.getElementById('page').value;
	page = parseInt(page) + 1;
	document.getElementById('page').value = page;
	document.getElementById('search').value = 1;
	document.getElementById('frm').submit();
}
function previousPage(){
	page = document.getElementById('page').value;
	page = parseInt(page) - 1;
	document.getElementById('page').value = page;
	document.getElementById('search').value = 1;
	document.getElementById('frm').submit();
}
function checkform ( form ){
	// CHECK FORM VALUES
	var date2 = new Date(form.date2.value);
	var date1 = new Date(form.date1.value);
	// date in hours
	var date3 = (date2 - date1) / 3600000;
	if (date3 < 0){
		alert( "Start date should be smaller than end date." );
		form.date1.focus();
		return false ;
	}
	var optimize = "<?php echo $optimize ?>";
	if ( (form.orderby.value != "None") && (optimize == "on")){
		if (date3 > 5){
			var conf = confirm("This query can be SLOW because of ORDER BY. Do you want to run it?");
			if (conf != true) return false;
		}
	}
	if ( (form.ipsrc.value != "") && (form.netsrc.value != "")) {
		alert( "IP SOURCE and NET SOURCE cannot be used together." );
		form.ipsrc.focus();
		return false ;
	}
	if ( (form.ipdst.value != "") && (form.netdst.value != "")) {
		alert( "IP DESTINATION and NET DESTINATION cannot be used together." );
		form.ipdst.focus();
		return false ;
	}
	if ( (form.usesum.checked) && (optimize == "on")){
		var sum = confirm("SUM is not recommend for LARGE TABLES. Do you really want to use it?");
		if (sum != true) form.usesum.value = 0;
	}
	return true ;
}
</script>
<script type='text/javascript'>
	// Initialize the calendar
	calendar=null;
	// This function displays the calendar associated to the input field 'id'
	function showCalendar(id) {
		var el = document.getElementById(id);
		if (calendar != null) {
			// we already have some calendar created
			calendar.hide();  // so we hide it first.
		} else {
			// first-time call, create the calendar.
			var cal = new Calendar(true, null, selected, closeHandler);
			cal.weekNumbers = false;  // Do not display the week number
			cal.showsTime = true;     // Display the time
			cal.time24 = true;        // Hours have a 24 hours format
			cal.showsOtherMonths = false;    // Just the current month is displayed
			calendar = cal;                  // remember it in the global var
			cal.setRange(1900, 2070);        // min/max year allowed.
			cal.create();
		}
		calendar.setDateFormat('%Y-%m-%d %H:%M');    // set the specified date format
		calendar.parseDate(el.value);                // try to parse the text in field
		calendar.sel = el;                           // inform it what input field we use
		// Display the calendar below the input field
		calendar.showAtElement(el, "Br");        // show the calendar
		return false;
	}

	// This function update the date in the input field when selected
	function selected(cal, date) {
		cal.sel.value = date;      // just update the date in the input field.
	}
	// This function gets called when the end-user clicks on the 'Close' button.
	// It just hides the calendar without destroying it.
	function closeHandler(cal) {
		cal.hide();                        // hide the calendar
		calendar = null;
	}
</script>
<script type="text/javascript">
<!--
        function applyTimespanFilterChange(objForm) {
                now = new Date();
                objForm.date2.value = now.format("Y-m-d h:i");
                objForm.date1.value = get_timespan(objForm.predefined_timespan.value);
        }
        function addTimeshift(objForm) {
                objForm.date2.value = add_timeshift(objForm.predefined_timeshift.value,objForm.date2.value);
                objForm.date1.value = add_timeshift(objForm.predefined_timeshift.value,objForm.date1.value);
                return false;
        }
        function delTimeshift(objForm) {
                objForm.date2.value = del_timeshift(objForm.predefined_timeshift.value,objForm.date2.value);
                objForm.date1.value = del_timeshift(objForm.predefined_timeshift.value,objForm.date1.value);
                return false;
        }
-->
</script></head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<?php
print "<form name='form_events' id='frm' method=POST action='" . $config['url_path'] . "plugins/pmacct/pmacct.php' onsubmit='return checkform(this);'>";
print '<input type="hidden" id="page" name="page" value =' . $set['page'] . '>';
print '<input type="hidden" id="search" name="search" value=0>';
print "\n<center><table width='100%' cellspacing=1 cellpadding=1 bgcolor='#E5E5E5'>\n";
print "<tr bgcolor='#00438C'><td colspan=100%><font color='#FFFFFF'><b>General Filters</b></font></td></tr>";
print "<tr >";
// FILTER TIME INTERVAL 
?>

<td width=5>&nbsp;<strong>Presets:</strong>&nbsp;
<select name='predefined_timespan' onchange="applyTimespanFilterChange(document.form_events)">
<?php
$graph_timespans = array(
        1 => "Last Half Hour",
        2 => "Last Hour",
        3 => "Last 2 Hours",
        4 => "Last 4 Hours",
        5 =>"Last 6 Hours",
        6 =>"Last 12 Hours",
        7 =>"Last Day",
        8 =>"Last 2 Days",
        9 =>"Last 3 Days",
        10 =>"Last 4 Days",
        11 =>"Last Week",
        12 =>"Last 2 Weeks",
);

        $start_val = 1;
        $end_val = sizeof($graph_timespans)+1;

        if (sizeof($graph_timespans) > 0) {
                for ($value=$start_val; $value < $end_val; $value++) {
                        print "<option value='$value'"; if ($_SESSION["sess_current_timespan"] == $value) { print " selected"; } print ">" . title_trim($graph_timespans[$value], 40) . "</option>\n";
                }
        }
?>
</select></td>
<td nowrap style='white-space: nowrap;'>&nbsp;<strong>From:</strong>&nbsp;
<input type='text' name='date1' id='date1' title='Graph Begin Timestamp' size='15' value='<?php print $set['start'];?>'>
&nbsp;<input type='image' src='../../images/calendar.gif' align='middle' alt='Start date selector' title='Start date selector' onclick="return showCalendar('date1');"></td>
<td nowrap style='white-space: nowrap;'>&nbsp;<strong>To:</strong>&nbsp;
<input type='text' name='date2' id='date2' title='Graph End Timestamp' size='15' value='<?php print $set['end'];?>'>
&nbsp;<input type='image' src='../../images/calendar.gif' align='middle' alt='End date selector' title='End date selector' onclick="return showCalendar('date2');"></td>

<td nowrap style='white-space: nowrap;'>&nbsp;<input type='image' name='move_left' src='../../images/move_left.gif' align='middle' alt='Left' title='Shift Left' onclick="return delTimeshift(document.form_events);">
&nbsp;<select name='predefined_timeshift' title='Define Shifting Interval'>
<?php
$graph_timeshifts = array(
        1 => "30 Min",
        2 => "1 Hour",
        3 => "12 Hours",
        4 => "1 Day",
        5 => "1 Week",
        6 => "1 Month",
        7 => "6 Months",
        8 => "1 Year",
);

$start_val = 1;
$end_val = sizeof($graph_timeshifts)+1;
if (sizeof($graph_timeshifts) > 0) {
        for ($shift_value=$start_val; $shift_value < $end_val; $shift_value++) {
                print "<option value='$shift_value'"; if ($_SESSION["sess_current_timeshift"] == $shift_value) { print " selected"; } print ">" . title_trim($graph_timeshifts[$shift_value], 40) . "</option>\n";
        }
}
?>
</select>
&nbsp;<input type='image' name='move_right' src='../../images/move_right.gif' align='middle' alt='Right' title='Shift Right' onclick="return addTimeshift(document.form_events);"></td>
<?php
// FILTER TABLE
print "<td >&nbsp;<strong>Table:&nbsp;</strong>";
print '<select name="table" size="1">';
selectedOptions($set['table'], $tableNames);
print '</td>';
// FILTER ORDER COUNTER
print "<td >&nbsp;<strong>Order by:&nbsp;</strong>";
print '<select name="orderby" size="1">';
selectedOptions($set['orderby'], $counters);
print "<option "; if ($set['orderby'] == "None") echo "selected=\"selected\""; echo ">None</option>\n";
print '</select></td>';
// FILTER USE SUM
print "<td >&nbsp;<strong>SUM:&nbsp;</strong>";
print '<input type="checkbox" name="usesum" value=1 '; if ($set['usesum']) echo "CHECKED"; print ' />';
print '</td>';
print "<td >&nbsp;<strong>ROWS PER PAGE:&nbsp;</strong>";
print '<input size=4 type="text" name="rowspage" value="' . $set['rows_page'] . '" />';
print '</td>';
print "<td>&nbsp;<input type='submit' id='refresh' name='refresh' value='Search' title='Refresh selected time span' onclick='searchquery();'>
	<input type='button' name='button_cancel' value='Cancel' title='Cancel current query' onclick='cancelquery();'></td>";
print "</tr>";
######### SQL CONDITION FILTERS ####
print "<tr bgcolor='#00438C'><td colspan=100%><font color='#FFFFFF'><b>Sql Condition Filters</b></font></td></tr>";
print "<tr>";
// FILTER SQL CONDITION
print "<td >&nbsp;<strong>IP SOURCE:&nbsp;</strong>";
print '<input size=15 type="text" name="ipsrc" value="' . $set['sqlcond']['ip_src'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>IP DESTINATION:&nbsp;</strong>";
print '<input size=15 type="text" name="ipdst" value="' . $set['sqlcond']['ip_dst'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>NET SOURCE:&nbsp;</strong>";
print '<input size=15 type="text" name="netsrc" value="' . $set['netsrc'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>NET DEST:&nbsp;</strong>";
print '<input size=15 type="text" name="netdst" value="' . $set['netdst'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>PORT SOURCE:&nbsp;</strong>";
print '<input size=7 type="text" name="portsrc" value="' . $set['sqlcond']['port_src'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>PORT DEST:&nbsp;</strong>";
print '<input size=7 type="text" name="portdst" value="' . $set['sqlcond']['port_dst'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>IP PROTOCOL:&nbsp;</strong>";
print '<input size=4 type="text" name="ipproto" value="' . $set['sqlcond']['ip_proto'] . '" />';
print "</td>";
print "<td >&nbsp;<strong>TCP FLAGS:&nbsp;</strong>";
print '<input size=4 type="text" name="tcpflash" value="' . $set['sqlcond']['tcp_flags'] . '" />';
print "</td>";
print "</tr>";

print '</form>';
print '</table></center>';

print_pmacct_table();

print '</body></html>';
?>
