var fontsets = {
	'layout1': 'Seaweed+Script|Medula+One',
	'layout2': 'Seaweed+Script',
	'layout3': 'Seaweed+Script'
}

var googleFonts = fontsets[qr_template];

if (typeof(googleFonts) != 'undefined') {
var fonts = googleFonts.indexOf("|") != -1 ? googleFonts.split('|') : [ googleFonts ];
WebFontConfig = {
	google: {
		families: fonts
	}
};

(function() {
	var wf = document.createElement('script');
	wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
			'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
	wf.type = 'text/javascript';
	wf.async = 'true';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(wf, s);
})();
}

jQuery(function($) {
	$body = $('body');
	$('.form-controls').anticsForm();
	
	if($body.hasClass('qr-receipt'))
	{
		var order_info_table = $('table:not(#viewCartTable)', '.qr_about_content'), oit_tds = $('td', order_info_table);
		order_info_table.addClass('qr-order-info');
		oit_tds.each(function(i) {
			if($(this).html().replace(/(&nbsp;|\s)/g, '').trim() === '')
				this.parentNode.removeChild(this);
		});
	}
});