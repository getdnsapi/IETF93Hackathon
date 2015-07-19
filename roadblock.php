<?php

header('Content-Type: text/html');

#ob_end_flush();

print("<html><body>
<form><input type=\"text\" name=\"addr\"><input type=\"submit\"></form>
");
if (!$_REQUEST["addr"]) {
print "
<br>
Also test: <ul>
<li><a href=\"?addr=216.146.35.35\">Dyn Internet Guide</a></li>
<li><a href=\"?addr=209.244.0.3\">Level 3</a></li>
<li><a href=\"?addr=8.8.8.8\">Google</a></li>
<li><a href=\"?addr=208.67.222.222\">OpenDNS</a></li>
<li><a href=\"?addr=198.41.2.2\">Verisign</a></li>
</ul>
";
	print("</body></html>");
	exit(0);
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

print "
<br>
Also test: <ul>
<li><a href=\"?addr=216.146.35.35\">Dyn Internet Guide</a></li>
<li><a href=\"?addr=209.244.0.3\">Level 3</a></li>
<li><a href=\"?addr=8.8.8.8\">Google</a></li>
<li><a href=\"?addr=208.67.222.222\">OpenDNS</a></li>
<li><a href=\"?addr=198.41.2.2\">Verisign</a></li>
</ul>
";
$success = True;

print("</body></html>");
error_destroy_response:
	php_getdns_dict_destroy($respDict);
error_destroy_upstreams:
	php_getdns_list_destroy($upstreams);
error_destroy_context:
	php_getdns_context_destroy($context);
error:
	exit($success ? 0 : -1);

