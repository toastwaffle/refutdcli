<?php
error_reporting(0);
$debug = false;		// Debug mode
$charlimit = 160;	// Character limit on messages
include('./config.php');

function soap($func, $params) {
	$soapClient = new SoapClient($apiurl);
	$params["apiKey"] = $apikey;
	try {
		$info = $soapClient->__call($func, array($params));
		$info = $info -> return;
		if ($info == "") {
			$info = "0";
		}
	} catch (SoapFault $fault) {
		$info = array();
	}
	return $info;
}

function qry($query) {
	$temp = array();
	$result = mysql_query($query, $cxn);
	if ($result !== false) {
		while ($row = mysql_fetch_assoc($result)) {
			$temp[] = $row;
		}
	}
	return $temp;
}

$stringin = "";
$guid = "";

if (isset($_GET["message"])) {
	$stringin = $_GET["message"];
	$debug = true;
}

if (isset($_GET["guid"])) {
	$guid = $_GET["guid"];
}

if (isset($_POST["message"])) {
	$stringin = $_POST["message"];
}

if (isset($_POST["userid"])) {
	$results = qry("select id, guid from registrations where id = '".$_POST["userid"]."'");
	if (count($results) == 1) {
		$guid = $results[0]["guid"];
	}
}

if ($debug) {
?><html>
	<head>
		<title>Ref Utd</title>
	</head>
	<body><pre>Hi
<?php
}

$lang = "en"; //TODO: Implement other languages.

$keywords = array (
	"en" => array (
		"search" => array (
			"find",
			"look"
		),
		"update" => array (
			"set"
		),
		"messages" => array (
			"inbox",
			"unread"
		),
		"sent" => array (
		),
		"message" => array (
			"email",
			"e-mail",
			"chat",
			"call",
			"mail",
			"send"
		),
		"details" => array (
			"info",
			"whoami"
		),
		"more" => array (
		),
		"help" => array (
			"man"
		)
	)
);

$ignorebeforecommand = array (
	"a",
	"get",
	"my",
	"please"
);
$ignoreaftercommand = array (
	"a",
	"for",
	"I'm",
	"message",
	"my",
	"to"
);

$stringin = preg_replace("/_+/i", "_", $stringin);
$stringin = str_replace("_", " ", $stringin);
$stringin = trim($stringin);
$command = $stringin;
do {
	$command = (explode(" ", $stringin));
	$command = strtolower($command[0]);
	if (strpos($stringin, " ") !== false) {
		$stringin = substr($stringin, strpos($stringin, " ") + 1);
	}
} while (in_array($command, $ignorebeforecommand));
if (strtolower($stringin) == $command) {
	$query = "";
} else {
	$query = $stringin;
	$temp = explode(" ", $query);
	$i = 0;
	while (in_array($temp[$i], $ignoreaftercommand)) {
		$i ++;
		$query = substr($query, strpos($query, " ") + 1);
	}
}
if ($debug) {
	echo($command."-".$query."\n".$stringin."\n\n");
}

$founditem = "";
foreach ($keywords[$lang] as $key => $item) {
	if (($command == $key) || (in_array($command, $item))) {
		$founditem = $key;
	}
}

$output = array();
$mores = 0;
$j = 0;
$disp = 0;


