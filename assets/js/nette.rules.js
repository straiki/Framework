/**
 * Validate time
 * @test!
 */
Nette.validators.SchmutzkaFormsRules_validateTime = function (elem, arg, value) {
    var pattern = /^\s*([01]?\d|2[0-3]):?([0-5]\d)\s*$/;
    if (m = value.match(pattern)) {
        result = (m[1].length == 2 ? "" : "0") + m[1] + ":" + m[2];
    }

    return result;
}


/**
 * Validate date
 * @test!
 * @2DO
 */
Nette.validators.SchmutzkaFormsRules_validateDate = function (elem, arg, value) {
	/*
    var pattern = /^\s*([01]?\d|2[0-3]):?([0-5]\d)\s*$/;
    if (m = value.match(pattern)) {
        result = (m[1].length == 2 ? "" : "0") + m[1] + ":" + m[2];
    }

    return result;
	*/
}


/**
 * Validate zip
 */
Nette.validators.SchmutzkaFormsRules_validateZip = function (elem, arg, value) {
	var pattern = /^\d{3} ?\d{2}$/;

	if (value.match(pattern)) {
		return true;
	}

	return false;
}


/**
 * Validate phone number
 */
Nette.validators.SchmutzkaFormsRules_validatePhone = function (elem, arg, value) {
	var pattern = /^(\+\d{2,3})? ?\d{3} ?\d{3} ?\d{3}$/;

	if (value.match(pattern)) {
		return true;
	}

	return false;
}


/**
 * Validate RC
 * http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo#comment-12097
 */
Nette.validators.SchmutzkaFormsRules_validateRC = function (elem, arg, value) {
	try {
		if(value.length == 0) return true;
		if(value.length < 9) throw 1;
		var year = parseInt(value.substr(0, 2), 10);
		var month = parseInt(value.substr(2, 2), 10);
		var day = parseInt( value.substr(4, 2), 10);
		var ext = parseInt(value.substr(6, 3), 10);
		if((value.length == 9) && (year < 54)) return true;
		var c = 0;
		if(value.length == 10) c = parseInt(value.substr(9, 1));
		var m = parseInt( value.substr(0, 9)) % 11;
		if(m == 10) m = 0;
		if(m != c) throw 1;
		year += (year < 54) ? 2000 : 1900;
		if((month > 70) && (year > 2003)) month -= 70;
		else if (month > 50) month -= 50;
		else if ((month > 20) && (year > 2003)) month -= 20;
		var d = new Date();
		if((year) > d.getFullYear()) throw 1;
		if(month == 0) throw 1;
		if(month > 12) throw 1;
		if(day == 0) throw 1;
		if(day > 31) throw 1;

	} catch(e) {
		return false;
	}

	return true;
};


/**
 * Validate IC
 * http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo#comment-12097
 */
Nette.validators.SchmutzkaFormsRules_validateIC = function (elem, arg, value) {
	try {
		var a = 0;
		if(value.length == 0) return true;
		if(value.length != 8) throw 1;
		var b = value.split('');
		var c = 0;
		for(var i = 0; i < 7; i++) a += (parseInt(b[i]) * (8 - i));
		a = a % 11;
		c = 11 - a;
		if(a == 1) c = 0;
		if(a == 0) c = 1;
		if(a == 10) c = 1;
		if(parseInt(b[ 7]) != c) throw(1);

	} catch(e) {
		return false;
	}

	return true;
};