$(document).ready(function() {

	// 1. tooltip
	$("[rel=tooltip]").tooltip();


	// 2. confirm alert
	$(".confirm").click(function() {
		return confirm("Opravdu chcete provést tuto akci?");
	});


	// 3. hide flashes
	window.setTimeout(function() {
		$(".flash.error").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove();
		});
		$(".flash.success").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove();
		});
	}, 2000);

	// 4. chosen
	$(".chosen").chosen();

	// 5. sortable rows
	$("#sortable").sortable({
		delay: 200,
		distance: 15,

		update: function (event, ui) {
			var rankList = $('#sortable').sortable('toArray').toString();
			var sortLink = $('#sortable').data("sort-link");

			/*
			var paramName = $('#sortable').data("param-name");
			alert(paramName);
			*/

			$.post(sortLink, { data: rankList });
		}
	});

});
