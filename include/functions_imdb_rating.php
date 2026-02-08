<?php
function TSSEGetIMDBRatingImage($Content = "")
{
    global $BASEURL;
    global $pic_base_url;
    if (preg_match("@<b>User Rating:<\\/b> (.*)\\/10@U", $Content, $IMDBRating)) {
        if (!isset($IMDBRating[1]) || !isset($IMDBRating[1][0]) || !$IMDBRating[1][0]) {
            return NULL;
        }
        $SecondLetter = "";
        if (isset($IMDBRating[1][2]) && $IMDBRating[1][2]) {
            switch ($IMDBRating[1][2]) {
                case 0:
                case 1:
                case 2:
                    $SecondLetter = "";
                    break;
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                    $SecondLetter = ".5";
                    break;
            }
        }
        $FirstLetter = $IMDBRating[1][0];
        if ($FirstLetter) {
            $IMDBRatingImage = "<img $src = \"" . $pic_base_url . "imdb_rating/" . $FirstLetter . $SecondLetter . "-10.png\" $border = \"0\" $alt = \"" . $IMDBRating[1] . "/10" . "\" $title = \"" . $IMDBRating[1] . "/10" . "\" class=\"inlineimg\" />";
            return ["image" => $IMDBRatingImage, "rating" => $IMDBRating[1] . "/10"];
        }
    }
}

?>