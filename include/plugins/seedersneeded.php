<?php
if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$seedersneeded = "";
$Query = sql_query("SELECT id, name, seeders, leechers FROM torrents WHERE leechers > 0 AND $seeders = 0 ORDER BY leechers DESC LIMIT 10");
if (0 < mysqli_num_rows($Query)) {
    $seedersneeded .= "\r\n\t<table $width = \"100%\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $align = \"left\" $width = \"80%\">Torrent Name</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\">Seeders</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\">Leechers</td>\r\n\t</tr>";
    while ($Torrent = mysqli_fetch_assoc($Query)) {
        $seolink = ts_seo($Torrent["id"], $Torrent["name"], "s");
        $seedersneeded .= "\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" $width = \"80%\"><a $href = \"" . $seolink . "\" $target = \"_top\"><b>" . htmlspecialchars_uni($Torrent["name"]) . "</b></a></td>\r\n\t\t\t<td $align = \"center\" $width = \"10%\"><font $color = \"#ff0000\">" . $Torrent["seeders"] . "</font></td>\r\n\t\t\t<td $align = \"center\" $width = \"10%\">" . ts_nf($Torrent["leechers"]) . "</td>\r\n\t\t</tr>";
    }
    $seedersneeded .= " </table>";
} else {
    $seedersneeded .= "<div $align = \"center\"><b>All Torrents have Seeders.</b></div>";
}

?>