<?
chdir('../../');
include_once("./include/auth.php");
include_once("./include/config.php");

header('Content-Type: text/html; charset=utf-8');
?>

<html>
<body>
<?php

/* 
WHOIS SERVERS

RIPE NCC (Redes IP europeias, whois.ripe.net) para a Europa;
APNIC (Asia Pacific Network Information Centre), whois.apnic.net para a Ásia e o Pacífico
ARIN (American Registry for Internet Numbers, whois.arin.net) para a América do Norte e a África Subsariana;
LACNIC (Regional Latin-American and Caribbean IP Address Registry, whois.lacnic.net) para a América latina e as Caraíbas;
INTERNIC (whois.internic.net) para as outras partes do globo.
*/

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

$ip = $_GET['ip'];
if (!(preg_match("/^(?:\d{1,3}\.){3}\d{1,3}$/",$ip) || validateIPv6($ip)))
	die("Error in input validation");

function simple_whois($query)
{
        #return utf8_encode(system("whois -h whois.arin.net $query"));
        exec("/usr/bin/whois -h whois.arin.net $query",$answer);
        return $answer;
}


$response = simple_whois($ip);
// filter fields
$fields = array("inetnum", "owner", "responsible", "country", "inetrev", "person", "e-mail", "phone", "remarks", "descr", "netname", "orgname", "city", "netrange", "netname", "stateprov", "OrgNOCEmail", "orgabuseemail", "orgabusephone", "orgabusename", "orgabusehandle");

foreach ($response as &$value)
{
        $value = utf8_encode($value);
        if (preg_match("/(\S*):( *)(.*)/", $value, $results) > 0)
        {
                if (in_array(strtolower($results[1]), $fields))
                        print $results[1] . ":" . $results[3] . "<br>";
        }
}

// print dns reverse
$dns = split(" ",exec("host ".$ip));
print "Host: " . end($dns);
print "\n";
?>

</body>
</html>
