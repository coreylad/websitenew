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
function TSFajaxquickthanks(D, C, A) {
    var B = "action=thanks&tid=" + intval(D) + "&pid=" + intval(C);
    if (A) {
        B += "&removethanks=true"
    }
    new Ajax.Request(baseurl + "/tsf_forums/tsf_ajax.php", {
        parameters: B,
        method: "POST",
        contentType: "application/x-www-form-urlencoded",
        encoding: charset,
        onLoading: function () {
            Element.show("loading-layerT")
        },
        onSuccess: function (F) {
            var E = F.responseText;
            if (E.match(/<error>(.*)<\/error>/)) {
                message = E.match(/<error>(.*)<\/error>/);
                if (!message[1]) {
                    message[1] = l_ajaxerror
                }
                alert(l_updateerror + message[1])
            } else {
					if (E == "")
					{
						$("thanks_zone_" + C).hide();
					}
					else
					{
						$("thanks_zone_" + C).show();
					}
                
                $("show_thanks_" + C).innerHTML = E;
                if (A) {
                    Element.hide("remove_thanks_button_" + C);
                    Element.show("thanks_button_" + C)
                } else {
                    Element.hide("thanks_button_" + C);
                    Element.show("remove_thanks_button_" + C)
                }
            }
            Element.hide("loading-layerT")
        },
        onException: function (F, E) {
            alert(l_ajaxerror + "\n\n" + E);
            Element.hide("loading-layerT")
        },
        onFailure: function () {
            alert(l_ajaxerror);
            Element.hide("loading-layerT")
        }
    })
};