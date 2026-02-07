<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.5 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
if ($TSSECache->Cache["total_funds"]["lastupdate"] < TIMENOW - 3600) {
    $Query = sql_query("SELECT SUM(cash) AS total_funds FROM funds WHERE cash > 0");
    if (mysqli_num_rows($Query)) {
        $TSSEConfig->TSLoadConfig("PAYPAL");
        $Result = mysqli_fetch_assoc($Query);
        $total_funds = $Result["total_funds"];
        $Progress_so_far = $total_funds / $tn * 100;
        $Progress_so_far = 100 <= $Progress_so_far ? 100 : number_format($Progress_so_far, 1);
    } else {
        $Progress_so_far = "1.0";
    }
    $TSSECache->UpdateCache("total_funds", $Progress_so_far);
}
$Progress_so_far = $TSSECache->Cache["total_funds"]["content"];
$donations = "\r\n<!-- begin donations -->\r\n\t<div id=\"donation\" align=\"center\">\r\n\t\t<font class=\"small\">\r\n\t\t\t<a href=\"" . $BASEURL . "/donate.php\" onclick=\"window.open('" . $BASEURL . "/scripts/pbar/ts_donation_status.php','ts_donation_status','toolbar=no, scrollbars=no, resizable=no, width=600, height=300, top=250, left=250'); return false;\">" . $lang->header["donate"] . "</a>\r\n\t\t</font>\r\n\t\t<div style=\"width: 80px; border: 1px solid black; text-align: left; background: #ffffff repeat;\">\r\n\t\t\t<div style=\"padding-left: 0px; color: white; font-weight: bold; width: " . $Progress_so_far . "%; border: 0px solid black; font-size: 8pt; background: #e3452f repeat;\">\r\n\t\t\t\t&nbsp;" . $Progress_so_far . "%" . (100 <= $Progress_so_far ? "&nbsp;<font class=\"small\">" . $lang->header["thanks"] . "</font>" : "") . "\r\n\t\t\t</div>\r\n\t\t</div>\r\n\t</div>\r\n<!-- end donations -->";

?>