if (($founditem == "more") | (preg_match("/^[0-9]+$/", $command))) {
	$results = qry("select lastaction, mores, lastactiondetails, guid from registrations where guid = 'ff80818136aa8b090136b06629c10007'");
	if (count($results) != 0) {
		$founditem = strtolower($results[0]["lastaction"]);
		$mores = $results[0]["mores"] + 1;
		$query = $results[0]["lastactiondetails"];
		qry("update `refuntdhack_phpfogapp_com`.`registrations` set mores = mores + 1 where guid = '$guid'");
	} else {
		$founditem = "error";
	}
	if (preg_match("/^[0-9]+$/", $command)) {
		$disp = $command + 0;
	}
}
switch ($founditem) {
	case "search":
		$results = soap("Search", array("queryString" => $query, "LCID" => 0, "limit" => ".(10 * ($mores + 1)).", "offset" => 0));
		$i = 0;
		$output[] = "";
		foreach($results as $x) {
			$i ++;
			if ($i == $disp) {
				$temp = $x -> firstName." ".$x -> lastName."\n";
				if ($x -> gender != "UNKNOWN") {
					$temp .= ucfirst(strtolower($x -> gender))."\n";
				}
				$temp .= $x -> cellphone."\n";
				$temp .= $x -> primaryEmail."\n";
				if ($x -> lastSighting != "") {
					$temp .= "Last saw-".$x -> lastSighting."\n";
				}
				$temp = preg_replace("/ +/i", " ", $temp);
				$output[] = preg_replace("/\n+/i", "\n", $temp);;
				$mores = count($output) - 1;
				break;
			}
			$temp = $i."-".ucwords(strtolower($x -> firstName))." ".ucwords(strtolower($x -> lastName));
			if (strlen($x -> nickName) <= 12) {
				$temp .=  " (".$x -> nickName.")";
			}
			$temp = preg_replace("/ +/i", " ", $temp);
			$temp = preg_replace("/\\(\\)/i", "", $temp);
			$temp = trim($temp);
			if ($output[$j] != "") {
				$temp = "\n".$temp;
			}
			if (strlen($output[$j].$temp) <= $charlimit) {
				$output[$j] .= $temp;
			} else {
				$output[] = $temp;
				$j ++;
			}
		}
		if ($command != "more") {
			qry("update `refuntdhack_phpfogapp_com`.`registrations` set mores = 0, lastaction = '$founditem', lastactiondetails = '$query' where guid = '$guid'");
		}
	break;
	case "update":
		$success = True;
		$operand = (explode(" ", $query, 2));
		$profile = soap("GetProfile", array("profileGuid" => $guid));
		switch (strtolower($operand[0])) {
			case "phone":
				if (!preg_match('/[0-9]+/',trim($operand[1]))) {
					$output[] = 'Sorry, phone numbers must only contain digits';
					$success = False;
				} else {
					$profile->cellphone = trim($operand[1]);
				}
			break;
			case "email":
				if (!filter_var(trim($operand[1]), FILTER_VALIDATE_EMAIL)) {
					$output[] = 'Sorry, that is not a valid email.';
					$success = False;
				} else {
					$profile->primaryEmail = trim($operand[1]);
				}
			break;
			case "lastname":
				$profile->lastName = trim($operand[1]);
			break;
			case "physicaltraits":
				$profile->physicalTraits = trim($operand[1]);
			break;
			case "favoriteplace":
				$profile->favoritePlace = trim($operand[1]);
			break;
			case "favoritefood":
				$profile->favoriteFood = trim($operand[1]);
			break;
			case "favoriteactivity":
				$profile->favoriteActivity = trim($operand[1]);
			break;
			case "hometown":
				$profile->homeTown = trim($operand[1]);
			break;
			case "dob":
				$date = new DateTime(trim($operand[1]));
				if (!$date) {
					$output[] = 'Sorry, that is not a valid date.';
					$success = False;
				} else {
					$profile->dateOfBirth = $date->format('Y-m-d H:i:s.u');
				}
			break;
			case "occupation":
				$profile->occupation = trim($operand[1]);
			break;
			case "parentsnationality":
				$profile->parentsNationality = trim($operand[1]);
			break;
			case "familysize":
				$profile->familySize = int(trim($operand[1]));
			break;
			case "firstname":
				$profile->firstName = trim($operand[1]);
			break;
			case "gender":
				switch (strtolower(trim($operand[1]))) {
					case "m":
					case "male":
					case "boy":
					case "gentleman":
						$gender = "MALE";
					break;
					case "f":
					case "female":
					case "girl":
					case "lady":
						$gender = "FEMALE";
					break;
					default:
						$gender = "UNKNOWN";
					break;
				}
				$profile->gender = $gender;
			break;
			case "lastsighting":
				$profile->lastSighting = trim($operand[1]);
			break;
			case "nickname":
				$profile->nickName = trim($operand[1]);
			break;
			case "otherinformation":
				$profile->otherInformation = trim($operand[1]);
			break;
			case "tribe":
				$profile->tribe = trim($operand[1]);
			break;
			default:
				$output[] = "Sorry, I didn't understand that. Reply \"HELP UPDATE\" to show editable fields.";
				$success = False;
			break;
		}
		if ($success) {
			soap("UpdateProfile", array("profile" => $profile));
			$output[] = 'Your profile has been updated.';
		}
	break;
	case "messages":
		if ($command == "unread") {
			$results = soap("GetUnreadMessages", array("profileGuid" => $guid, "mailBox" => "INBOX", "limit" => 15, "offset" => 0));
		} else {
			$results = soap("GetMessages", array("profileGuid" => $guid, "mailBox" => "INBOX", "limit" => 15, "offset" => 0));
		}
		$i = 0;
		foreach ($results as $x) {
			$i ++;
			if ($i == $disp) {
				$temp = $x -> subject."\n".$x -> body."\n";
				$temp = preg_replace("/ +/i", " ", $temp);
				$output[] = preg_replace("/\n+/i", "\n", $temp);;
				$mores = count($output) - 1;
				break;
			}
			$temp = $i."-".$x -> subject."(".$x -> recipientName.")";
			if ($output[$j] != "") {
				$temp = "\n".$temp;
			}
			if (strlen($output[$j].$temp) <= $charlimit) {
				$output[$j] .= $temp;
			} else {
				$output[] = $temp;
				$j ++;
			}
		}
		qry("update `refuntdhack_phpfogapp_com`.`registrations` set mores = 0, lastaction = '$founditem', lastactiondetails = '$query' where guid = '$guid'");
	break;
	case "sent":
		$results = soap("GetMessages", array("profileGuid" => $guid, "mailBox" => "SENT", "limit" => 15, "offset" => 0));
		$i = 0;
		foreach ($results as $x) {
			$i ++;
			$temp = $i."-".$x -> subject."(".$x -> recipientName.")";
			if ($output[$j] != "") {
				$temp = "\n".$temp;
			}
			if (strlen($output[$j].$temp) <= $charlimit) {
				$output[$j] .= $temp;
			} else {
				$output[] = $temp;
				$j ++;
			}
		}
		if ($command != "more") {
			qry("update `refuntdhack_phpfogapp_com`.`registrations` set mores = 0, lastaction = '$founditem', lastactiondetails = '$query' where guid = '$guid'");
		}
	break;
	case "message":
		$operand = (explode(" ", $query));
		$operand = strtolower($operand[0]);
		if (strpos($query, " ") !== false) {
			$query = substr($query, strpos($query, " ") + 1);
		}
		// Todo: somehow get recipients guid - recipients name in $operand
		$recipguid = $guid;
		$results = soap("SendMessage", array("recipientGuid" => $recipguid, "senderGuid" => $guid, "subject" => "Test", "body" => $query));
		$output[0] = "Message sent successfully";
	break;
	case "details":
		$results = soap("GetProfile", array("profileGuid" => $guid));
		$temp = array();
		$temp[] = trim($results -> userName);
		$temp[] = "Name-".trim($results -> firstName)." ".trim($results -> lastName)." (".trim($results -> nickName).")";
		$temp[] = "Tribe-".trim($results -> tribe);
		$temp[] = trim($results -> otherInformation);
		$temp[] = "DOB-".date_format(new datetime($results -> dateOfBirth), "Y-m-d");
		$temp[] = "From-".trim($results -> homeTown);
		$temp[] = "Phone No.-+".trim($results -> cellphone);
		$temp[] = "Email-".trim($results -> primaryEmail);
		// keep going...
		foreach ($temp as $x) {
			$x = trim($x);
			if (strlen($output[$j]."\n".$x) <= $charlimit) {
				if ($output[$j] != "") {
					$output[$j] .= "\n".$x;
				} else {
					$output[$j] .= $x;
				}
			} else {
				$output[] = $x;
				$j ++;
			}
		}
		if ($command != "more") {
			qry("update `refuntdhack_phpfogapp_com`.`registrations` set mores = 0, lastaction = '$founditem', lastactiondetails = '$query' where guid = '$guid'");
		}
	break;
	case "error":
		$output[] = "Sorry, I can't find you";
	break;
	case "help":
		switch (trim($query)) {
			case "update":
				$output[] = "Usage: UPDATE <field> <new value>. Available fields are: phone, email, lastname, physicaltraits, favoriteplace, favoritefood, favoriteactivity, hometown, dob, occupation, parentsnationality, familysize, firstname, gender, lastsighting, nickname, otherinformation, tribe.";
			break;
			default:
				$output[] = "Available commands are: search, update, messages, sent, send, details.";
			break;
		}

	break;
	default:
		$output[] = "I'm sorry, I didn't understand that. Type \"HELP\" for info.";
	break;
}
if (count($output) > $mores) {
	echo(trim($output[$mores]));
} else {
	echo("Sorry, I can't find anything");
}
if ($debug) {
var_dump($_POST);
	echo("\n\n");
	print_r($output);
	echo "\n\nAll OK";
?>
	</pre></body>
</html><?php
}
?>
