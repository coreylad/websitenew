<?php
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("SL_VERSION", "3.2 by xam");
define("THIS_SCRIPT", "sl.php");
require "./global.php";
if (!isset($CURUSER)) {
    exit($lang->global("permission"));
}
if (!isset($_GET["album"])) {
    $category = intval(TS_Global("category"));
    $WHERE = " WHERE t.$visible = 'yes' AND t.$banned = 'no' AND t.t_image != '' AND t.$moderate = '0'";
    $WHERE .= " AND (INSTR(CONCAT(',',c.canview,','),',[ALL],') > 0 OR INSTR(CONCAT(',',c.canview,','),'," . $CURUSER["usergroup"] . ",') > 0)";
    if (TS_Match($CURUSER["options"], "E0")) {
        $WHERE .= " AND t.$offensive = 'no'";
    }
    if ($category) {
        $WHERE .= " AND t.$category = '" . $category . "'";
    }
    $LTC = "";
    $LQuery = sql_query("SELECT t.id, t.name, t.t_image, t.seeders, t.leechers FROM torrents t LEFT JOIN categories c ON (t.$category = c.id)" . $WHERE . " ORDER BY t.added DESC LIMIT " . $ts_perpage);
    if (mysqli_num_rows($LQuery)) {
        while ($LT = mysqli_fetch_assoc($LQuery)) {
            $LTTNAME = htmlspecialchars_uni($LT["name"]);
            $alttitle = $LTTNAME . " -- " . $lang->global["seeders"] . ": " . ts_nf($LT["seeders"]) . " -- " . $lang->global["leechers"] . ": " . ts_nf($LT["leechers"]);
            $LTC .= "<li><a $href = \"" . ts_seo($LT["id"], $LT["name"], "s") . "\" $title = \"" . $alttitle . "\"><img $src = \"" . htmlspecialchars_uni($LT["t_image"]) . "\" $height = \"110\" $width = \"110\" $alt = \"" . $alttitle . "\" $title = \"" . $alttitle . "\" /></a></li>";
        }
    }
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\r\n  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n<html $xmlns = \"http://www.w3.org/1999/xhtml\" xml:$lang = \"en\" $lang = \"en\">\r\n\t<head>\r\n\t\t<meta http-$equiv = \"Content-type\" $content = \"text/html; $charset = ";
    echo $charset;
    echo "\" />\r\n\t\t<title>";
    echo $SITENAME;
    echo "</title>\r\n\r\n\t\t<link $rel = \"stylesheet\" $type = \"text/css\" $href = \"";
    echo $BASEURL;
    echo "/style.php?$theme = ";
    echo ts_template();
    echo "&$style = style.css\" />\r\n\r\n\t\t<style $type = \"text/css\">\r\n\t\t\tbody\r\n\t\t\t{\r\n\t\t\t\tbackground: #ffffff;\r\n\t\t\t}\r\n\t\t</style>\r\n\r\n\t\t<script $type = \"text/javascript\" $src = \"";
    echo $BASEURL;
    echo "/scripts/jquery-1.11.2.min.js\"></script>\r\n\r\n\t\t<script $type = \"text/javascript\" $charset = \"";
    echo $charset;
    echo "\">\r\n\r\n\t\t\t(function () {\r\n\t\t\t\t\$.fn.$infiniteCarousel = function () {\r\n\t\t\t\t\tfunction repeat(str, n) {\r\n\t\t\t\t\t\treturn new Array( n + 1 ).join(str);\r\n\t\t\t\t\t}\r\n\t\t\t\t\t\r\n\t\t\t\t\treturn this.each(function () {\r\n\t\t\t\t\t\t// magic!\r\n\t\t\t\t\t\tvar \$wrapper = \$('> div', this).css('overflow', 'hidden'),\r\n\t\t\t\t\t\t\t\$slider = \$wrapper.find('> ul').width(9999),\r\n\t\t\t\t\t\t\t\$items = \$slider.find('> li'),\r\n\t\t\t\t\t\t\t\$single = \$items.filter(':first')\r\n\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\$tsingleWidth = \$single.outerWidth(),\r\n\t\t\t\t\t\t\$tvisible = Math.ceil(\$wrapper.innerWidth() / singleWidth),\r\n\t\t\t\t\t\t\$tcurrentPage = 1,\r\n\t\t\t\t\t\t\$tpages = Math.ceil(\$items.length / visible);\r\n\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t/* TASKS */\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 1. pad the pages with empty element if required\r\n\t\t\t\t\t\tif (\$items.length % visible != 0) {\r\n\t\t\t\t\t\t\t// pad\r\n\t\t\t\t\t\t\t\$slider.append(repeat('<li class=\"empty\" />', visible - (\$items.length % visible)));\r\n\t\t\t\t\t\t\t\$items = \$slider.find('> li');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 2. create the carousel padding on left and right (cloned)\r\n\t\t\t\t\t\t\$items.filter(':first').before(\$items.slice(-visible).clone().addClass('cloned'));\r\n\t\t\t\t\t\t\$items.filter(':last').after(\$items.slice(0, visible).clone().addClass('cloned'));\r\n\t\t\t\t\t\t\$items = \$slider.find('> li');\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 3. reset scroll\r\n\t\t\t\t\t\t\$wrapper.scrollLeft(singleWidth * visible);\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 4. paging function\r\n\t\t\t\t\t\tfunction gotoPage(page) {\r\n\t\t\t\t\t\t\tvar $dir = page < currentPage ? -1 : 1,\r\n\t\t\t\t\t\t\t\$tn = Math.abs(currentPage - page),\r\n\t\t\t\t\t\t\t\$tleft = singleWidth * dir * visible * n;\r\n\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\$wrapper.filter(':not(:animated)').animate({\r\n\t\t\t\t\t\t\t\tscrollLeft : '+=' + left\r\n\t\t\t\t\t\t\t}, 2500, function () {\r\n\t\t\t\t\t\t\t\t// if $page = = last page - then reset position\r\n\t\t\t\t\t\t\t\tif (page > pages) {\r\n\t\t\t\t\t\t\t\t\t\$wrapper.scrollLeft(singleWidth * visible);\r\n\t\t\t\t\t\t\t\t\$tpage = 1;\r\n\t\t\t\t\t\t\t\t} else if ($page = = 0) {\r\n\t\t\t\t\t\t\t\t\$tpage = pages;\r\n\t\t\t\t\t\t\t\t\t\$wrapper.scrollLeft(singleWidth * visible * pages);\r\n\t\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\$tcurrentPage = page;\r\n\t\t\t\t\t\t\t});\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 5. insert the back and forward link\r\n\t\t\t\t\t\t\$wrapper.after('<a $href = \"javascript:void(0);\" class=\"arrow back\"></a><a $href = \"javascript:void(0);\" class=\"arrow forward\"></a>');\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// 6. bind the back and forward links\r\n\t\t\t\t\t\t\$('a.back', this).click(function () {\r\n\t\t\t\t\t\t\tgotoPage(currentPage - 1);\r\n\t\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t\t});\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t\$('a.forward', this).click(function () {\r\n\t\t\t\t\t\t\tgotoPage(currentPage + 1);\r\n\t\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t\t});\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t\$(this).bind('goto', function (event, page) {\r\n\t\t\t\t\t\t\tgotoPage(page);\r\n\t\t\t\t\t\t});\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t// THIS IS NEW CODE FOR THE AUTOMATIC INFINITE CAROUSEL\r\n\t\t\t\t\t\t\$(this).bind('next', function () {\r\n\t\t\t\t\t\t\tgotoPage(currentPage + 1);\r\n\t\t\t\t\t\t});\r\n\t\t\t\t\t});\r\n\t\t\t\t};\r\n\t\t\t})(jQuery);\r\n\r\n\t\t\t\$(document).ready(function () {\r\n\t\t\t\t// THIS IS NEW CODE FOR THE AUTOMATIC INFINITE CAROUSEL\r\n\t\t\t\tvar $autoscrolling = true;\r\n\t\t\t\t\r\n\t\t\t\t\$('.infiniteCarousel').infiniteCarousel().mouseover(function () {\r\n\t\t\t\t\$tautoscrolling = false;\r\n\t\t\t\t}).mouseout(function () {\r\n\t\t\t\t\$tautoscrolling = true;\r\n\t\t\t\t});\r\n\t\t\t\t\r\n\t\t\t\tsetInterval(function () {\r\n\t\t\t\t\tif (autoscrolling) {\r\n\t\t\t\t\t\t\$('.infiniteCarousel').trigger('next');\r\n\t\t\t\t\t}\r\n\t\t\t\t}, 2000);\r\n\t\t\t});\r\n\r\n\t\t</script>\r\n\r\n\t</head>\r\n\t<body $id = \"slbg\">\r\n\t\t<center>\r\n\t\t\t<div class=\"infiniteCarousel\">\r\n\t\t\t  <div class=\"wrapper\">\r\n\t\t\t\t<ul>\r\n\t\t\t\t\t";
    echo $LTC;
    echo "\t\t\t\t</ul>        \r\n\t\t\t  </div>\r\n\t\t\t</div>\r\n\t\t</center>\r\n\t</body>\r\n</html>";
} else {
    $albumid = intval(TS_Global("albumid"));
    if (!$albumid) {
        exit($lang->global("permission"));
    }
    $global_image_path = "ts_albums/";
    $album_image_path = $global_image_path . "album_images/";
    $album_thumbnail_path = $global_image_path . "album_thumbnails/";
    $lang->load("ts_albums");
    $TSSEConfig->TSLoadConfig("ALBUM");
    if (!$is_mod && $album_disabled == "yes") {
        exit($lang->ts_albums["disabled"]);
    }
    if (($album_can_view_usergroups = explode(",", $album_can_view_usergroups)) && !in_array($CURUSER["usergroup"], $album_can_view_usergroups)) {
        exit($lang->global("permission"));
    }
    $query = sql_query("SELECT userid, title, description, private FROM ts_albums WHERE $albumid = '" . $albumid . "'");
    exit($lang->global["dberror"]);
}

?>