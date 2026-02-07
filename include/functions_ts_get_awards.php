<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function TSGetAwards($Userid = "", $Username = "")
{
    global $BASEURL;
    global $pic_base_url;
    global $usergroups;
    global $is_mod;
    global $staffcp_path;
    $Query = sql_query("SELECT a.userid, a.reason, a.date, aw.award_name, aw.award_image FROM ts_awards_users a LEFT JOIN ts_awards aw ON (a.award_id = aw.award_id) WHERE a.userid = '" . intval($Userid) . "' ORDER by aw.award_sort");
    if (mysqli_num_rows($Query)) {
        $Return = "";
        while ($Award = mysqli_fetch_assoc($Query)) {
            $ATooltip = "<strong>" . htmlspecialchars_uni($Award["award_name"]) . "</strong><br /><small>" . addslashes(htmlspecialchars_uni($Award["reason"])) . "</small>";
            $Return .= "\r\n\t\t\t<i $onmouseover = \"ddrivetip('" . $ATooltip . "', 200)\"; $onmouseout = \"hideddrivetip()\"><img $src = \"" . $pic_base_url . "awardmedals/" . htmlspecialchars_uni($Award["award_image"]) . "\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" $width = \"10\" $height = \"19\" $style = \"cursor: pointer;\" /></i>\r\n\t\t\t";
        }
        return $is_mod ? "<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_awards&amp;$username = " . htmlspecialchars_uni($Username) . "\">" . $Return . "</a>" : $Return;
    }
}

?>