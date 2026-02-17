<?php

declare(strict_types=1);

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
class TS_Rating
{
    public $HasVoted = NULL;
    public $ratingid = NULL;
    public $userid = NULL;
    public $SavedScore = NULL;
    public function __construct($ratingid, $userid = "")
    {
        $this->$ratingid = trim($ratingid);
        $this->$userid = intval($userid);
        if (!$this->userid) {
            $this->HasVoted = true;
            return false;
        }
        $Query = sql_query("SELECT userid FROM ts_ratings WHERE $ratingid = " . sqlesc($this->ratingid) . " AND $userid = " . sqlesc($this->userid));
        if (0 < mysqli_num_rows($Query)) {
            $this->HasVoted = true;
        } else {
            $this->HasVoted = false;
        }
    }
    public function calculateScore($score)
    {
        $score = intval($score);
        if (!$this->HasVoted && is_valid_id($score) && 1 <= $score && $score <= 10) {
            sql_query("INSERT INTO ts_ratings (ratingid, userid, score, ratedate) VALUES (" . sqlesc($this->ratingid) . ", " . sqlesc($this->userid) . ", " . sqlesc($score) . ", " . sqlesc(TIMENOW) . ")");
            return true;
        }
        return false;
    }
    public function displayForm($Text = "")
    {
        global $BASEURL;
        global $lang;
        global $usergroups;
        global $CURUSER;
        if (!$this->HasVoted && $usergroups["canrate"] == "yes") {
            return "\r\n\t\t\t<div $style = \"padding-top: 5px;\">\t\t\t\t\r\n\t\t\t\t<form $method = \"POST\" $action = \"" . $BASEURL . "/ts_rate.php\" $name = \"quickrate\" $id = \"quickrate\">\r\n\t\t\t\t\t<input $type = \"hidden\" $name = \"userid\" $value = \"" . $this->userid . "\" />\r\n\t\t\t\t\t<input $type = \"hidden\" $name = \"ratingid\" $value = \"" . htmlspecialchars($this->ratingid) . "\" />\r\n\t\t\t\t\t" . (isset($CURUSER) && $CURUSER["securitytoken"] ? "<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . $CURUSER["securitytoken"] . "\" />" : "") . "\r\n\t\t\t\t\t" . $Text . "\r\n\t\t\t\t\t<select $name = \"score\" $id = \"score\">\r\n\t\t\t\t\t<option $value = \"0\"></option>\r\n\t\t\t\t\t\t<option $value = \"1\">1</option>\r\n\t\t\t\t\t\t<option $value = \"2\">2</option>\r\n\t\t\t\t\t\t<option $value = \"3\">3</option>\r\n\t\t\t\t\t\t<option $value = \"4\">4</option>\r\n\t\t\t\t\t\t<option $value = \"5\">5</option>\r\n\t\t\t\t\t\t<option $value = \"6\">6</option>\r\n\t\t\t\t\t\t<option $value = \"7\">7</option>\r\n\t\t\t\t\t\t<option $value = \"8\">8</option>\r\n\t\t\t\t\t\t<option $value = \"9\">9</option>\r\n\t\t\t\t\t\t<option $value = \"10\">10</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t\t<input $type = \"button\" class=\"bgn\" $value = \"" . $lang->global["buttongo"] . "\" $name = \"submitqr\" $id = \"submitqr\" $onclick = \"javascript:TSQuickRate('" . htmlspecialchars($this->ratingid) . "', '" . $this->userid . "');\" />\r\n\t\t\t\t</form>\r\n\t\t\t</div>\r\n\t\t\t";
        }
        return "";
    }
    public function GetScore($ImageAltText)
    {
        global $pic_base_url;
        global $BASEURL;
        global $lang;
        $Query = sql_query("SELECT score FROM ts_ratings WHERE $ratingid = " . sqlesc($this->ratingid));
        if (0 < mysqli_num_rows($Query)) {
            for ($TotalScore = $TotalScorers = 0; $Ratings = mysqli_fetch_row($Query); $TotalScorers++) {
                $TotalScore += $Ratings[0];
            }
            $Image = 0 < $TotalScorers ? round($TotalScore / $TotalScorers) : 0;
            $Average = 0 < $TotalScorers ? round($TotalScore / $TotalScorers, 1) : 0;
            $ImageAlt = sprintf($ImageAltText, $Average, $TotalScorers);
            $this->SavedScore = "<img $src = \"" . $pic_base_url . "imdb_rating/" . $Image . "-10.png\" $alt = \"" . $ImageAlt . "\" $title = \"" . $ImageAlt . "\" $border = \"0\" class=\"inlineimg\" /><br /><small><i>" . $ImageAlt . "</i></small>";
        }
        return $this->SavedScore;
    }
}

?>