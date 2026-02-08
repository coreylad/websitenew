<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$RankCache = [];
$RQuery = sql_query("SELECT * FROM ts_ranks");
while ($Rank = mysqli_fetch_assoc($RQuery)) {
    $RankCache[] = $Rank;
}
function user_rank($userinfo)
{
    global $BASEURL;
    global $RankCache;
    $Image = "<img $src = \"" . $BASEURL . "/tsf_forums/images/ranks/rank_0.gif\" $border = \"0\" $alt = \"\" $title = \"\" />";
    foreach ($RankCache as $rank) {
        if ($rank["usergroup"] == 0 && $rank["minposts"] <= $userinfo["totalposts"] && $rank["displaytype"] == 1 || 0 < $rank["usergroup"] && $rank["minposts"] <= $userinfo["totalposts"] && $rank["displaytype"] == 1 || 0 < $rank["usergroup"] && $rank["minposts"] <= $userinfo["totalposts"] && $rank["displaytype"] == 2 && $rank["usergroup"] == $userinfo["usergroup"]) {
            $Image = "<img $src = \"" . $BASEURL . "/" . $rank["image"] . "\" $border = \"0\" $alt = \"\" $title = \"\" />";
        }
    }
    return $Image;
}

?>