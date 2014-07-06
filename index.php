<?php

require('routeros_api.class.php');

$octet_2 = -1;
$octet_3 = -1;



function sortByOption($a, $b) {
  // Not so nice code. explodes the ip number then multiply.
  $aa = explode (".",$a["address"]);
  $bb = explode (".",$b["address"]);
  $aaa = $aa[0]*255*255*255 + $aa[1]*255*255 + $aa[2]*255 + $aa[3];
  $bbb = $bb[0]*255*255*255 + $bb[1]*255*255 + $bb[2]*255 + $bb[3];
  return $aaa - $bbb;
 }

function subnet_header($ip) {
	global $octet_2, $octet_3;
	$octets = explode (".",$ip);
	// Octet 2 header 127.xxx.0.0
	if ($octets[1] > $octet_2 ) {
		echo "</table>";
		echo "<h1> 172.".$octets[1].".".$octets[2].".0/24 </h1>";
		echo "<table style=width:300px>";
		echo " <tr><th></th> <th>IP</th> <th>Address</th> </tr>\n";
		$octet_2 = $octets[1];
		$octet_3 = $octets[2];

	}

	if (($octets[2] > $octet_3 ) and ($octets[1] == $octet_2)) {
		echo "</table>";
		echo "<h2> 172.".$octets[1].".".$octets[2].".0/24 </h2>";
		echo "<table style=width:300px>";
		echo " <tr><th></th> <th>IP</th> <th>Address</th> </tr>\n";
		$octet_2 = $octets[1];
		$octet_3 = $octets[2];
	}
}


if(isset($_GET["delete"])) {
	/*
	delete a record based on ID
	*/
	$API = new routeros_api();
	$API->debug = false;
	if ($API->connect('172.26.0.1', 'admin', 'PASSWORD')) {
		
		// Get the ID of what we are trying to delete
		$API->write('/ip/dns/static/print',false);
		$API->write('?name='.$_GET["delete"],false);
		$API->write('=.proplist=.id');
   		$READ = $API->read(false);
   		$ARRAY = $API->parse_response($READ);

		$API->write('/ip/dns/static/remove',false);
		$API->write('=.id='.$ARRAY[0][".id"],true);
   		$READ = $API->read();
   		$ARRAY = $API->parse_response($READ);
   		
		$API->disconnect();
		echo "Record number: ".$ARRAY[0][".id"]." deleted\n";
	//	print_r ($ARRAY);
	}

} 


// Main page
{

$API = new routeros_api();
$API->debug = false;
if ($API->connect('172.26.0.1', 'admin', 'xxxxxxx')) {
	$API->write('/ip/dns/static/getall');
   	$READ = $API->read(false);
   	$ARRAY = $API->parse_response($READ);
   	$API->disconnect();
	
	// Sort the array by IP range and number
	usort($ARRAY, 'sortByOption');


   	for ($i = 0; $i < sizeof($ARRAY) ; $i++) {
		subnet_header($ARRAY[$i]['address']);
		echo "<TR>";
		echo "<TD><a href=/index.php?delete=".$ARRAY[$i]['name']."><img src=/images/delete.png width=16 height=16></a></TD>\n";
		echo "<TD>".$ARRAY[$i]['name'] . "</TD>";
		echo "<TD>".$ARRAY[$i]['address'] . "</TD>\n";
		echo "</TR>\n";
   	}
   	
	echo "</TABLE>";
//print_r ($ARRAY);
}
}

?>


