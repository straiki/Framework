$(document).ready(function() {

	// 1. tooltip
	$('[rel=tooltip]').tooltip();

	$(function () {
		$("input.onChangeSubmit").change(function () {
			$(this).closest("form").submit();
		});
	});


	// 2. confirm alert
	$(".confirm").on("click", function() {
		if ($(this).data("confirm")) {
			return confirm($(this).data("message"));

		} else {
			return confirm("Opravdu chcete provést tuto akci?");
		}
	});


	// 3. hide flashes
	window.setTimeout(function() {
		$(".alert.timeout").fadeTo(500, 0).slideUp(500, function(){
			$(this).remove();
		});
	}, 2000);


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


// ajax
jQuery(window).load(function () {
	jQuery.nette.ext('init').linkSelector = 'a.ajax';
	jQuery.nette.ext('init').formSelector = 'form.ajax';
	jQuery.nette.init();
});

