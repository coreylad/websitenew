<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function clean_keywords_ft($keywords)
{
    if (!$keywords) {
        return false;
    }
    $keywords = strtolower($keywords);
    $keywords = str_replace("%", "\\%", $keywords);
    $keywords = preg_replace("#\\*{2,}#s", "*", $keywords);
    $keywords = preg_replace("#([\\[\\]\\|\\.\\,:])#s", " ", $keywords);
    $keywords = preg_replace("#\\s+#s", " ", $keywords);
    if (strpos($keywords, "\"") !== false) {
        $inquote = false;
        $keywords = explode("\"", $keywords);
        foreach ($keywords as $phrase) {
            if ($phrase != "") {
                if ($inquote) {
                    $words[] = "\"" . trim($phrase) . "\"";
                } else {
                    $split_words = preg_split("#\\s{1,}#", $phrase, -1);
                    if (!is_array($split_words)) {
                        return false;
                    }
                    foreach ($split_words as $word) {
                        if (!$word) {
                            return false;
                        }
                        $words[] = trim($word);
                    }
                }
            }
            $inquote = !$inquote;
        }
    } else {
        $split_words = preg_split("#\\s{1,}#", $keywords, -1);
        if (!is_array($split_words)) {
            return false;
        }
        foreach ($split_words as $word) {
            if (!$word) {
                return false;
            }
            $words[] = trim($word);
        }
    }
    $keywords = "";
    $boolean = "";
    foreach ($words as $word) {
        if ($word == "or") {
            $boolean = "";
        } else {
            if ($word == "and") {
                $boolean = "+";
            } else {
                if ($word == "not") {
                    $boolean = "-";
                } else {
                    $keywords .= " " . $boolean . $word;
                    $boolean = "";
                }
            }
        }
    }
    $keywords = "+" . trim($keywords);
    return $keywords;
}

?>