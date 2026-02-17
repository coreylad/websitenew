<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Language = loadStaffLanguage('ip_info');
$Message = "";
$ip = isset($_GET["ip"]) ? trim(urldecode($_GET["ip"])) : "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
        exit;
    }
    
    $ip = trim($_POST["ip"] ?? "");
    if ($ip) {
        $ipdata = new Class_33($ip);
        $Message = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"8\"><b>" . escape_html($Language[13]) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[3]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[6]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[10]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[11]) . "</td>\r\n\t\t\t\t<td class=\"alt2\">" . escape_html($Language[12]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->ip) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->country) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->host) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->netname) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->person) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->address) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->phone) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($ipdata->email) . "</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t";
    } else {
        $Message = showAlertErrorModern($Language[5]);
    }
}
echo "<form $action = \"";
echo escape_attr($_SERVER["SCRIPT_NAME"]);
echo "?do=ip_info\" $method = \"post\" $name = \"ip_info\">\r\n";
echo getFormTokenField();
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo escape_html($Language[2]);
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo escape_html($Language[3]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ip\" $value = \"";
echo escape_attr($ip);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\t\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo escape_attr($Language[2]);
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo escape_attr($Language[4]);
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";

class Class_33
{
    public $ip = NULL;
    public $host = NULL;
    public $netname = NULL;
    public $country = NULL;
    public $person = NULL;
    public $address = NULL;
    public $phone = NULL;
    public $email = NULL;
    public $msg = NULL;
    public $server = NULL;
    public function __construct($addr = "194.44.39.135")
    {
        $extra = "";
        $msg = "";
        $buffer = "";
        $this->ip = $addr;
        $this->host = gethostbyaddr($this->ip);
        if (!$this->server) {
            $this->server = "whois.arin.net";
        }
        if ($this->ip !== gethostbyname($this->host)) {
            $msg .= "Can't IP Whois without an IP address.";
        } else {
            if (!($sock = fsockopen($this->server, 43, $num, $error, 20))) {
                unset($sock);
                $msg .= "Timed-out connecting to " . $this->server . " (port 43)";
            } else {
                fputs($sock, $this->ip . "\n");
                while (!feof($sock)) {
                    $buffer .= fgets($sock, 10240);
                }
                fclose($sock);
            }
            if (preg_match("@RIPE.NET@is", $buffer)) {
                $nextServer = "whois.ripe.net";
            } else {
                if (preg_match("@whois.apnic.net@is", $buffer)) {
                    $nextServer = "whois.apnic.net";
                } else {
                    if (preg_match("@nic.ad.jp@is", $buffer)) {
                        $nextServer = "whois.nic.ad.jp";
                        $extra = "/e";
                    } else {
                        if (preg_match("@whois.registro.br@is", $buffer)) {
                            $nextServer = "whois.registro.br";
                        }
                    }
                }
            }
            if (isset($nextServer) && $nextServer) {
                $buffer = "";
                if (!($sock = fsockopen($nextServer, 43, $num, $error, 10))) {
                    unset($sock);
                    $msg .= "Timed-out connecting to " . $nextServer . " (port 43)";
                } else {
                    fputs($sock, $this->ip . $extra . "\n");
                    while (!feof($sock)) {
                        $buffer .= fgets($sock, 10240);
                    }
                    fclose($sock);
                }
            }
            $msg .= nl2br($buffer);
        }
        $msg .= "</blockquote></p>";
        $this->msg = str_replace(" ", "&nbsp;", $msg);
        $tmparr = explode("<br />", $msg);
        foreach ($tmparr as $value) {
            $tmpvalue = explode(":", $value);
            $key = trim($tmpvalue[0]);
            $znach = isset($tmpvalue[1]) && $tmpvalue[1] !== "" ? trim($tmpvalue[1]) : "--";
            if ($key === "country") {
                $this->country = $znach;
            } else {
                if ($key === "netname") {
                    $this->netname = $znach;
                } else {
                    if ($key === "person") {
                        $this->person .= $znach . " ";
                    } else {
                        if ($key === "address") {
                            $this->address .= $znach . " ";
                        } else {
                            if ($key === "phone") {
                                $this->phone = $znach;
                            } else {
                                if ($key === "e-mail") {
                                    $this->email = $znach;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
