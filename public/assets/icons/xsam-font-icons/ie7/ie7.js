/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referencing this file must be placed before the ending body tag. */

/* Use conditional comments in order to target IE 7 and older:
	<!--[if lt IE 8]><!-->
	<script src="ie7/ie7.js"></script>
	<!--<![endif]-->
*/

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'xsam-font-icons\'">' + entity + '</span>' + html;
	}
	var icons = {
		'xs-dollar-us-sign': '&#xe900;',
		'xs-euro-sign': '&#xe901;',
		'xs-franc-cfa-sign': '&#xe902;',
		'xs-franc-congolais-sign': '&#xe903;',
		'xs-kwanza-sign': '&#xe904;',
		'xs-logo-0': '&#xe905;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		c = el.className;
		c = c.match(/xs-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
