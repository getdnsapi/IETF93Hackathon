<?php

header('Content-Type: text/html');

#ob_end_flush();

print("<html><body>
Enter an address for a publicly resolver here:
<form><input type=\"text\" name=\"addr\"><input type=\"submit\"></form>
<font size=\"-1\">Private resolvers can not be assessed with this web application.</font><br><br>
");
if (!$_REQUEST["addr"]) {
	$success = True;
	goto error;
}
$success = False;

$context = 0;
if (php_getdns_context_create($context, True)) {
	goto error;
}

if (php_getdns_context_set_resolution_type($context, GETDNS_RESOLUTION_STUB)) {
	goto error_destroy_context;
}

$txt_addr =  $_REQUEST["addr"] ? $_REQUEST["addr"] : "8.8.8.8";
$addr = inet_pton($txt_addr);

print("Results for $txt_addr<br>");
$upstreamsArr = array(
	0 => array("address_data" => $addr,
		   "address_type" => strlen($addr) == 4 ? "IPv4" : "IPv6")
	);

$upstreams = 0;
if (php_getdns_util_convert_array($upstreamsArr, $upstreams)) {
	goto error_destroy_response;
}

if (php_getdns_context_set_upstream_recursive_servers($context, $upstreams)) {
	goto error_destroy_upstreams;
}

$extensionsArr = array( "dnssec_return_status" => GETDNS_EXTENSION_TRUE
                      , "dnssec_return_validation_chain" => GETDNS_EXTENSION_TRUE);
$extensions = 0;
if (php_getdns_util_convert_array($extensionsArr, $extensions)) {
	goto error_destroy_upstreams;
}

$grade = 0;
$respDict = 0;
if (php_getdns_general_sync($context,
    "alg-8-nsec3.dnssec-test.org", GETDNS_RRTYPE_SOA, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] > 0) {
	$grade += 1;
	print("Query for alg-8-nsec3.dnssec-test.org returned answers: $grade<br>");
} else {
	print("Query for alg-8-nsec3.dnssec-test.org returned no answers: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	print("Query for alg-8-nsec3.dnssec-test.org had secure answer: $grade<br>");
} else {
	print("Query for alg-8-nsec3.dnssec-test.org did not have an secure answer: $grade<br>");
}

php_getdns_dict_destroy($respDict);
$respDict = 0;
if (php_getdns_general_sync($context,
    "realy-doesnotexist.dnssec-test.org.", GETDNS_RRTYPE_A, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] == 0) {
	$grade += 1;
	print("Query for realy-doesnotexist.dnssec-test.org. did not return answers: $grade<br>");
} else {
	print("Query for realy-doesnotexist.dnssec-test.org. did return answers: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	print("Query for realy-doesnotexist.dnssec-test.org. was secure: $grade<br>");
} else {
	print("Query for realy-doesnotexist.dnssec-test.org. was not secure: $grade<br>");
}
php_getdns_dict_destroy($respDict);
$respDict = 0;
if (php_getdns_general_sync($context,
    "alg-13-nsec3.dnssec-test.org", GETDNS_RRTYPE_SOA, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] > 0) {
	$grade += 1;
	print("Query for alg-13-nsec3.dnssec-test.org returned answers: $grade<br>");
} else {
	print("Query for alg-13-nsec3.dnssec-test.org returned no answers: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_SECURE) {
	$grade += 1;
	print("Query for alg-13-nsec3.dnssec-test.org had secure answer: $grade<br>");
} else {
	print("Query for alg-13-nsec3.dnssec-test.org did not have an secure answer: $grade<br>");
}
php_getdns_dict_destroy($respDict);
$respDict = 0;
if (php_getdns_general_sync($context,
    "dnssec-failed.org", GETDNS_RRTYPE_SOA, $extensions, $respDict)) {
	goto error_destroy_upstreams;
}
$respArr = array();
if (php_getdns_util_convert_dict($respDict, $respArr)) {
	goto error_destroy_response;
}
if ($respArr["replies_tree"][0]["header"]["ancount"] > 0) {
	$grade += 1;
	print("Query for dnssec-failed.org returned answers: $grade<br>");
} else {
	print("Query for dnssec-failed.org returned no answers: $grade<br>");
}
if ($respArr["replies_tree"][0]["dnssec_status"] == GETDNS_DNSSEC_BOGUS) {
	$grade += 1;
	print("Query for dnssec-failed.org had bogus answer: $grade<br>");
} else {
	print("Query for dnssec-failed.org did not have a bogus answer: $grade<br>");
}

$success = True;

error_destroy_response:
	php_getdns_dict_destroy($respDict);
error_destroy_upstreams:
	php_getdns_list_destroy($upstreams);
error_destroy_context:
	php_getdns_context_destroy($context);
error:
?>
<br>
Also try:
<table>
<tr><td>Dyn Internet Guide</td><td><a href="?addr=216.146.35.35">216.146.35.35</a></td><td><a href="?addr=216.146.36.36">216.146.36.36</a></td></tr>
<tr><td>Level 3</td><td><a href="?addr=209.244.0.3">209.244.0.3</a></td><td><a href="?addr=209.244.0.4">209.244.0.4</a></td></tr>
<tr><td>Google</td><td><a href="?addr=8.8.8.8">8.8.8.8</a></td><td><a href="?addr=8.8.4.4">8.8.4.4</a></td></tr>
<tr><td>Comodo Secure DNS</td><td><a href="?addr=8.26.56.26">8.26.56.26</a></td><td><a href="?addr=8.20.247.20">8.20.247.20</a></td></tr>
<tr><td>OpenDNS Home</td><td><a href="?addr=208.67.222.222">208.67.222.222</a></td><td><a href="?addr=208.67.220.220">208.67.220.220</a></td></tr>
<tr><td>DNS Advantage</td><td><a href="?addr=156.154.70.1">156.154.70.1</a></td><td><a href="?addr=156.154.71.1">156.154.71.1</a></td></tr>
<tr><td>Norton ConnectSafe</td><td><a href="?addr=198.153.192.40">198.153.192.40</a></td><td><a href="?addr=198.153.194.40">198.153.194.40</a></td></tr>
<tr><td>ScrubIT</td><td><a href="?addr=67.138.54.120">67.138.54.120</a></td><td><a href="?addr=207.225.209.77">207.225.209.77</a></td></tr>
<tr><td>OpenNIC</td><td><a href="?addr=74.207.247.4">74.207.247.4</a></td><td><a href="?addr=64.0.55.201">64.0.55.201</a></td></tr>
<tr><td>Public-Root</td><td><a href="?addr=199.5.157.131">199.5.157.131</a></td><td><a href="?addr=208.71.35.137">208.71.35.137</a></td></tr>
<tr><td>SmartViper</td><td><a href="?addr=208.76.50.50">208.76.50.50</a></td><td><a href="?addr=208.76.51.51">208.76.51.51</a></td></tr>
<tr><td>DNSResolvers.com</td><td><a href="?addr=205.210.42.205">205.210.42.205</a></td><td><a href="?addr=64.68.200.200">64.68.200.200</a></td></tr>
<tr><td>Verisign</td><td><a href="?addr=198.41.2.2">198.41.2.2</a></td><td><a href="?addr=198.41.1.1">198.41.1.1</a></td></tr>
</table>
<?php
	exit($success ? 0 : -1);

