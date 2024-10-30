( function( $ ) {
	$.extend( $.validator.messages, {
		required: jquery_validate_localization.required,
		remote: jquery_validate_localization.remote,
		email: jquery_validate_localization.email,
		url: jquery_validate_localization.url,
		date: jquery_validate_localization.date,
		dateISO: jquery_validate_localization.dateISO,
		number: jquery_validate_localization.number,
		digits: jquery_validate_localization.digits,
		equalTo: jquery_validate_localization.equalTo,
		maxlength: $.validator.format( jquery_validate_localization.maxlength ),
		minlength: $.validator.format( jquery_validate_localization.minlength ),
		rangelength: $.validator.format( jquery_validate_localization.rangelength ),
		range: $.validator.format( jquery_validate_localization.range ),
		max: $.validator.format( jquery_validate_localization.max ),
		min: $.validator.format( jquery_validate_localization.min ),
		matadorMaxsize: $.validator.format( jquery_validate_localization.maxsize ),
		matadorExtension: $.validator.format( jquery_validate_localization.extension ),
	} );
} )( jQuery );
