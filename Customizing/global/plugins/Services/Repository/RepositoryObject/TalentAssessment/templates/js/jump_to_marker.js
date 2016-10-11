$(document).ready(function(){
	var sPageURL = decodeURIComponent(window.location.search.substring(1));
	sURLVariables = sPageURL.split('&');

	$(sURLVariables).each(function($k, $v){
		$param = $v.split("=");
		if($param[0] == "pos") {
			var aTag = $("a[name='#pos"+ $param[1] +"']");
			$('html,body').animate({scrollTop: aTag.offset().top},'slow');
		}
	});
});