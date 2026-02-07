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
function TSajaxquickvm(A, D) {
    var C = $("message").value;
    var B = "what=save_vmsg&userid=" + intval(A) + "&message=" + urlencode(C) + "&securitytoken=" + securitytoken;
    if (D) {
        B = B + "&isupdate=" + D
    }
    new Ajax.Request(baseurl + "/ts_ajax2.php", {
        parameters: B,
        method: "POST",
        contentType: "application/x-www-form-urlencoded",
        encoding: charset,
        onLoading: function () {
            Element.show("loading-layer");
            $("quickreply").submitvm.disabled = true
        },
        onSuccess: function (F) {
            var E = F.responseText;
            if (E.match(/<error>(.*)<\/error>/)) {
                C = E.match(/<error>(.*)<\/error>/);
                if (!C[1]) {
                    C[1] = l_ajaxerror
                }
                alert(l_updateerror + C[1])
            } else {
                if (D && $("ShowVisitorMessage" + D)) {
                    $("ShowVisitorMessage" + D).innerHTML = E
                } else {
                    Element.show("PostedQuickVisitorMessages");
                    $("PostedQuickVisitorMessages").innerHTML = E
                }
                $("message").value = ""
            }
            Element.hide("loading-layer");
            $("quickreply").submitvm.disabled = false
        },
        onException: function (F, E) {
            alert(l_ajaxerror + "\n\n" + E);
            Element.hide("loading-layer");
            $("quickreply").submitvm.disabled = false
        },
        onFailure: function () {
            alert(l_ajaxerror);
            Element.hide("loading-layer");
            $("quickreply").submitvm.disabled = false
        }
    })
};