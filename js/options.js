var $ = jQuery.noConflict();

$(document).ready(function() {
	$("input[name='delete']").click(function() {
		if (confirm('Are you sure you want to delete this item?')) {
			var id = $(this).parent().parent().find('.note-id').text();
			//console.log(id);
			var data = {
				action: 'delete_note',
				object: id
			};

			$.ajax({
			  type: "POST",
			  url: ajaxurl,
			  data: data,
			  success: function(response) {
			  	location.reload();
			  }
			});
		}
	});
});