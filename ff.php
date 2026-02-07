<?php
/*
+--------------------------------------------------------------------------
|   TS Special Edition v.8.0 
|   ========================================
|   by xam
|   (c) 2005 - 2020 Template Shares Services
|   https://templateshares.net
|   ========================================
|   Web: https://templateshares.net
|   Time: $_ts_date_
|   Signature Key: $_ts_signature_key_
|   Email: contact@templateshares.net
|   TS SE IS NOT FREE SOFTWARE!
+---------------------------------------------------------------------------
*/
define('THIS_SCRIPT', 'ff.php');
require('./global.php');
$lang->load('ff');

stdhead($lang->ff['head']);  
print("<table class=\"main\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\">\n<table border=1 width=100% cellspacing=0 cellpadding=5><tr><td class=colhead align=center><font size=3>".strtoupper($SITENAME)." ".$lang->ff['head']."</font>\n</td></tr>");  
print("<tr><td align=center>");  
?>  
<script language="JavaScript1.2">    
function addEngine(name,ext,cat)  
{  
  if ((typeof window.sidebar == "object") && (typeof  
  window.sidebar.addSearchEngine == "function"))  
  {  
    window.sidebar.addSearchEngine(  
      baseurl + "/misc/"+name+".src",  
      baseurl + "/misc/"+name+"."+ext,  
      name,  
      cat );  
  }  
  else  
  {  
  alert(l_ff);  
  }  
}  
</script>  
  
<?php echo $lang->ff['info']; ?>  
<ul>  
  <li><a class="altlink" href="javascript:addEngine('<?php echo $SITENAME; ?>', 'gif', 'Torrent Search')"><?php echo sprintf($lang->ff['info2'], $SITENAME);?></a></li>  
  <li><a class="altlink" href="javascript:addEngine('<?php echo $SITENAME; ?>_forums', 'gif', 'Forums Search')"><?php echo sprintf($lang->ff['info3'], $SITENAME);?></a></li>  
</ul>  
<?php
print($lang->ff['info4']);
print("</td></tr></table>");  
print("</td></tr></table>\n"); 
print("</td></tr></table>\n");
stdfoot();  
?>