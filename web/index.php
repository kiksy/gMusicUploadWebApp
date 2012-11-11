<?php
session_start();
include('../lib/GMApi/GMApi.php');
require('../lib/getid3/getid3.php');


//Simple PHP form upload for songs for the unnoficial Google Music API

include_once("account.php");

/*$email = (defined("GM_EMAIL") ? GM_EMAIL : "xxx");
$password = (defined("GM_PASSWORD") ? GM_PASSWORD : "xxx");
$mac_address = (defined("GM_MACADDRESS") ? GM_MACADDRESS : "xxx"); // xx-xx-xx-xx-xx
*/
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
	
	$getID3 = new getID3;

	// Analyze file and store returned data in $ThisFileInfo
	$ThisFileInfo = $getID3->analyze($path);

	/*
	 Optional: copies data from all subarrays of [tags] into [comments] so
	 metadata is all available in one location for all tag formats
	 metainformation is always available under [tags] even if this is not called
	*/
	getid3_lib::CopyTagsToComments($ThisFileInfo);

	/*
	 Output desired information in whatever format you want
	 Note: all entries in [comments] or [tags] are arrays of strings
	 See structure.txt for information on what information is available where
	 or check out the output of /demos/demo.browse.php for a particular file
	 to see the full detail of what information is returned where in the array
	 Note: all array keys may not always exist, you may want to check with isset()
	 or empty() before deciding what to output
	*/

	//echo $ThisFileInfo['comments_html']['artist'][0]; // artist from any/all available tag formats
	//echo $ThisFileInfo['tags']['id3v2']['title'][0];  // title from ID3v2
	//echo $ThisFileInfo['audio']['bitrate'];           // audio bitrate
	//echo $ThisFileInfo['playtime_string'];            // playtime in minutes:seconds, formatted string

	//print_r($ThisFileInfo);die();

		$trackLength = round($ThisFileInfo['playtime_seconds']) * 1000;
		$trackLength = intval($trackLength);
	
		$data = array(
			"filepath"=>$path,
			"track"=>array(
				//"creation"=>intval(date("U")),
				//"lastPlayed"=>intval(date("U")),
				"title"=>$ThisFileInfo['comments_html']['title'][0],
				"artist"=>$ThisFileInfo['comments_html']['artist'][0],
				"composer"=>$ThisFileInfo['comments_html']['artist'][0],
				"album"=>$ThisFileInfo['comments_html']['album'][0],
				"albumArtist"=>$ThisFileInfo['comments_html']['artist'][0],
				"year"=>2012,
				"comment"=>"my comment",
				"track"=>1,
				"genre"=>"my genre",
				"duration"=>$trackLength,
				"beatsPerMinute"=>1,
				"playCount"=>0,
				"totalTracks"=>1,
				"disc"=>1,
				"totalDiscs"=>1,
				"rating"=>0,
				//"fileSize" let api do it
				//"u13"=>0,
				//"u14"=>0,
				"bitrate"=>$ThisFileInfo['audio']['bitrate'],
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


?>

<form enctype="multipart/form-data" action="" method="POST">
Choose a file to upload: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Upload File" />
</form>

HERE ARE YOUR SONGS IN PLAYLIST 1
--------------------------------------------

<?
//Get All Playlists:
$Allplaylists = $api->get_playlists();


$playlist = $api->get_playlists('ecbf42b7-b2bb-4f18-9a3b-7af2c16e316f');
print_r($playlist);


/* Handle the upload */

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
