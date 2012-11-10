<?php
session_start();
include('../lib/GMApi/GMApi.php');
include('../lib/MP3_Id-1.2.2/Id.php');


//Simple PHP form upload for songs for the unnoficial Google Music API

@include_once("account.php");
$email = (defined("GM_EMAIL") ? GM_EMAIL : "xxx");
$password = (defined("GM_PASSWORD") ? GM_PASSWORD : "xxx");
$mac_address = (defined("GM_MACADDRESS") ? GM_MACADDRESS : "xxx"); // xx-xx-xx-xx-xx

$api = new GMApi();
$api->setDebug(true);
$api->enableRestore(true);
$api->enableMACAddressCheck(true);
$api->enableSessionFile(true);

function PrintOutput($message) 
{
		echo $message."<br/><br/>";
		GMAPIUtils::flushOutput();
}

function demo_detail_upload($paths) {
	global $api;
	$infos = array();
	foreach($paths as $path) {
	
	$mp3 = new MP3_Id();
	$mp3->read($path);
	
	
		$data = array(
			"filepath"=>$path,
			"track"=>array(
				//"creation"=>intval(date("U")),
				//"lastPlayed"=>intval(date("U")),
				"title"=>$mp3->getTag('name'),
				"artist"=>$mp3->getTag('artists'),
				"composer"=>$mp3->getTag('artists'),
				"album"=>$mp3->getTag('album'),
				"albumArtist"=>$mp3->getTag('artists'),
				"year"=>2012,
				"comment"=>"my comment",
				"track"=>1,
				"genre"=>"my genre",
				"duration"=>60000, // 60 seconds, use getID3 library
				"beatsPerMinute"=>1,
				"playCount"=>0,
				"totalTracks"=>1,
				"disc"=>1,
				"totalDiscs"=>1,
				"rating"=>0,
				//"fileSize" let api do it
				//"u13"=>0,
				//"u14"=>0,
				"bitrate"=>192,
				//"u15"=>"",
				//"u16"=>0
			)
		);
		$infos[] = $data;
	}
	
	$res = $api->upload($infos);
	return $res;
}

if(!$api->login($email, $password, $mac_address)) {
	die("login fail");
}

PrintOutput("login success. This login is using ".$api->getLoginResultType());

/*
$mp3_1 = new MP3_Id();
$mp3_1->read('uploads/EmotionalBrilliance.mp3');
print_r($mp3_1->getTag('name'));
die('deadedHERE');
*/
?>

<form enctype="multipart/form-data" action="" method="POST">
Choose a file to upload: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload File" />
</form>

<?
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{		
	$target_path = "uploads/";

	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 

	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) 
	{
    	echo "The file ".  basename( $_FILES['uploadedfile']['name']). 
    	" has been uploaded";
    	
    	//$res = $api->upload("uploads/".$_FILES['uploadedfile']['name']);
		//$res = $api->upload(array("h.mp3","x.mp3"));
		$res = demo_detail_upload(array("uploads/".$_FILES['uploadedfile']['name']));
		
		if($res == false) {
			die("unable upload");
		}
		
		foreach($res as $key=>$value) {
			$songid = $value;
			PrintOutput("getting ".$songid);
			$stream_url = $api->get_stream_url($songid);
			if($stream_url == false) {
				PrintOutput("unable get stream url");
			} else {
				print_r("stream url: ".$stream_url);
			}
			
			$download_info = $api->get_download_info($songid);
			if($download_info == false) {
				PrintOutput("unable get download url");
			} else {
				PrintOutput("download url: ".$download_info['url']);
			}
		}
		
	
		
	
		
		
		die("done");
    	
    	
	}
	else
	{
    	echo "There was an error uploading the file, please try again!";
	}
   
}

?>