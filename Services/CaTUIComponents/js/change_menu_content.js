$(document).ready(function() {
	$(document).on("click",'a[id^="expand_"]',"", function(e) {
		element_id = e.target.id;
		new_id = element_id.replace("expand_", "");
		$.ajax({
			url:il.mainMenue,
			type: 'POST',
			data: {needed: new_id},
			context: document.body
		}).done(function(data) {
			$("#"+element_id + " + ul").empty().append(data);
			$('a[id="'+element_id+'"]').attr("id", new_id);
		});
	});
});