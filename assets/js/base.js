$(document).ready(function() {

	// 1. tooltip
	$('[rel=tooltip]').tooltip();
	

	// 2. confirm alert
	$(".confirm").click(function() {
		return confirm("Opravdu chcete provést tuto akci?");
	});


	// 3. hide flashes
	/*window.setTimeout(function() {
		$(".flash-error").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove(); 
		});
		$(".flash-success").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove(); 
		});
	}, 2000);*/

	window.setTimeout(function() {
		$(".flash.timeout").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove(); 
		});
	}, 2000);


	// 4. autoresize textaera
	$('.autoresize.height150').autoResize({
		minHeight: 150,
		animate: {duration: 300},
	});

	$('.autoresize.height300').autoResize({
		minHeight: 300,
		animate: {duration: 300},
	});


	// 5. showCombo slider
	/*
		.showCombo
			.showBox Show
			.box Box
	 */
	$(".showCombo .showBox").click(function(){
		$(this).closest(".showCombo").find(".showed").hide();
		$(this).closest(".showCombo").find(".box").show();
		$(this).closest(".showCombo").find(".showBox").hide();
		$(this).closest(".showCombo").find(".showBoxBack").show();
		$(this).hide();
	});

	$(".showCombo .showBoxBack").click(function(){
		$(this).closest(".showCombo").find(".showed").show();
		$(this).closest(".showCombo").find(".box").hide();
		$(this).closest(".showCombo").find(".showBox").show();
		$(this).hide();
	});


	// 6. onChange submit
	$(function () {
		$("input.onChangeSubmit").change(function () {
			$(this).closest("form").submit();
		});
	});

});
