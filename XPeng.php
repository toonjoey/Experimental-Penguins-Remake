<?php
/*---------------------------------------------------------------------*/
// ABOUT:
//	Title: Experimental Penguins Encrypted Server//	Author: SandCP <sandcp8@gmail.com>//	Version: 3.7//	Brief Description: A long polling server written in PHP for a Flash Character Chat.
//	Full Description: The Experimental Penguins Encrypted
//	was a project which I began after developing a server for the original
//	Experimental Penguins Server Remake which used the original archived
//	client. The Experimental Penguins Encrypted Server was originally
//	going to be a lighter version of the first remake but ended up
//	becoming a new game itself. The client and the server are both
//	unique in code. The graphics were bits and pieces taken from various
//	projects done by the original creator RocketSnail <rocketsnail.com>.
//	The client and the server were/will be distributed as an open-source project
//	on the 31st of July 2015 AEST on Aureus.pw and later on Github.
//	-SandCP
/*---------------------------------------------------------------------*/
// LICENCE://	Copyright 2016 SandCP <sandcp8@gmail.com> Licensed under the//	Educational Community License, Version 2.0 (the "License"); you may//	not use this file except in compliance with the License. You may//	obtain a copy of the License at////	https://opensource.org/licenses/ECL-2.0////	Unless required by applicable law or agreed to in writing,//	software distributed under the License is distributed on an "AS IS"//	BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express//	or implied. See the License for the specific language governing//	permissions and limitations under the License.
/*---------------------------------------------------------------------*/// USAGE://	To use this server you must renew the global//	key/salt which is used for the games encryption//	if you go to http://127.0.0.1/XPeng.php?rkey (or wherever you stored this)//	you will receive a renewed global key/salt.//	You are to place the global key in the designated space below: $k=	"----------->PUT_GLOBAL_KEY_HERE<-----------";
	"WARNING: THE KEY IN THE CLIENT AND THE SERVER MUST BE IDENTICAL!";//	the renewal of this key is very important for security reasons.//	This key should be copied into the client too.
//	To run the intended version of Experimental Penguins
//	Encrypted Server & Client nothing other than the global key
//	should be edited. - SandCP
/*---------------------------------------------------------------------*/

$GLOBALS["key"] =  get_string_between($k,">","<");
$GLOBALS["systemRooms"] = ["users","banned"];
if(isset($_SERVER["HTTP_X_CLIENT_IP"])){
	$GLOBALS["clientIp"] = $_SERVER["HTTP_X_CLIENT_IP"];
} else {
	$GLOBALS["clientIp"] = $_SERVER["REMOTE_ADDR"];
}
date_default_timezone_set("UTC");
function generateRandomKey() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 36; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return wordwrap($randomString,4,'-',true);
}
function sha256($string,$ckey=false){
	if($ckey===false){
		$ckey = $GLOBALS["key"];
	}
	return hash_hmac("sha256",$string,$ckey);
}
function fakeChar(){	$ch = [61];	$ch = array_merge($ch,range(65,90));	$ch = array_merge($ch,range(97,122));	shuffle($ch);	$fakeord = intval($ch[rand(0,count($ch)-1)]);	return $fakeord+20;}function charEnc($char){	$asc = ord($char);	$pos = rand(1,255);	if($pos%2){		$rgb = [$pos,$asc+20,fakeChar()];	} else {		$rgb = [$pos,fakeChar(),$asc+20];	}	return $rgb;}function encodeMessage($message){
	header("Content-type: application/undefined");	$im = imagecreatetruecolor(32, 32);	$background_color = imagecolorallocate($im, 0,0,0);	$colors = [];	$l = 0;	for($y=0;$y<32;$y++){		for($x=0;$x<32;$x++){			if(isset($message[$l])){				$enc = charEnc($message[$l]);				$pali = count($colors);				$colors[$pali] = imagecolorallocate($im, $enc[0], $enc[1], $enc[2]);				imagesetpixel($im,$x,$y,$colors[$pali]);				$l++;			} else {				shuffle($colors);				if($x+$y !== 62){					imagesetpixel($im,$x,$y,$colors[0]);				}			}		}	}	$wlen = imagecolorallocate($im, $enc[0], $enc[1], strlen($message));	imagesetpixel($im,31,31,$wlen);	imagepng($im);	imagedestroy($im);
	die();}
