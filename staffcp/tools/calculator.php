<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/calculator.lang");
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction calc(A){$gb = document.sizes.gb.value;$mb = document.sizes.mb.value;$kb = document.sizes.kb.value;$b = document.sizes.Bytee.value;if(A==\"gb\"){document.sizes.mb.$value = gb;document.sizes.mb.value*=\"1024\";document.sizes.kb.$value = gb;document.sizes.kb.value*=\"1024\";document.sizes.kb.value*=\"1024\";document.sizes.Bytee.$value = gb;document.sizes.Bytee.value*=\"1024\";document.sizes.Bytee.value*=\"1024\";document.sizes.Bytee.value*=\"1024\";}else{if(A==\"mb\"){document.sizes.gb.$value = mb;document.sizes.gb.value/=\"1024\";document.sizes.kb.$value = mb;document.sizes.kb.value*=\"1024\";document.sizes.Bytee.$value = mb;document.sizes.Bytee.value*=\"1024\";document.sizes.Bytee.value*=\"1024\";}else{if(A==\"kb\"){document.sizes.gb.$value = kb;document.sizes.gb.value/=\"1024\";document.sizes.gb.value/=\"1024\";document.sizes.mb.$value = kb;document.sizes.mb.value/=\"1024\";document.sizes.Bytee.$value = kb;document.sizes.Bytee.value*=\"1024\";}else{if(A==\"Bytee\"){document.sizes.gb.$value = b;document.sizes.gb.value/=\"1024\";document.sizes.gb.value/=\"1024\";document.sizes.gb.value/=\"1024\";document.sizes.mb.$value = b;document.sizes.mb.value/=\"1024\";document.sizes.mb.value/=\"1024\";document.sizes.kb.$value = b;document.sizes.kb.value/=\"1024\";}}}}}\r\n</script>\r\n<form $name = \"sizes\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[1] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t\t<table $border = \"0\" $width = \"100%\" $cellspacing = \"5\" $cellpadding = \"2\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"6%\" $align = \"right\">GB&nbsp;</td>\r\n\t\t\t\t\t<td $width = \"20%\">&nbsp<input $type = \"text\" $name = \"gb\" $size = \"20\" /></td>\r\n\t\t\t\t\t<td $width = \"44%\">&nbsp<input $onclick = \"javascript:calc('gb')\" $type = \"button\" $value = \"" . $Language[2] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"6%\" $align = \"right\">MB&nbsp;</td>\r\n\t\t\t\t\t<td $width = \"20%\">&nbsp;<input $type = \"text\" $name = \"mb\" $size = \"20\" /></td>\r\n\t\t\t\t\t<td $width = \"44%\">&nbsp;<input $onclick = \"javascript:calc('mb')\" $type = \"button\" $value = \"" . $Language[3] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"6%\" $align = \"right\">KB&nbsp;</td>\r\n\t\t\t\t\t<td $width = \"20%\">&nbsp;<input $type = \"text\" $name = \"kb\" $size = \"20\" /></td>\r\n\t\t\t\t\t<td $width = \"44%\">&nbsp;<input $onclick = \"javascript:calc('kb')\" $type = \"button\" $value = \"" . $Language[4] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"6%\" $align = \"right\">Byte&nbsp;</td>\r\n\t\t\t\t\t<td $width = \"20%\">&nbsp;<input $type = \"text\" $name = \"Bytee\" $size = \"20\" /></td>\r\n\t\t\t\t\t<td $width = \"44%\">&nbsp;<input $onclick = \"javascript:calc('Bytee')\" $type = \"button\" $value = \"" . $Language[5] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

?>