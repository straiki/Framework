$(document).ready(function() {

	// 1. tooltip
	$('[rel=tooltip]').tooltip();
	

	// 2. confirm alert
	$(".confirm").click(function() {
		return confirm("Opravdu chcete provést tuto akci?");
	});


	// 3. hide flashes
	window.setTimeout(function() {
		$(".flash.flash-error").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove(); 
		});
		$(".flash.flash-success").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove(); 
		});
	}, 2000);

});