function killGame($place=""){
	header("HTTP/1.1 202 Accepted");
	encodeMessage("disconnected:".$place);
}
function checkHeaders(){
	$recvhead = array_map('strtolower', (array)getallheaders());
	$recvhead = array_change_key_case($recvhead, CASE_LOWER);
	$chekhead = ["user-agent","accept","accept-language","accept-encoding"];
	foreach($chekhead as $head){
		if(!isset($recvhead[$head])){
			killGame(1);
		}
	}
	return true;
}
function checkSeedString($seed,$string){
	$char = ["abcdefghijklm1234567890".$seed.$seed,
		"nopqrstuvwxyz1385029".$seed.$seed,
		"acegikmoqsuwy1234567890",
		"bdfhjlnprtvxz0864213",
		"qetuoadgjlxvn1234567",
		"wryipsfhkzcbm0987654",
		"1234567890qetuoadgjlxvn".$seed.$seed.$seed,
		"1234567890qetuoadgjlxvn".$seed.$seed.$seed];
	$rand = str_split($string,4);
	$string_safe = true;
	foreach($rand as $slice){
		$characters = str_split($slice);
		$index = array_search($slice,$rand);
		foreach($characters as $character){
			if(strpos($char[$index],$character) === false){
				$string_safe = false;
			}
		}
	}
	return $string_safe;
}
function encrypt($name,$pass){
	$return_crypt = sha256($name.$pass.$GLOBALS["key"]."=accepted");
	return $return_crypt;
}
function decodeStamp($stamp){
	$piece = str_split($stamp);
	$stamp = array();
	foreach($piece as $p){
		if(is_numeric($p)){
			array_push($stamp,$p);
		} else {
			array_push($stamp,ord($p)-97);
		}
	}
	return intval(strrev(implode("",$stamp)));
}
function returnCrypt($name,$pass){
	if(strlen($pass) > 64+32){
		$cstamp = substr($pass,96);
		$stamp = decodeStamp($cstamp);
		$hashes = str_split($pass,32);
		$passcode = str_split($pass,64)[0];
		$seedle = $hashes[2];
		if(checkSeedString($passcode,$seedle)){
			return [encrypt($name,$pass),$stamp];
		} else {
			killGame(2);
		}
	} else {
		killGame(3);
	}
}
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
function searchBase($value,$type,$count=false){
	$base = getBase();
	$online = 0;
	foreach($base as $room){
		foreach($room as $user){
			$rid = array_search($room,$base);
			if(count($user) > 2 && !in_array($rid,$GLOBALS["systemRooms"])){
				$uvals = ['name' => array_search($user,$room),
				'time' => $user[1],
				'pass' => $user[2],
				'ip' => $user[3]];
				if(isset($uvals[$type])){
					if($uvals[$type] === $value){
						return $uvals;
					}
				}
			}
			$online++;
		}
	}
	if($count === "count"){
		return $online;
	}
	return false;
}
function getBase(){
	$strings = [base64_decode("PGV4cD4="), base64_decode("PC9leHA+")];
	$this_file = file_get_contents(__FILE__);
	$database = get_string_between($this_file,$strings[0],$strings[1]);
	return json_decode($database,true);
}
function saveBase($newBase){
	$this_file = file_get_contents(__FILE__);
	$oldBase = json_encode(getBase());
	$newBase = json_encode($newBase);
	$new_file = str_replace($oldBase,$newBase,$this_file);
	if($new_file !== ""){
		file_put_contents(__FILE__,$new_file);
	}
}
function verifyData($data){
	$blocks = explode(":",$data);
	$blocks[3] = cleanString($blocks[3]);
	$data = implode(":",$blocks);
	return $data;
}
function cleanString($str){
	return preg_replace("/[^a-zA-Z0-9!.?\ ]+/", "", $str);
}
function updateRoom($roomId,$name,$data,$pass,$join){
	$base = getBase();
	$roomString = "";
	if(in_array($roomId,$GLOBALS["systemRooms"])){
		killGame(4);
	}
	foreach($base as $room){
		$baseRoomId = array_search($room,$base);
		if(intval($baseRoomId) === intval($roomId)){
			foreach($room as $user){
				$username = array_search($user,$room);
				if($username !== $name){
					$roomString .= "&".$username."=".$user[0];
				}
			}
		}
		if(isset($room[$name]) && isset($room[$name][2])){
			if($join){
				encodeMessage("denied");
			} else {
				if($room[$name][2] !== $pass){
					killGame(5);
				}
				if($baseRoomId !== $roomId){
					unset($base[$baseRoomId][$name]);
				}
			}
		}
	}
	if(in_array($GLOBALS["clientIp"],$base["banned"])){
		killGame(6);
	}
	if(isset($base["users"][$name])){
		if(substr($pass,0,64) !== $base["users"][$name][0]){
			encodeMessage("denied");
		}
		$type = $base["users"][$name][1];
		$roomString .= "&type=$type";
	}
	$base[$roomId][$name] = [verifyData($data),time(),$pass,$GLOBALS["clientIp"]];
	saveBase($base);
	return $roomString;
}
function erasePlayer($name,$pass,$timer,$force=false){
	$base = getBase();
	foreach($base as $room){
		$roomId = array_search($room,$base);
		if(!in_array($roomId,$GLOBALS["systemRooms"])){
			foreach($room as $player){
				$pname = array_search($player,$room);
				if($timer !== true){
					if($pname == $name && $player[2] === $pass){
						unset($base[$roomId][$pname]);
					}
				} else {
					if(strtotime('-30 seconds') >= $player[1]){
						unset($base[$roomId][$pname]);
					}
				}
				if($force && $pname === $name){
					unset($base[$roomId][$pname]);
				}
			}
		}
		if(count($room) === 0 && !in_array($roomId,$GLOBALS["systemRooms"])){
			unset($base[$roomId]);
		}
	}
	saveBase($base);
}
function verifyMail($mail){
	$expmail = explode("@",$mail);
	if(count($expmail) > 1){
		if(checkdnsrr($expmail[1])){
			return true;
		}
	}
	return false;
}
function getAccountType($name){
	$base = getBase();
	if(isset($base["users"][$name][1])){
		return $base["users"][$name][1];
	}
	return false;
}
function verifyUser($name,$pass){
	$base = getBase();
	foreach($base as $room){
		$rid = array_search($room,$base);
		if(isset($base[$rid][$name][0]) &&  !in_array($rid,$GLOBALS["systemRooms"])){
			if($base[$rid][$name][2] === $pass){
				return true;
			}
		}
	}
	return false;
}
if(isset($_GET["rkey"])){
	if($GLOBALS["key"] === "PUT_GLOBAL_KEY_HERE"){
		$nkey = generateRandomKey();
		$base = getBase();
		$base["users"]["Admin"] = [sha256("password"."Admin".$nkey,$nkey),2];
		saveBase($base);
		die($nkey);
	} else {
		die("Key already generated and saved!");
	}
}
if(isset($_GET["logout"])){
	$base = getBase();
	$user = searchBase($GLOBALS["clientIp"],"ip");
	if($user){
		erasePlayer($user["name"],$pass,false,true);
		encodeMessage("success");
	}
}
if(isset($_GET['name']) && isset($_GET['password']) && isset($_GET['action'])){
	erasePlayer(false,false,true);
	if(isset($_GET["cchk"])){
		$user = searchBase($GLOBALS["clientIp"],"ip");
		if($user){
			encodeMessage("status:".$user["name"].":".$user["pass"]);
		} else {
			encodeMessage("status:logged_out");
		}
	}
	if(isset($_GET[str_replace(" ","_",$_GET['name'])])){
		if($_GET["name"] === "type"){
			encodeMessage("denied");
		}
		$name = ucfirst(strtolower($_GET['name']));
		$pass = $_GET['password'];
		$data = $_GET[str_replace(" ","_",$_GET['name'])];
		$name = cleanString($name);
		$room = explode(":",$data)[2];
		$crypt = returnCrypt($name,$pass);
		$action = $_GET['action'];
		if(!(searchBase(0,0,"count") < 30) && searchBase($name,'name') && !(strlen($name) < 14)){
			encodeMessage("denied");
		}
		if($action === "newplayer" && $crypt[1] > strtotime("-0 seconds")){
			if(searchBase($GLOBALS["clientIp"],'ip') && searchBase($name,'name')){
				erasePlayer($name,$pass,false,true);
			}
			if(searchBase($GLOBALS["clientIp"],'ip') && !searchBase($name,'name')){
				encodeMessage("denied");
			}
			$returnString = updateRoom($room,$name,$data,$pass,true);
		} else if($action === "update" && verifyUser($name,$pass)){
			$returnString = updateRoom($room,$name,$data,$pass,false);
		} else if($action === "drop" && verifyUser($name,$pass)) {
			$returnString = erasePlayer($name,$pass,false);	
		} else if(getAccountType($name) > 1){
			if($action === "kick" && isset($_GET["kick"])){
				$kicked = ucfirst($_GET["kick"]);
				$message = $kicked." is offline!";
				if(searchBase($kicked,"name")){
					erasePlayer($kicked,$pass,false,true);
					$message = $kicked." has been kicked offline!";
				}
			} else if($action === "make" && isset($_GET["make"])){
				$make = $_GET["make"];
				$mpic = explode(":",$make);
				$message = "bad parameters!";
				if($mpic > 2){
					$base = getBase();
					$mpic[0] = ucfirst($mpic[0]);
					$base["users"][$mpic[0]] = [sha256($mpic[1].$mpic[0].$GLOBALS["key"]),$mpic[2]];
					saveBase($base);
					$message = $mpic[0]."'s account has been updated!";
				}
			} else if($action === "dele" && isset($_GET["dele"])){
				$dele = ucfirst($_GET["dele"]);
				$base = getBase();
				$message = $dele." does not have an account!";
				if(isset($base["users"][$dele])){
					unset($base["users"][$dele]);
					$message = $dele." has been deleted!";
				}
				saveBase($base);
			} else if($action === "bann" && isset($_GET["bann"])){
				$bann = ucfirst($_GET["bann"]);
				$message = $bann." is offline!";
				if(searchBase($bann,"name")){
					$banip = searchBase($bann,"name")["ip"];
					$message = "Successfully banned ".$bann."'s ip: ".$banip;
					$base = getBase();
					erasePlayer($bann,$pass,false,true);
					array_push($base["banned"],$banip);
					saveBase($base);
				}
			}
			$returnString = updateRoom($room,$name,$data,$pass,false);
			if(isset($message)){
				$returnString .= "&message=".$message;
			}
		} else {
			killGame(7);
		}
		checkHeaders();
		header("HTTP/1.1 201 Created");
		//header("Content-Encoding: gzip");
		$reply = $crypt[0].$returnString;
		//$reply = gzencode($reply,9);
		encodeMessage($reply);
	}
}
die("Experimental Penguins Server<p><a href=\"?rkey\">Generate Key</a>");
?>
<exp>{"users":{"Admin":["",2]},"banned":[]}</exp>