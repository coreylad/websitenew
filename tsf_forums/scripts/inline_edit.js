var inlineEditor = Class.create();

function HTMLchars(A)
{
    A = A.replace(new RegExp("&(?!#[0-9]+;)", "g"), "&amp;");
    A = A.replace(/</g, "&lt;");
    A = A.replace(/>/g, "&gt;");
    A = A.replace(/"/g, "&quot;");
    A = A.replace(/  /g, "&nbsp;&nbsp;");
    return A
}

function unHTMLchars(A)
{
    A = A.replace(/&lt;/g, "<");
    A = A.replace(/&gt;/g, ">");
    A = A.replace(/&nbsp;/g, " ");
    A = A.replace(/&quot;/g, '"');
    A = A.replace(/&amp;/g, "&");
    return A
}
var DomLib = {
    addClass: function (B, A)
    {
        if (B)
        {
            if (B.className != "")
            {
                B.className += " " + A
            }
            else
            {
                B.className = A
            }
        }
    },
    removeClass: function (B, A)
    {
        if (B.className == B.className.replace(" ", "-"))
        {
            B.className = B.className.replace(A, "")
        }
        else
        {
            B.className = B.className.replace(" " + A, "")
        }
    },
    getElementsByClassName: function (B, F, A)
    {
        var E = (F == "*" && document.all) ? document.all : B.getElementsByTagName(F);
        var H = new Array();
        A = A.replace(/\-/g, "\\-");
        var G = new RegExp("(^|\\s)" + A + "(\\s|$)");
        var D;
        for (var C = 0; C < E.length; C++)
        {
            D = E[C];
            if (G.test(D.className))
            {
                H.push(D)
            }
        }
        return (H)
    },
    getPageScroll: function ()
    {
        var A;
        if (self.pageYOffset)
        {
            A = self.pageYOffset
        }
        else
        {
            if (document.documentElement && document.documentElement.scrollTop)
            {
                A = document.documentElement.scrollTop
            }
            else
            {
                if (document.body)
                {
                    A = document.body.scrollTop
                }
            }
        }
        arrayPageScroll = new Array("", A);
        return arrayPageScroll
    },
    getPageSize: function ()
    {
        var F, A;
        if (window.innerHeight && window.scrollMaxY)
        {
            F = document.body.scrollWidth;
            A = window.innerHeight + window.scrollMaxY
        }
        else
        {
            if (document.body.scrollHeight > document.body.offsetHeight)
            {
                F = document.body.scrollWidth;
                A = document.body.scrollHeight
            }
            else
            {
                F = document.body.offsetWidth;
                A = document.body.offsetHeight
            }
        }
        var D, G;
        if (self.innerHeight)
        {
            D = self.innerWidth;
            G = self.innerHeight
        }
        else
        {
            if (document.documentElement && document.documentElement.clientHeight)
            {
                D = document.documentElement.clientWidth;
                G = document.documentElement.clientHeight
            }
            else
            {
                if (document.body)
                {
                    D = document.body.clientWidth;
                    G = document.body.clientHeight
                }
            }
        }
        var E, B;
        if (A < G)
        {
            E = G
        }
        else
        {
            E = A
        } if (F < D)
        {
            B = D
        }
        else
        {
            B = F
        }
        var C = new Array(B, E, D, G);
        return C
    }
};
inlineEditor.prototype = {
    initialize: function (B, A)
    {
        this.url = B;
        this.elements = new Array();
        this.currentElement = "";
        this.options = A;
        if (!A.className)
        {
            alert("You need to specify a className in the options.");
            return false
        }
        this.className = A.className;
        if (A.spinnerImage)
        {
            this.spinnerImage = A.spinnerImage
        }
        this.elements = DomLib.getElementsByClassName(document, "*", A.className);
        if (this.elements)
        {
            for (var C = 0; C < this.elements.length; C++)
            {
                if (this.elements[C].id)
                {
                    this.makeEditable(this.elements[C])
                }
            }
        }
        return true
    },
    makeEditable: function (A)
    {
        if (A.title != "")
        {
            A.title = A.title + " "
        }
        if (!this.options.lang_click_edit)
        {
            this.options.lang_click_edit = "(Click and hold to edit)"
        }
        A.title = A.title + this.options.lang_click_edit;
        A.onmousedown = this.onMouseDown.bindAsEventListener(this);
        return true
    },
    onMouseDown: function (B)
    {
        var A = Event.element(B);
        Event.stop(B);
        if (this.currentElement != "")
        {
            return false
        }
        if (typeof (A.id) == "undefined" && typeof (A.parentNode.id) != "undefined")
        {
            A.id = A.parentNode.id
        }
        this.currentElement = A.id;
        this.timeout = setTimeout(this.showTextbox.bind(this), 1200);
        document.onmouseup = this.onMouseUp.bindAsEventListener(this);
        return false
    },
    onMouseUp: function (A)
    {
        clearTimeout(this.timeout);
        Event.stop(A);
        return false
    },
    onButtonClick: function (A)
    {
        if ($(A))
        {
            this.currentElement = A;
            this.showTextbox()
        }
        return false
    },
    showTextbox: function ()
    {
        this.element = $(this.currentElement);
        if (typeof (this.element.parentNode) == "undefined" || typeof (this.element.id) == "undefined")
        {
            return false
        }
        this.oldValue = this.element.innerHTML;
        this.testNode = this.element.parentNode;
        if (!this.testNode)
        {
            return false
        }
        this.cache = this.testNode.innerHTML;
        this.textbox = document.createElement("input");
        this.textbox.style.width = "95%";
        this.textbox.maxlength = "85";
        this.textbox.className = "textbox";
        this.textbox.type = "text";
        Event.observe(this.textbox, "blur", this.onBlur.bindAsEventListener(this));
        Event.observe(this.textbox, "keypress", this.onKeyUp.bindAsEventListener(this));
        this.textbox.setAttribute("autocomplete", "off");
        this.textbox.name = "value";
        this.textbox.index = this.element.index;
        this.textbox.value = unHTMLchars(this.oldValue);
        Element.remove(this.element);
        this.testNode.innerHTML = "";
        this.testNode.appendChild(this.textbox);
        this.textbox.focus();
        return true
    },
    onBlur: function (A)
    {
        this.hideTextbox();
        return true
    },
    onKeyUp: function (A)
    {
        if (A.keyCode == Event.KEY_RETURN)
        {
            this.hideTextbox()
        }
        else
        {
            if (A.keyCode == Event.KEY_ESC)
            {
                this.cancelEdit()
            }
        }
        return true
    },
    onSubmit: function (A)
    {
        this.hideTextbox();
        return true
    },
    hideTextbox: function ()
    {
        Event.stopObserving(this.textbox, "blur", this.onBlur.bindAsEventListener(this));
        var A = this.textbox.value;
        if (typeof (A) != "undefined" && A != "" && HTMLchars(A) != this.oldValue)
        {
            this.testNode.innerHTML = this.cache;
            this.element = $(this.currentElement);
            this.element.innerHTML = A;
            this.element.onmousedown = this.onMouseDown.bindAsEventListener(this);
            this.lastElement = this.currentElement;
            postData = "value=" + encodeURIComponent(A);
            if (this.spinnerImage)
            {
                this.showSpinner()
            }
            idInfo = this.element.id.split("_");
            if (idInfo[0] && idInfo[1])
            {
                postData = postData + "&" + idInfo[0] + "=" + idInfo[1]
            }
            new ajax(this.url,
            {
                method: "post",
                postBody: postData,
                onComplete: this.onComplete.bind(this)
            })
        }
        else
        {
            Element.remove(this.textbox);
            this.testNode.innerHTML = this.cache;
            this.element = $(this.currentElement);
            this.element.onmousedown = this.onMouseDown.bindAsEventListener(this)
        }
        this.currentElement = "";
        return true
    },
    cancelEdit: function ()
    {
        Element.remove(this.textbox);
        this.testNode.innerHTML = this.cache;
        this.element = $(this.currentElement);
        this.element.onmousedown = this.onMouseDown.bindAsEventListener(this);
        this.currentCurrentElement = ""
    },
    onComplete: function (A)
    {
        if (A.responseText.match(/<error>(.*)<\/error>/))
        {
            message = A.responseText.match(/<error>(.*)<\/error>/);
            this.element.innerHTML = this.oldValue;
            if (!message[1])
            {
                message[1] = "An unknown error occurred."
            }
            alert("There was an error performing the update.\n\n" + message[1])
        }
        else
        {
            if (A.responseText)
            {
                this.element.innerHTML = HTMLchars(A.responseText)
            }
        } if (this.spinnerImage)
        {
            this.hideSpinner()
        }
        this.currentIndex = -1;
        return true
    },
    showSpinner: function ()
    {
        if (!this.spinnerImage)
        {
            return false
        }
        if (!this.spinner)
        {
            this.spinner = document.createElement("img");
            this.spinner.src = this.spinnerImage;
            if (saving_changes)
            {
                this.spinner.alt = saving_changes
            }
            else
            {
                this.spinner.alt = "Saving changes.."
            }
            this.spinner.style.verticalAlign = "middle";
            this.spinner.style.paddingRight = "3px"
        }
        this.testNode.insertBefore(this.spinner, this.testNode.firstChild);
        return true
    },
    hideSpinner: function ()
    {
        if (!this.spinnerImage)
        {
            return false
        }
        Element.remove(this.spinner);
        return true
    }
};