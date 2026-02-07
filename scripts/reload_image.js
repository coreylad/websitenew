function reload ()
{
	TSGetID('regimage').src = baseurl+"/ts_image.php?type=new&width=132&height=50&" + (new Date()).getTime();
	TSGetID('listen').style.visibility = "hidden";
	return;
};