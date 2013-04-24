$(document).ready(function() {


	// datepicker překlad
	$.datepicker.regional['cs'] = {
		closeText: 'Zavřít',
		prevText: '&#x3c;Dříve',
		nextText: 'Později&#x3e;',
		currentText: 'Nyní',
		monthNames: ['leden','únor','březen','duben','květen','červen','červenec','srpen','září','říjen','listopad','prosinec'],
		monthNamesShort: ['led','úno','bře','dub','kvě','čer','čvc','srp','zář','říj','lis','pro'],
		dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
		dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
		dayNamesMin: ['ne','po','út','st','čt','pá','so'],
		weekHeader: 'Týd',
		dateFormat: 'dd. mm. yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
	$.datepicker.setDefaults($.datepicker.regional['cs']); /* deaktivace v případě en */


	// datepicker - livequery edit, "each" before
	$("input.date").each(function () { // input[type=date] does not work in IE
		var el = $(this);
		var value = el.val();
		var date = (value ? $.datepicker.parseDate($.datepicker.W3C, value) : null);

		var minDate = el.attr("min") || null;
		if (minDate) minDate = $.datepicker.parseDate($.datepicker.W3C, minDate);
		var maxDate = el.attr("max") || null;
		if (maxDate) maxDate = $.datepicker.parseDate($.datepicker.W3C, maxDate);

		// Replace built-in date input: NOTE: input.attr("type", "text") throws exception by the browser
		if (el.attr("type") == 'date') {
			var tmp = $("<input/>");

			$.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value".split(","), function(i, attr)  {
				tmp.attr(attr, el.attr(attr));
			});
			el.replaceWith(tmp);
			el = tmp;
		}

		el.datepicker({
			minDate: minDate,
			maxDate: maxDate
		});
		el.val($.datepicker.formatDate(el.datepicker("option", "dateFormat"), date));
	});

	$("input.date.birthDate")
        .datepicker("option", "changeMonth", true)
        .datepicker("option", "changeYear", true);

});