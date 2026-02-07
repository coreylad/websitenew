/**
* editor_plugin.js
*
* Copyright 2009, Moxiecode Systems AB
* Released under LGPL License.
*
* License: http://tinymce.moxiecode.com/license
* Contributing: http://tinymce.moxiecode.com/contributing
*/

(function()
{
	tinymce.create('tinymce.plugins.BBCodePlugin',
	{
		init : function(ed, url)
		{
			var t = this, dialect = ed.getParam('bbcode_dialect', 'tsse').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o)
			{
				if(o.content)
					o.content = t[dialect + '_BBCODEtoHTML'](o.content);
			});

			ed.onPostProcess.add(function(ed, o)
			{
				if (o.set)
					o.content = t[dialect + '_BBCODEtoHTML'](o.content);

				if (o.get)
					o.content = t[dialect + '_HTMLtoBBCODE'](o.content);
			});
		},

		getInfo : function(){
			return{
				longname : 'BBCode Plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// HTML -> BBCode
		tsse_HTMLtoBBCODE : function(s)
		{
			s = tinymce.trim(s);

			if(!s)
			{
				return s;
			}

			function rep(re, str)
			{
				s = s.replace(re, str);
			};

			// lists
			rep(/<ul>/gi,"[list]");
			rep(/<\/ul>/gi,"[/list]");

			rep(/<ol>/gi,"[list=ol]");
			rep(/<\/ol>/gi,"[/list]");
			
			rep(/<li>(.*?)<\/li>/gi,"[*]$1");

			// url
			rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");

			//color
			rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");

			// font size
			rep(/<span style=\"font-size: (.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");

			//font family
			rep(/<span style=\"font-family: ?(.*?);\">(.*?)<\/span>/gi,"[font=$1]$2[/font]");

			//img
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");

			//align
			rep(/<p style=\"text-align: ?(.*?);\">(.*?)<\/p>/gi,"[align=$1]$2[/align]");

			//strike
			rep(/<span style=\"text-decoration: ?line-through;\">(.*?)<\/span>/gi,"[s]$1[/s]");

			//underline
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<u>/gi,"[u]");

			//Bold
			rep(/<span style=\"font-weight: bold;\">(.*?)<\/span>/gi, "[b]$1[/b]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");

			//em
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");

			//quote
			rep(/<blockquote[^>]*>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");

			//BR
			rep(/(<br[\s]?[\/]?>[\s]*){3,}/ig, "<br /><br />");
			rep(/<br[\s]?[\/]?>/gi, "\n");
			rep(/<p>/gi,"");
			rep(/<\/p>/gi,"\n");

			//remove any other html codes
			rep(/<div.*?>(.*?)<\/div>/gi,"$1");
			rep(/<table.*?>(.*?)<\/table>/gi,"$1");
			rep(/<tbody.*?>(.*?)<\/tbody>/gi,"$1");
			rep(/<tr.*?>(.*?)<\/tr>/gi,"$1");
			rep(/<td.*?>(.*?)<\/td>/gi,"$1");

			return s;
		},

		// BBCode -> HTML
		tsse_BBCODEtoHTML : function(s)
		{
			s = tinymce.trim(s);

			if(!s)
			{
				return s;
			}

			function rep(re, str)
			{
				s = s.replace(re, str);
			};

			function parse_list()
			{
				var liTags = arguments[1];
				var ulHTML = '<ul>';

				if(!liTags)
				{
					return;
				}

				liTags = liTags.split("[*]");

				if(liTags.length < 1)
				{
					return;
				}

				for(i=1; i<liTags.length;i++)
				{
					ulHTML += '<li>'+liTags[i]+'</li>';
				}

				ulHTML += '</ul>';
				return ulHTML;
			}

			function parse_list_ol()
			{
				var liTags = arguments[1];
				var olHTML = '<ol>';

				if(!liTags)
				{
					return;
				}

				liTags = liTags.split("[*]");

				if(liTags.length < 1)
				{
					return;
				}

				for(i=1; i<liTags.length;i++)
				{
					olHTML += '<li>'+liTags[i]+'</li>';
				}

				olHTML += '</ol>';
				return olHTML;
			}

			//Replace new lines with a br
			rep(/\n/gi, "<br />");
			rep(/<br[\s]?[\/]?>/gi, "<br />");
			rep(/(<br[\s]?[\/]?>[\s]*){3,}/ig, "<br /><br />");

			// LIST UL
			rep(/\[list\](.*?)\[\/list\]/gi, parse_list);

			// LIST OL
			rep(/\[list=ol\](.*?)\[\/list\]/gi, parse_list_ol);

			//align
			rep(/\[align=(.*?)\](.*?)\[\/align\]/gi,"<p style=\"text-align: $1\">$2</p>");

			// strong/b
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");

			//em/i
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");

			//underline
			rep(/\[u\](.*?)\[\/u\]/gi,"<span style=\"text-decoration: underline;\">$1</span>");

			//strike
			rep(/\[s\](.*?)\[\/s\]/gi,"<span style=\"text-decoration: line-through;\">$1</span>");

			//color
			rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<span style=\"color: $1\">$2</span>");

			// font size 
			rep(/\[size=(.*?)\](.*?)\[\/size\]/gi,"<span style=\"font-size: $1\">$2</span>");
			
			//font family
			rep(/\[font=(.*?)\](.*?)\[\/font\]/gi,"<span style=\"font-family: $1\">$2</span>");

			//URL
			rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");

			//email, email=, hr, h3

			//pre
			rep(/\[pre\](.*?)\[\/pre\]/gi,"<pre>$1</pre>");

			//img
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" alt=\"\" title=\"\" />");

			return s;
		}
	});
	// Register plugin
	tinymce.PluginManager.add('bbcode', tinymce.plugins.BBCodePlugin);
})();