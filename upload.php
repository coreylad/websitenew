<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("TU_VERSION", "3.0.7 by xam");
define("THIS_SCRIPT", "upload.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
@ini_set("upload_max_filesize", 1000 < $max_torrent_size ? $max_torrent_size : 10485760);
@ini_set("memory_limit", "20000M");
$lang->load("upload");
$TSSEConfig->TSLoadConfig("ANNOUNCE");
require_once INC_PATH . "/editor.php";
$ModerateTorrent = $usergroups["canupload"] == "moderate" ? true : false;
$CanUploadExternalTorrent = $usergroups["canexternal"] == "yes" && $externalscrape == "yes" ? true : false;
$AnnounceURL = trim($xbt_active == "yes" ? $xbt_announce_url . "/" . $CURUSER["torrent_pass"] . "/announce" : $announce_urls[0] . "?$passkey = " . $CURUSER["torrent_pass"]);
$postoptions = "";
$postoptionstitle = "";
$UploadErrors = [];
if (isset($_GET["id"]) && is_valid_id($_GET["id"])) {
    if ($EditTorrentID = intval(TS_Global("id"))) {
        // Query for editing torrent
        $editTorrentQuery = sql_query("SELECT * FROM torrents WHERE $id = " . sqlesc($EditTorrentID));
        if (mysqli_num_rows($editTorrentQuery)) {
            $editTorrentData = mysqli_fetch_assoc($editTorrentQuery);
            if ($editTorrentData["owner"] != $CURUSER["id"] && !$is_mod) {
                print_no_permission(true);
            }
            $name = $editTorrentData["name"];
            $descr = $editTorrentData["descr"];
            $isExternalTorrent = $editTorrentData["ts_external"] == "yes" && $CanUploadExternalTorrent ? true : false;
            $category = intval($editTorrentData["category"]);
            $torrentImageUrl = $editTorrentData["t_image"];
            $torrentLink = $editTorrentData["t_link"];
            if ($torrentLink) {
                preg_match("@https://www.imdb.com/title/(.*)/@Us", $torrentLink, $imdbResult);
                if ($imdbResult && isset($imdbResult[1]) && $imdbResult[1]) {
                    $torrentLink = "https://www.imdb.com/title/" . $imdbResult[1];
                }
                unset($imdbResult);
            }
            $offensive = $editTorrentData["offensive"];
            $anonymous = $editTorrentData["anonymous"];
            $free = $editTorrentData["free"];
            $silver = $editTorrentData["silver"];
            $doubleUpload = $editTorrentData["doubleupload"];
            $allowComments = $editTorrentData["allowcomments"];
            $sticky = $editTorrentData["sticky"];
            $isRequest = $editTorrentData["isrequest"];
            $isNuked = $editTorrentData["isnuked"];
            $nukeReason = $editTorrentData["WhyNuked"];
            if ($usergroups["canuploadddl"] == "yes") {
                $directDownloadLink = $editTorrentData["directdownloadlink"];
            }
            if ($use_torrent_details == "yes") {
                $detailsQuery = sql_query("SELECT video_info,audio_info FROM ts_torrents_details WHERE $tid = " . sqlesc($EditTorrentID));
                if (mysqli_num_rows($detailsQuery)) {
                    $detailsResult = mysqli_fetch_assoc($detailsQuery);
                    $videoDetails = explode("~", $detailsResult["video_info"]);
                    $video["codec"] = isset($videoDetails[0]) ? $videoDetails[0] : "";
                    $video["bitrate"] = isset($videoDetails[1]) ? $videoDetails[1] : "";
                    $video["resulation"] = isset($videoDetails[2]) ? $videoDetails[2] : "";
                    $video["length"] = isset($videoDetails[3]) ? $videoDetails[3] : "";
                    $video["quality"] = isset($videoDetails[4]) ? $videoDetails[4] : "";
                    $audioDetails = explode("~", $detailsResult["audio_info"]);
                    $audio["codec"] = isset($audioDetails[0]) ? $audioDetails[0] : "";
                    $audio["bitrate"] = isset($audioDetails[1]) ? $audioDetails[1] : "";
                    $audio["frequency"] = isset($audioDetails[2]) ? $audioDetails[2] : "";
                    $audio["language"] = isset($audioDetails[3]) ? $audioDetails[3] : "";
                }
            }
        } else {
            print_no_permission();
        }
    } else {
        print_no_permission();
    }
}
if ($usergroups["canupload"] == "no") {
    print_no_permission(false, true, $lang->upload["uploaderform"]);
}
$userPermQuery = sql_query("SELECT userid FROM ts_u_perm WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $canupload = '0'");
if (mysqli_num_rows($userPermQuery)) {
    print_no_permission(false, true, $lang->upload["uploaderform"]);
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $name = TS_Global("subject");
    $descr = TS_Global("message");
    $torrentfile = $_FILES["torrentfile"];
    $isExternalTorrent = isset($_POST["IsExternalTorrent"]) && $_POST["IsExternalTorrent"] == "yes" && $CanUploadExternalTorrent ? true : false;
    $nfofile = $_FILES["nfofile"];
    $useNfoAsDescription = TS_Global("UseNFOasDescr");
    $category = intval(TS_Global("category"));
    $torrentImageUrl = TS_Global("t_image_url");
    $torrentImageFile = $_FILES["t_image_file"];
    $torrentLink = TS_Global("t_link");
    $isSceneValue = TS_Global("isScene");
    $offensive = TS_Global("offensive");
    $anonymous = TS_Global("anonymous");
    $free = TS_Global("free");
    $silver = TS_Global("silver");
    $doubleupload = TS_Global("doubleupload");
    $allowcomments = TS_Global("allowcomments");
    $sticky = TS_Global("sticky");
    $isrequest = TS_Global("isrequest");
    $isnuked = TS_Global("isnuked");
    $nukeReason = TS_Global("WhyNuked");
    if ($usergroups["canuploadddl"] == "yes") {
        $directDownloadLink = TS_Global("directdownloadlink");
    }
    if ($use_torrent_details == "yes") {
        $audio = $_POST["audio"];
        $video = $_POST["video"];
    }
    if (!empty($directDownloadLink) && $usergroups["canuploadddl"] == "yes" && (!filter_var($directDownloadLink, FILTER_VALIDATE_URL) || get_extension($directDownloadLink) != "zip")) {
        $UploadErrors[] = $lang->global["invalidddllink"];
    }
    if (isset($nfofile["name"]) && !empty($nfofile["name"])) {
        if (get_extension($nfofile["name"]) != "nfo") {
            $UploadErrors[] = $lang->upload["error10"];
        }
        if ($nfofile["size"] == "0") {
            $UploadErrors[] = $lang->upload["error11"];
        }
        if ($nfofile["error"] != "0") {
            $UploadErrors[] = $lang->upload["error6"] . " {" . intval($nfofile["error"]) . "}";
        }
        if (!is_file($nfofile["tmp_name"])) {
            $UploadErrors[] = $lang->upload["error6"] . " {" . intval($nfofile["error"]) . "}";
        }
        if (count($UploadErrors) == 0) {
            $nfoContents = file_get_contents($nfofile["tmp_name"]);
        }
    }
    if (empty($name) || strlen($name) < 5) {
        if (isset($torrentfile["name"]) && !empty($torrentfile["name"])) {
            $name = str_replace(".torrent", "", $torrentfile["name"]);
        } else {
            $UploadErrors[] = $lang->upload["error1"];
        }
    }
    if (empty($descr) || strlen($descr) < 10) {
        if (!($UseNFOasDescr == "yes" && isset($NfoContents)) || empty($NfoContents)) {
            $UploadErrors[] = $lang->upload["error2"];
        }
    }
    if (isset($torrentfile["name"]) && !empty($torrentfile["name"]) && !$IsExternalTorrent) {
        $UpdateHash = true;
    }
    if (isset($editTorrentData) && empty($torrentfile["name"])) {
        $torrentfile["name"] = $editTorrentData["filename"];
        $torrentfile["type"] = "application/x-bittorrent";
        $torrentfile["size"] = $editTorrentData["size"];
        $torrentfile["error"] = 0;
        $torrentfile["tmp_name"] = $torrent_dir . "/" . $EditTorrentID . ".torrent";
    }
    if (isset($torrentfile["name"]) && !empty($torrentfile["name"])) {
        if (get_extension($torrentfile["name"]) != "torrent") {
            $UploadErrors[] = $lang->upload["error3"];
        }
        if ($torrentfile["size"] == "0") {
            $UploadErrors[] = $lang->upload["error5"];
        }
        if ($torrentfile["error"] != "0") {
            $UploadErrors[] = $lang->upload["error6"] . " {" . intval($torrentfile["error"]) . "}";
        }
        if (!is_file($torrentfile["tmp_name"])) {
            $UploadErrors[] = $lang->upload["error6"] . " {" . intval($torrentfile["error"]) . "}";
        }
    } else {
        $UploadErrors[] = $lang->upload["error3"];
    }
    if (!$category) {
        $UploadErrors[] = $lang->upload["error9"];
    }
    if (count($UploadErrors) == 0) {
        require_once INC_PATH . "/class_torrent.php";
        if ($Data = file_get_contents($torrentfile["tmp_name"])) {
            $Torrent = new Torrent();
            if ($Torrent->load($Data)) {
                $info_hash = $Torrent->getHash();
                if ($privatetrackerpatch == "yes" && $IsExternalTorrent && $Torrent->getWhatever("announce") == $AnnounceURL && $Torrent->getWhatever("announce-list") == NULL) {
                    $IsExternalTorrent = false;
                }
                if ($privatetrackerpatch == "yes" && !$IsExternalTorrent) {
                    $Torrent->removeWhatever("announce-list");
                    $Torrent->removeWhatever("nodes");
                    if ($Torrent->getPrivate() != "1") {
                        $Torrent->setPrivate("1");
                    }
                    if ($Torrent->getWhatever("announce") != $AnnounceURL) {
                        $Torrent->setTrackers([$AnnounceURL]);
                    }
                }
                if ($privatetrackerpatch != "yes" && !$IsExternalTorrent && $Torrent->getWhatever("announce") != $AnnounceURL) {
                    $Torrent->addTracker($AnnounceURL);
                }
                if (strlen($Torrent->getPieces()) % 20 != 0) {
                    $UploadErrors[] = $lang->upload["error7"];
                }
            } else {
                $UploadErrors[] = $lang->global["error"] . ": " . htmlspecialchars_uni($Torrent->error);
            }
        } else {
            $UploadErrors[] = $lang->upload["error7"];
        }
    }
    if (count($UploadErrors) == 0) {
        $Torrent->setComment($lang->upload["DefaultTorrentComment"]);
        $Torrent->setCreatedBy(sprintf($lang->upload["CreatedBy"], $anonymous == "yes" ? "" : $CURUSER["username"]) . " [" . $SITENAME . "]");
        $Torrent->setSource($BASEURL);
        $Torrent->setCreationDate(TIMENOW);
        $check_info_hash = $Torrent->getHash();
        if ($info_hash != $check_info_hash) {
            $info_hash = $check_info_hash;
            unset($check_info_hash);
            $UpdateHash = true;
            $Info_hash_changed = true;
        }
        $numfiles = 0;
        $size = 0;
        $IncludedFiles = $Torrent->getFiles();
        foreach ($IncludedFiles as $File) {
            $numfiles++;
            $size += $File->length;
        }
        $filename = str_replace(".torrent", "", $torrentfile["name"]);
        $filename = MakeFriendlyText($filename);
        $filename = $filename . ".torrent";
        $UpdateSet = [];
        if (isset($UpdateHash) || !isset($EditTorrent)) {
            $UpdateSet[] = "info_hash = " . sqlesc($info_hash);
        }
        $UpdateSet[] = "name = " . sqlesc($name);
        $UpdateSet[] = "filename = " . sqlesc($filename);
        $UpdateSet[] = "category = " . sqlesc($category);
        $UpdateSet[] = "size = " . sqlesc($size);
        $UpdateSet[] = "numfiles = " . sqlesc($numfiles);
        if ($descr) {
            $UpdateSet[] = "descr = " . sqlesc($descr);
        }
        if (!isset($EditTorrent)) {
            $UpdateSet[] = "added = NOW()";
            $UpdateSet[] = "ctime = UNIX_TIMESTAMP()";
            $UpdateSet[] = "owner = " . sqlesc($CURUSER["id"]);
        } else {
            $UpdateSet[] = "mtime = UNIX_TIMESTAMP()";
        }
        if ($is_mod) {
            if ($free == "yes") {
                $UpdateSet[] = "free = 'yes'";
                $UpdateSet[] = "silver = 'no'";
                $UpdateSet[] = "download_multiplier = '0'";
            } else {
                if ($silver == "yes") {
                    $UpdateSet[] = "silver = 'yes'";
                    $UpdateSet[] = "free = 'no'";
                    $UpdateSet[] = "download_multiplier = '0.5'";
                } else {
                    $UpdateSet[] = "silver = 'no'";
                    $UpdateSet[] = "free = 'no'";
                    $UpdateSet[] = "download_multiplier = '1'";
                }
            }
            if ($doubleupload == "yes") {
                $UpdateSet[] = "doubleupload = 'yes'";
                $UpdateSet[] = "upload_multiplier = '2'";
            } else {
                $UpdateSet[] = "doubleupload = 'no'";
                $UpdateSet[] = "upload_multiplier = '1'";
            }
            $UpdateSet[] = "`flags` = '" . ($free == "yes" || $free != "yes" && $silver == "yes" || $doubleupload == "yes" ? 2 : 0) . "'";
            $UpdateSet[] = "trailerurl=''";
            if ($allowcomments == "no") {
                $UpdateSet[] = "allowcomments = 'no'";
            } else {
                $UpdateSet[] = "allowcomments = 'yes'";
            }
            if ($sticky == "yes") {
                $UpdateSet[] = "sticky = 'yes'";
            } else {
                $UpdateSet[] = "sticky = 'no'";
            }
            if ($isnuked == "yes") {
                $UpdateSet[] = "WhyNuked = " . sqlesc($WhyNuked);
                $UpdateSet[] = "isnuked = 'yes'";
            } else {
                $UpdateSet[] = "WhyNuked = ''";
                $UpdateSet[] = "isnuked = 'no'";
            }
        }
        if ($usergroups["canuploadddl"] == "yes" && get_extension($directdownloadlink) == "zip") {
            $UpdateSet[] = "directdownloadlink = " . sqlesc($directdownloadlink);
        } else {
            $UpdateSet[] = "directdownloadlink = ''";
        }
        if ($isrequest == "yes") {
            $UpdateSet[] = "isrequest = 'yes'";
        } else {
            $UpdateSet[] = "isrequest = 'no'";
        }
        if ($anonymous == "yes") {
            $UpdateSet[] = "anonymous = 'yes'";
        } else {
            $UpdateSet[] = "anonymous = 'no'";
        }
        if ($offensive == "yes") {
            $UpdateSet[] = "offensive = 'yes'";
        } else {
            $UpdateSet[] = "offensive = 'no'";
        }
        if ($IsExternalTorrent) {
            $UpdateSet[] = "ts_external = 'yes'";
            $UpdateSet[] = "ts_external_url = " . sqlesc($Torrent->getWhatever("announce"));
            $UpdateSet[] = "visible = 'yes'";
        } else {
            $UpdateSet[] = "ts_external = 'no'";
            $UpdateSet[] = "ts_external_url = ''";
            if (!isset($EditTorrent) && $xbt_active != "yes") {
                $UpdateSet[] = "visible = 'no'";
            }
        }
        if ($isScene && $isScene != -1) {
            $UpdateSet[] = "isScene = " . sqlesc(intval(TS_MTStoUTS($isScene)));
        } else {
            if ($isScene == -1) {
                $UpdateSet[] = "isScene = '0'";
            }
        }
        if (!isset($EditTorrentID)) {
            if ($ModerateTorrent) {
                $UpdateSet[] = "moderate = '1'";
            } else {
                $UpdateSet[] = "moderate = '0'";
            }
        }
        if (empty($t_link)) {
            $UpdateSet[] = "t_link = ''";
        } else {
            if (substr($t_link, -1, 1) != "/") {
                $t_link = $t_link . "/";
            }
            if (preg_match("@^https:\\/\\/www.imdb.com\\/title\\/(.*)\\/@isU", $t_link, $result) && $result[0]) {
                $t_link = $result[0];
                include_once INC_PATH . "/ts_imdb.php";
                $UpdateSet[] = "t_link = " . sqlesc($t_link);
                unset($result);
            }
        }
        if (empty($t_image_url)) {
            $UpdateSet[] = "t_image = ''";
        }
        sql_query((isset($EditTorrent) ? "UPDATE" : "INSERT INTO") . " torrents SET " . implode(", ", $UpdateSet) . (isset($EditTorrent) ? " WHERE $id = " . sqlesc($EditTorrentID) : "")) || stderr($lang->global["error"], $lang->upload["error13"] . " {" . htmlspecialchars_uni(mysqli_error($GLOBALS["DatabaseConnect"])) . "}");
        if (isset($EditTorrent)) {
            $NewTID = $EditTorrentID;
            if (is_file($torrent_dir . "/" . $NewTID . ".torrent")) {
                @unlink($torrent_dir . "/" . $NewTID . ".torrent");
            }
        } else {
            $NewTID = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        }
        if (!empty($t_image_url)) {
            $donotupdatesameimage = false;
            if (isset($EditTorrent["t_image"]) && !empty($EditTorrent["t_image"]) && $EditTorrent["t_image"] == $t_image_url) {
                $donotupdatesameimage = true;
            }
            if (!$donotupdatesameimage) {
                $t_image_url = fix_url($t_image_url);
                $AllowedFileTypes = ["jpg", "gif", "png"];
                $ImageExt = get_extension($t_image_url);
                if (in_array($ImageExt, $AllowedFileTypes, true)) {
                    $AllowedMimeTypes = ["image/jpeg", "image/gif", "image/png"];
                    $ImageDetails = getimagesize($t_image_url);
                    if ($ImageDetails && in_array($ImageDetails["mime"], $AllowedMimeTypes, true)) {
                        include_once INC_PATH . "/functions_ts_remote_connect.php";
                        if ($ImageContents = TS_Fetch_Data($t_image_url, false)) {
                            $NewImageURL = $torrent_dir . "/images/" . $NewTID . "." . $ImageExt;
                            if (file_exists($NewImageURL)) {
                                @unlink($NewImageURL);
                            }
                            if (file_put_contents($NewImageURL, $ImageContents)) {
                                $COVERIMAGEUPDATED = true;
                                sql_query("UPDATE torrents SET $t_image = " . sqlesc($BASEURL . "/" . $NewImageURL) . " WHERE $id = " . sqlesc($NewTID));
                            }
                        }
                    }
                }
            }
        }
        if (!empty($t_image_file) && 0 < $t_image_file["size"] && $t_image_file["error"] === 0 && $t_image_file["tmp_name"] && $t_image_file["name"]) {
            $t_image_url = fix_url($t_image_file["name"]);
            $AllowedFileTypes = ["jpg", "gif", "png"];
            $ImageExt = get_extension($t_image_url);
            if (in_array($ImageExt, $AllowedFileTypes, true)) {
                $AllowedMimeTypes = ["image/jpeg", "image/gif", "image/png"];
                $ImageDetails = getimagesize($t_image_file["tmp_name"]);
                if ($ImageDetails && in_array($ImageDetails["mime"], $AllowedMimeTypes, true) && ($ImageContents = file_get_contents($t_image_file["tmp_name"]))) {
                    $NewImageURL = $torrent_dir . "/images/" . $NewTID . "." . $ImageExt;
                    if (file_exists($NewImageURL)) {
                        @unlink($NewImageURL);
                    }
                    if (file_put_contents($NewImageURL, $ImageContents)) {
                        $COVERIMAGEUPDATED = true;
                        sql_query("UPDATE torrents SET $t_image = " . sqlesc($BASEURL . "/" . $NewImageURL) . " WHERE $id = " . sqlesc($NewTID));
                    }
                }
            }
        }
        if (!isset($COVERIMAGEUPDATED) && isset($cover_photo_name)) {
            sql_query("UPDATE torrents SET $t_image = " . sqlesc($BASEURL . "/" . $cover_photo_name) . " WHERE $id = " . sqlesc($NewTID));
        }
        if ($use_torrent_details == "yes") {
            sql_query("DELETE FROM ts_torrents_details WHERE $tid = " . sqlesc($NewTID));
            foreach ($video as $videoTrack) {
                if (!empty($videoTrack)) {
                    $InsertTdetails = true;
                }
            }
            foreach ($audio as $audioTrack) {
                if (!empty($audioTrack)) {
                    $InsertTdetails = true;
                }
            }
            if (isset($InsertTdetails)) {
                $video_info = implode("~", $video);
                $audio_info = implode("~", $audio);
                sql_query("INSERT INTO ts_torrents_details VALUES (NULL, " . sqlesc($NewTID) . ", " . sqlesc($video_info) . ", " . sqlesc($audio_info) . ")");
            }
        }
        if (isset($EditTorrent)) {
            write_log(sprintf($lang->upload["editedtorrent"], $name, $CURUSER["username"]));
        } else {
            write_log(sprintf($lang->upload["newtorrent"], $name, $CURUSER["username"]));
        }
        $TorrentContents = $Torrent->bencode();
        if ($TorrentContents && file_put_contents($torrent_dir . "/" . $NewTID . ".torrent", $TorrentContents)) {
            if ($IsExternalTorrent) {
                $externaltorrent = $torrent_dir . "/" . $NewTID . ".torrent";
                $externalTorrentId = $NewTID;
                include_once INC_PATH . "/ts_external_scrape/ts_external.php";
            }
            if (isset($NfoContents) && !empty($NfoContents)) {
                if ($UseNFOasDescr == "yes") {
                    $NewDescr = $BASEURL . "/viewnfo.php?$id = " . $NewTID;
                    sql_query("UPDATE torrents SET $descr = " . sqlesc($NewDescr) . " WHERE $id = " . sqlesc($NewTID));
                }
                sql_query("REPLACE INTO ts_nfo (id, nfo) VALUES (" . $NewTID . ", " . sqlesc($NfoContents) . ")");
            }
            if ($ModerateTorrent && !isset($EditTorrentID)) {
                $msgtext = sprintf($lang->upload["modmsgss"], $name, $CURUSER["username"], "[URL]" . $BASEURL . "/details.php?$id = " . $NewTID . "[/URL]");
                $message = sqlesc($msgtext);
                $subject = sqlesc($lang->upload["modmsgs"]);
                sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(0, NOW(), " . $message . ", " . $subject . ")") || sqlerr(__FILE__, 743);
                stderr($lang->upload["title"], $lang->upload["mulmsg"]);
            } else {
                if (!isset($EditTorrent)) {
                    $TSSEConfig->TSLoadConfig("KPS");
                    KPS("+", $kpsupload, $CURUSER["id"]);
                    $res = sql_query("SELECT name FROM categories WHERE $id = " . sqlesc($category));
                    $Result = mysqli_fetch_assoc($res);
                    $cat = $Result["name"];
                    $res = sql_query("SELECT u.email FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$enabled = 'yes' AND u.$status = 'confirmed' AND u.notifs LIKE '%[cat" . $category . "]%' AND u.notifs LIKE '%[email]%' AND u.notifs != '' AND g.$isvipgroup = 'yes' AND g.$canemailnotify = 'yes'");
                    $size = mksize($size);
                    $body = sprintf($lang->upload["emailbody"], $name, $size, $cat, $anonymous == "yes" ? "N/A" : $CURUSER["username"], $descr, $BASEURL, $NewTID, $SITENAME);
                    $emailRecipients = "";
                    $nmax = 100;
                    $nthis = $ntotal = 0;
                    $total = mysqli_num_rows($res);
                    if (0 < $total) {
                        while ($emailRow = mysqli_fetch_row($res)) {
                            if ($nthis == 0) {
                                $emailRecipients = $emailRow[0];
                            } else {
                                $emailRecipients .= "," . $emailRow[0];
                            }
                            $nthis++;
                            $ntotal++;
                            if ($nthis == $nmax || $ntotal == $total) {
                                $emailSendResult = sent_mail($emailRecipients, sprintf($lang->upload["emailsubject"], $SITENAME, $name), $body, "ts_upload_torrent", false);
                                $nthis = 0;
                            }
                        }
                    }
                    $TSSEConfig->TSLoadConfig("PJIRC");
                    if ($ircbot == "yes" && ($connect = @fsockopen($botip, $botport, $errno, $errstr))) {
                        $botmessage = chr(3) . "9" . chr(2) . " " . $SITENAME . chr(2) . " -" . chr(3) . "10 New Torrent: (" . chr(3) . "13 " . $name . chr(3) . "10 ) Size: (" . chr(3) . "13 " . $size . chr(3) . "10 )  Category: (" . chr(3) . "13 " . $cat . chr(3) . "10 ) Uploader: (" . chr(3) . "13 " . ($anonymous == "yes" ? "N/A" : $CURUSER["username"]) . chr(3) . "10 ) Link: (" . chr(3) . "13 " . $BASEURL . "/details.php?$id = " . $NewTID . chr(3) . "10 )\r\n";
                        @fwrite($connect, $botmessage);
                        @fclose($connect);
                    }
                    $TSSEConfig->TSLoadConfig("SHOUTBOX");
                    if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "upload")) {
                        $seo_link = ts_seo($NewTID, $name, "s");
                        $shoutbOT = sprintf($lang->upload["shoutbOT"], $seo_link, $name, $anonymous == "yes" ? "N/A" : $CURUSER["username"]);
                        require INC_PATH . "/functions_ajax_chatbot.php";
                        TSAjaxShoutBOT($shoutbOT);
                    }
                    if (file_exists(TSDIR . "/" . $cache . "/latesttorrents.html")) {
                        @unlink(TSDIR . "/" . $cache . "/latesttorrents.html");
                    }
                }
            }
            if (isset($Info_hash_changed)) {
                stdhead($lang->upload["title"]);
                echo show_notice(sprintf($lang->upload["done"], "download.php?$id = " . $NewTID, "details.php?$id = " . $NewTID), false, $lang->upload["title"]);
                stdfoot();
                exit;
            }
            redirect("details.php?$id = " . $NewTID);
            exit;
        }
        $UploadErrors[] = $lang->upload["error8"];
    }
}
require INC_PATH . "/functions_category.php";
$postoptionstitle = [3 => $lang->upload["category"], 4 => $lang->upload["cover"], 5 => $lang->upload["t_link"], 6 => $lang->upload["isScene1"], 7 => $lang->upload["offensive1"], 8 => $lang->upload["anonymous1"], 9 => $lang->upload["isrequest1"]];
$postoptions = [3 => ts_category_list("category", isset($category) ? $category : ""), 4 => "\r\n\t\t<input $type = \"radio\" $name = \"nothingtopost\" $value = \"1\" $onclick = \"ChangeBox(this.value);\" $checked = \"checked\" /> " . $lang->upload["cover1"] . "\r\n\t\t<div $style = \"display: inline;\" $id = \"nothingtopost1\">\r\n\t\t\t<br /><input $type = \"text\" $name = \"t_image_url\" $id = \"specialboxg\" $size = \"70\" $value = \"" . (isset($t_image_url) ? htmlspecialchars_uni($t_image_url) : "") . "\" />\r\n\t\t</div>\r\n\t\t<br />\r\n\t\t\r\n\t\t<input $type = \"radio\" $name = \"nothingtopost\" $value = \"2\" $onclick = \"ChangeBox(this.value);\" /> " . $lang->upload["cover2"] . "\r\n\t\t<div $style = \"display: none;\" $id = \"nothingtopost2\">\r\n\t\t\t<br /><input $type = \"file\" $name = \"t_image_file\" $id = \"specialboxg\" $size = \"70\" />\r\n\t\t</div>", 5 => "<input $type = \"text\" $name = \"t_link\" $id = \"specialboxg\" $size = \"70\" $value = \"" . (isset($t_link) ? htmlspecialchars_uni($t_link) : "") . "\" /> " . $lang->upload["t_link2"], 6 => "<input $type = \"text\" $name = \"isScene\" $id = \"isScene\" $value = \"" . (isset($isScene) ? htmlspecialchars_uni($isScene) : "") . "\" $size = \"20\" /> " . $lang->upload["isScene2"], 7 => "<input $type = \"checkbox\" $name = \"offensive\" $value = \"yes\"" . (isset($offensive) && $offensive == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["offensive2"], 8 => "<input $type = \"checkbox\" $name = \"anonymous\" $value = \"yes\"" . (isset($anonymous) && $anonymous == "yes" || TS_Match($CURUSER["options"], "I3") || TS_Match($CURUSER["options"], "I4") ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["anonymous2"], 9 => "<input $type = \"checkbox\" $name = \"isrequest\" $value = \"yes\"" . (isset($isrequest) && $isrequest == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["isrequest2"]];
if ($is_mod) {
    $postoptionstitle[] = "<fieldset><legend>" . $lang->upload["moptions"] . "</legend>";
    $postoptions[] = "\r\n\t<table $width = \"100%\" $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["free1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"free\" $value = \"yes\"" . (isset($free) && $free == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["free2"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["silver1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"silver\" $value = \"yes\"" . (isset($silver) && $silver == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["silver2"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["doubleupload1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"doubleupload\" $value = \"yes\"" . (isset($doubleupload) && $doubleupload == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["doubleupload2"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["allowcomments1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"allowcomments\" $value = \"no\"" . (isset($allowcomments) && $allowcomments == "no" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["allowcomments2"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["sticky1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"sticky\" $value = \"yes\"" . (isset($sticky) && $sticky == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" /> " . $lang->upload["sticky2"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<b>" . $lang->upload["nuked1"] . "</b><br />\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"isnuked\" $value = \"yes\"" . (isset($isnuked) && $isnuked == "yes" ? " $checked = \"checked\"" : "") . " class=\"inlineimg\" $onclick = \"ShowHideField('nukereason');\" /> " . $lang->upload["nuked2"] . "\r\n\t\t\t\t<div $style = \"display:" . (isset($isnuked) && $isnuked == "yes" ? "inline" : "none") . ";\" $id = \"nukereason\">\r\n\t\t\t\t\t<br /><b>" . $lang->upload["nreason"] . "</b> <input $type = \"text\" $name = \"WhyNuked\" $value = \"" . (isset($WhyNuked) ? htmlspecialchars_uni($WhyNuked) : "") . "\" $size = \"40\" />\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</fieldset>";
}
if ($use_torrent_details == "yes") {
    $postoptionstitle[] = "<fieldset><legend>" . $lang->upload["tinfo"] . "</legend>";
    $postoptions[] = "\r\n\t<table $width = \"100%\" $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tr>\r\n\t\t\t\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<table $width = \"100%\" $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\" $colspan = \"2\">" . $lang->upload["video"] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["codec"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"video[codec]\" $size = \"20\" $value = \"" . (isset($video) && $video["codec"] ? htmlspecialchars_uni($video["codec"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["bitrate"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"video[bitrate]\" $size = \"20\" $value = \"" . (isset($video) && $video["bitrate"] ? htmlspecialchars_uni($video["bitrate"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["resulation"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"video[resulation]\" $size = \"20\" $value = \"" . (isset($video) && $video["resulation"] ? htmlspecialchars_uni($video["resulation"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["length"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"video[length]\" $size = \"20\" $value = \"" . (isset($video) && $video["length"] ? htmlspecialchars_uni($video["length"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["quality"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"video[quality]\" $size = \"20\" $value = \"" . (isset($video) && $video["quality"] ? htmlspecialchars_uni($video["quality"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<table $width = \"100%\" $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\" $colspan = \"2\">" . $lang->upload["audio"] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["codec"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"audio[codec]\" $size = \"20\" $value = \"" . (isset($audio) && $audio["codec"] ? htmlspecialchars_uni($audio["codec"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["bitrate"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"audio[bitrate]\" $size = \"20\" $value = \"" . (isset($audio) && $audio["bitrate"] ? htmlspecialchars_uni($audio["bitrate"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["frequency"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"audio[frequency]\" $size = \"20\" $value = \"" . (isset($audio) && $audio["frequency"] ? htmlspecialchars_uni($audio["frequency"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>" . $lang->upload["language"] . "</td>\r\n\t\t\t\t\t\t<td><input $type = \"text\" $name = \"audio[language]\" $size = \"20\" $value = \"" . (isset($audio) && $audio["language"] ? htmlspecialchars_uni($audio["language"]) : "") . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $colspan = \"2\"><i>" . $lang->upload["tinfohelp"] . "</i></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\r\n\t\t</tr>\r\n\t</table>\r\n\t</fieldset>";
}
$str = "";
if ($search_before_upload == "yes") {
    define("SUBJECT_EXTRA", " $onKeyDown = \"SearchBeforeUpload(this);\" $onKeyUp = \"SearchBeforeUpload(this);\"");
    $str .= "\r\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_searchbu.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction SearchBeforeUpload(Whatever)\r\n\t\t{\r\n\t\t\tvar $subject = Whatever.value;\r\n\t\t\tvar $subjectlength = subject.length;\r\n\t\t\tvar $defaultContent = '<img $src = \"" . $BASEURL . "/include/templates/" . ts_template() . "/images/loading.gif\" $border = \"0\" $alt = \"" . $lang->global["pleasewait"] . "\" $title = \"" . $lang->global["pleasewait"] . "\" class=\"inlineimg\" /> " . $lang->upload["sbu_wait"] . "';\r\n\r\n\t\t\tif (subjectlength > 2)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"search_before_upload\").style.$display = \"inline\";\r\n\t\t\t\tget(subject);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"search_before_upload\").style.$display = \"none\";\r\n\t\t\t\tTSGetID(\"search_before_upload_results\").$innerHTML = defaultContent;\r\n\t\t\t}\r\n\t\t}\r\n\t\tfunction EnableDisableField(fName)\r\n\t\t{\r\n\t\t\tif(TSGetID(fName).disabled)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(fName).$disabled = \"\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tTSGetID(fName).$value = \"\";\r\n\t\t\t\tTSGetID(fName).$disabled = \"disabled\";\r\n\t\t\t}\r\n\t\t}\r\n\t\tfunction ShowHideField(fName)\r\n\t\t{\r\n\t\t\tif(TSGetID(fName).style.$display = = \"inline\")\r\n\t\t\t{\r\n\t\t\t\tTSGetID(fName).style.$display = \"none\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tTSGetID(fName).style.$display = \"inline\";\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t\r\n\t<div $id = \"search_before_upload\" $style = \"display: none;\">\r\n\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . ts_collapse("sbu") . "\r\n\t\t\t\t\t" . $lang->upload["sbu"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ts_collapse("sbu", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<div $id = \"search_before_upload_results\">\t\t\t\t\t\t\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t<br />\r\n\t</div>";
}
$str .= "\r\n<div $id = \"ts_uploading_progress\">\r\n</div>\r\n<div $id = \"ts_upload_form\">\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . (isset($EditTorrent) ? "?$id = " . $EditTorrentID : "") . "\" $name = \"ts_upload_torrent\" $enctype = \"multipart/form-data\" $onsubmit = \"return ProcessUpload();\">";
$FirstTabs = [$lang->upload["torrentfile"] => "<input $type = \"file\" $name = \"torrentfile\" $id = \"torrentfile\" $onchange = \"checkFileName(this.value);\" />" . ($CanUploadExternalTorrent ? " <input $type = \"checkbox\" $name = \"IsExternalTorrent\" $value = \"yes\"" . (isset($IsExternalTorrent) && $IsExternalTorrent ? " $checked = \"checked\"" : "") . " /> " . $lang->upload["isexternal"] : ""), $lang->upload["nfofile"] => "<input $type = \"file\" $name = \"nfofile\" $id = \"nfofile\" $onchange = \"checknfoFileName(this.value);\" /> <input $type = \"checkbox\" $name = \"UseNFOasDescr\" $value = \"yes\"" . (isset($UseNFOasDescr) && $UseNFOasDescr == "yes" ? " $checked = \"checked\"" : "") . " $onclick = \"enableDisableTA(this.checked);\" /> " . $lang->upload["UseNFOasDescr"], $lang->upload["tname"] => "<input $type = \"text\" $name = \"subject\" $id = \"subject\" $style = \"width: 650px\" $value = \"" . (isset($name) ? htmlspecialchars_uni($name) : "") . "\" $tabindex = \"1\"" . (defined("SUBJECT_EXTRA") ? SUBJECT_EXTRA : "") . " $autocomplete = \"off\" />"];
if ($usergroups["canuploadddl"] == "yes") {
    $FirstTabs = array_merge($FirstTabs, [$lang->global["directdownloadlink"] => "<input $type = \"text\" $name = \"directdownloadlink\" $id = \"directdownloadlink\" $style = \"width: 650px\" $value = \"" . (isset($directdownloadlink) ? htmlspecialchars_uni($directdownloadlink) : "") . "\" $tabindex = \"1\" /> <small>" . $lang->global["ddlallowedfiletype"] . "</small>"]);
}
$str .= insert_editor(false, "", isset($descr) ? $descr : "", $lang->upload["title"], sprintf($lang->upload["title2"], $AnnounceURL), $postoptionstitle, $postoptions, false, "", isset($EditTorrent) ? $lang->upload["savechanges"] : $lang->upload["title"], "", "", $FirstTabs);
$str .= "\r\n\t</form>\r\n</div>\r\n<script $type = \"text/javascript\">\r\n\tfunction file_get_ext(filename)\r\n\t{\r\n\t\treturn typeof filename != \"undefined\" ? filename.substring(filename.lastIndexOf(\".\")+1, filename.length).toLowerCase() : false;\r\n\t}\r\n\r\n\tfunction checkFileName(path)\r\n\t{\r\n\t\tvar $ext = file_get_ext(path);\r\n\t\t//var $fullpath = path;\r\n\t\t//var $newpath = fullpath.substring(0, fullpath.length-8);\r\n\t\t//TSGetID(\"subject\").$value = newpath;\r\n\r\n\t\tif (!ext || ext != \"torrent\")\r\n\t\t{\r\n\t\t\talert(\"" . strip_tags($lang->upload["error3"]) . "\");\r\n\t\t}\r\n\t}\r\n\r\n\tfunction checknfoFileName(path)\r\n\t{\r\n\t\tvar $ext = file_get_ext(path);\r\n\r\n\t\tif (!ext || ext != \"nfo\")\r\n\t\t{\r\n\t\t\talert(\"" . strip_tags($lang->upload["error10"]) . "\");\r\n\t\t}\r\n\t}\r\n\r\n\tfunction enableDisableTA(cStatus)\r\n\t{\r\n\t\tif ($cStatus = = true)\r\n\t\t{\r\n\t\t\tTSGetID(\"message_new\").$disabled = \"disabled\";\r\n\t\t\tTSGetID(\"message_old\").$disabled = \"disabled\";\r\n\t\t\tTSGetID(\"message_new\").$value = \"\";\r\n\t\t\tTSGetID(\"message_old\").$value = \"\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"message_new\").$disabled = \"\";\r\n\t\t\tTSGetID(\"message_old\").$disabled = \"\";\r\n\t\t}\r\n\t}\r\n\r\n\tfunction ProcessUpload()\r\n\t{\r\n\t\tTSGetID('ts_uploading_progress').$innerHTML = '<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\"><tr><td class=\"thead\">" . $lang->global["pleasewait"] . "</td></tr><tr><td><img $src = \"include/templates/" . ts_template() . "/images/loading.gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" /> " . trim($lang->upload["uploading"]) . "</td></tr></table>';\r\n\t\tTSGetID('ts_upload_form').style.$display = 'none';\r\n\t\treturn true;\r\n\t}\r\n\r\n\tfunction ChangeBox(BoxValue)\r\n\t{\r\n\t\tTSGetID(\"nothingtopost1\").style.$display = \"none\";\r\n\t\tTSGetID(\"nothingtopost2\").style.$display = \"none\";\r\n\t\tTSGetID(\"nothingtopost\"+BoxValue).style.$display = \"inline\";\r\n\t}\r\n</script>";
if (isset($upload_page_notice) && !empty($upload_page_notice)) {
    $str = show_notice($upload_page_notice) . $str;
}
stdhead($lang->upload["title"]);
show_upload_errors();
echo $str;
stdfoot();
function show_upload_errors()
{
    global $UploadErrors;
    global $lang;
    global $BASEURL;
    global $pic_base_url;
    if (0 < count($UploadErrors)) {
        $Errors = "";
        foreach ($UploadErrors as $Error) {
            $Errors .= "<img $src = \"" . $pic_base_url . "error.gif\" $border = \"0\" $alt = \"\" $title = \"\" /> " . $Error . "<br />";
        }
        echo show_notice($Errors, 1, $lang->upload["error"]);
        unset($Errors);
    }
}

?>