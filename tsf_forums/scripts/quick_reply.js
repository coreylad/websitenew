function intval(B, C)
{
    var A;
    if (typeof (B) == "string")
    {
        A = parseInt(B * 1);
        if (isNaN(A) || !isFinite(A))
        {
            return 0
        }
        else
        {
            return A.toString(C || 10)
        }
    }
    else
    {
        if (typeof (B) == "number" && isFinite(B))
        {
            return Math.floor(B)
        }
        else
        {
            return 0
        }
    }
}

function TSajaxquickreply(G, C, E)
{
    var D = $("message").value;
    var A = "0";
    var F = "0";
    if ($("quickreply").closethread.checked == true)
    {
        var A = "1"
    }
    if ($("quickreply").stickthread.checked == true)
    {
        var F = "1"
    }
    var B = "ajax_quick_reply=1&closethread=" + A + "&stickthread=" + F + "&tid=" + intval(G) + "&postcount=" + intval(C) + "&message=" + urlencode(D) + "&page=" + intval(E);
    new Ajax.Request(baseurl + "/ts_ajax.php",
    {
        parameters: B,
        method: "POST",
        contentType: "application/x-www-form-urlencoded",
        encoding: charset,
        onLoading: function ()
        {
            Element.show("loading-layerS");
            $("quickreply").quickreplybutton.value = l_pleasewait;
            $("quickreply").quickreplybutton.disabled = true
        },
        onSuccess: function (I)
        {
            var H = I.responseText;
            if (H.match(/<error>(.*)<\/error>/))
            {
                D = H.match(/<error>(.*)<\/error>/);
                if (!D[1])
                {
                    D[1] = l_ajaxerror
                }
                alert(l_updateerror + D[1])
            }
            else
            {
                var J = document.createElement("div");
                J.setAttribute("id", "PostedReply");
                J.innerHTML = H;
                $("ajax_quick_reply").appendChild(J);
                $("message").value = ""
            }
            Element.hide("loading-layerS");
            $("quickreply").quickreplybutton.value = l_newreply;
            $("quickreply").quickreplybutton.disabled = false
        },
        onException: function (I, H)
        {
            alert(l_ajaxerror + "\n\n" + H);
            Element.hide("loading-layerS");
            $("quickreply").quickreplybutton.value = l_newreply;
            $("quickreply").quickreplybutton.disabled = false
        },
        onFailure: function ()
        {
            alert(l_ajaxerror);
            Element.hide("loading-layerS");
            $("quickreply").quickreplybutton.value = l_newreply;
            $("quickreply").quickreplybutton.disabled = false
        }
    })
};