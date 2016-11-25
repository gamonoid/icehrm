function SocialShare(){
};

SocialShare.facebook = function(url) {
	var w = 700;
	var h = 500;
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	
	var url = "https://www.facebook.com/sharer/sharer.php?u="+encodeURIComponent(url);

    window.open(url, "Share on Facebook", "width="+w+",height="+h+",left="+left+",top="+top);
    return false;
	
};

SocialShare.google = function(url) {
	var w = 500;
	var h = 500;
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	
	var url = "https://plus.google.com/share?url="+encodeURIComponent(url);

    window.open(url, "Share on Google", "width="+w+",height="+h+",left="+left+",top="+top);
    return false;
	
};

SocialShare.linkedin = function(url) {
	var w = 500;
	var h = 500;
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	
	var url = "https://www.linkedin.com/cws/share?url="+encodeURIComponent(url);

    window.open(url, "Share on Linked in", "width="+w+",height="+h+",left="+left+",top="+top);
    return false;
	
};

SocialShare.twitter = function(url, msg) {
	window.open('http://twitter.com/share?text='+escape(msg) + '&url=' + escape(url),'popup','width=550,height=260,scrollbars=yes,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=200,top=200');
	return false;
	
};