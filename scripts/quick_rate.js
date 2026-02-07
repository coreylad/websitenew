function intval(B, C) {
    var A;
    if (typeof(B) == "string") {
        A = parseInt(B * 1);
        if (isNaN(A) || !isFinite(A)) {
            return 0
        } else {
            return A.toString(C || 10)
        }
    } else {
        if (typeof(B) == "number" && isFinite(B)) {
            return Math.floor(B)
        } else {
            return 0
        }
    }
}
function TSQuickRate(C, A) {
    var D = intval($("score").value);
    if (D >= 1 && D <= 10) {} else {
        alert(l_spacenotallowed);
        return false
    }
	var EC=$("tsrating_results").innerHTML;
    $("tsrating_results").innerHTML = '<img src="' + themedir + 'images/loading.gif" alt="' + l_pleasewait + '" title="' + l_pleasewait + '" style="vertical-align: middle;" /> ' + l_pleasewait;
    var B = "ratingid=" + C + "&userid=" + A + "&score=" + D + "&securitytoken= " + securitytoken;
    new Ajax.Request(baseurl + "/ts_rate.php", {
        parameters: B,
        method: "POST",
        contentType: "application/x-www-form-urlencoded",
        encoding: charset,
        onSuccess: function (F) {
            var E = F.responseText;
            if (E.match(/<error>(.*)<\/error>/)) {
                message = E.match(/<error>(.*)<\/error>/);
                if (!message[1]) {
                    message[1] = l_ajaxerror
                }
                alert(l_updateerror + message[1]);
				$("tsrating_results").innerHTML = EC;
            } else {
                $("tsrating_results").innerHTML = E;
            }
        },
        onFailure: function () {
            alert(l_ajaxerror)
        }
    })
};