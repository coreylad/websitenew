function check(A) {
    if (checkflag == "false") {
        for (i = 0; i < A.length; i++) {
            A[i].checked = true
        }
        checkflag = "true";
        return l_uncheckall
    } else {
        for (i = 0; i < A.length; i++) {
            A[i].checked = false
        }
        checkflag = "false";
        return l_checkall
    }
}
function log_out() {
    ht = document.getElementsByTagName("html");
    ht[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
    if (confirm(l_logout)) {
        return true
    } else {
        ht[0].style.filter = "";
        return false
    }
}
function jumpto(A, B) {
    if (typeof B != "undefined" && TSGetID("jumpto")) {
        TSGetID("jumpto").style.display = "block"
    }
    window.location = A
}
function highlight(A) {
    A.focus();
    A.select()
}
function select_deselectAll(B, D, C) {
    var A = document.forms[B];
    for (i = 0; i < A.length; i++) {
        if (D.attributes.checkall != null && D.attributes.checkall.value == C) {
            if (A.elements[i].attributes.checkme != null && A.elements[i].attributes.checkme.value == C) {
                A.elements[i].checked = D.checked
            }
        } else {
            if (A.elements[i].attributes.checkme != null && A.elements[i].attributes.checkme.value == C) {
                if (A.elements[i].checked == false) {
                    A.elements[1].checked = false
                }
            }
        }
    }
}
function ts_show(A) {
    TSGetID(A).style.display = "block"
}
function ts_hide(A) {
    TSGetID(A).style.display = "none"
}
function TSGetID(A) {
    if (document.getElementById) {
        return document.getElementById(A)
    } else {
        if (document.all) {
            return document.all[A]
        } else {
            if (document.layers) {
                return document.layers[A]
            } else {
                return null
            }
        }
    }
}
function TSGoToPage(B, A) {
    if (TSGetID("Page_Number") && (pagenum = parseInt(TSGetID("Page_Number").value, 10)) > 0) {
        window.location = B + "page=" + pagenum + (A?A:"")
    } else {
        if (TSGetID("Page_Number2") && (pagenum = parseInt(TSGetID("Page_Number2").value, 10)) > 0) {
            window.location = B + "page=" + pagenum + (A?A:"")
        }
    }
    return false
}

function TSOpenPopup(B, D, A, C) {
    if (!A) {
        A = screen.width * (3 / 4)
    }
    if (!C) {
        C = screen.height * (3 / 4)
    }
    l = (screen.width - A) / 2;
    t = (screen.height - C) / 2;
    widthHeight = "width=" + A + ",height=" + C + ",left=" + l + ",top=" + t + ",menubar=no,resizable=no,scrollbars=yes,status=no,toolbar=no,location=no";
    window.open(B, D, widthHeight);
    return true
}

function urlencode( str )
{
	return encodeURIComponent(str);
}

function parseQuote(Quote, textareaID, tID, pID)
{
	var textarea = jQuery('#'+textareaID), currentMessage = textarea.val();
	
	if(currentMessage)
	{
		Quote = "\n\n"+Quote;
	}
	
	textarea.val(currentMessage+Quote);
	
	bookmarkscroll.scrollTo(textareaID);
	textarea.focus();
}

function TSResizeImage(img, ImageHash)
{
	var maxheight = 600, maxwidth = 700, IsResized = false, w = ow = parseInt( img.width ), h = oh =  parseInt( img.height );
	
	if ( w > maxwidth )
	{
		img.style.cursor = "pointer";
		img.onclick = function( )
		{
			jQuery(img).colorbox({href: this.src, open: true, photo: true, fixed: true, maxWidth: '90%', maxHeight: '90%'});
		};
		h = ( maxwidth / w ) * h;
		w = maxwidth;
		img.height = h;
		img.width = w;
		IsResized = true;
	}

	if ( h > maxheight )
	{
		img.style.cursor="pointer";
		img.onclick = function( )
		{ 
			jQuery(img).colorbox({href: this.src, open: true, photo: true, fixed: true, maxWidth: '90%', maxHeight: '90%'});
		};
		img.width = ( maxheight / h ) * w;
		img.height = maxheight;
		IsResized = true;
	}

	if (IsResized && !ImageHashes[ImageHash])
	{
		jQuery("#"+ImageHash).append('<div style="width: '+maxwidth+'px; padding-top: 3px; padding-bottom: 3px;" class="highlight">&nbsp;'+lang_resized+' '+ow+' x '+oh+'</div>');
		ImageHashes[ImageHash] = 1;
	}
}

var ImageHashes = new Array,quotedPosts = new Array,checkflag = "false";
window.status = 'Powered by TS Special Edition v.8.0';

jQuery(document).ready(function()
{
	jQuery(document).on('click', 'a[href="#goshowcontents"]', function(e)
	{
		e.preventDefault();
		return false;
	});

	var cache = {}, lastXhr;

	if(jQuery('input[rel="autoCompleteUsers"]').length)
	{
		jQuery('input[rel="autoCompleteUsers"]').autocomplete//For Member Search
		({
			minLength: 1,
			source: function( request, response )
			{
				var term = request.term;
				if( term in cache )
				{
					response( cache[ term ] );
					return;
				}

				lastXhr = $.ajax
				({
					type: 'POST',
					url: baseurl+'/search-user.php',
					dataType: 'json',
					data: 'username='+term,
					success: function(serverResponse, status, xhr)
					{
						cache[ term ] = serverResponse;
						if( xhr === lastXhr )
						{
							response( serverResponse );
						}
					}
				});
			}
		}).attr('autocomplete', 'off');
	}
});