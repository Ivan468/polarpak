<!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={google_tracking_code}"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '{google_tracking_code}');
//<!-- begin google_trans -->
	gtag('event', 'purchase', {
		"transaction_id": "{google_order_id}",
		"affiliation": "{google_affiliation}",
		"value": {google_total},
		"currency": "{google_currency_code}",
		"tax": {google_tax},
		"shipping": {google_shipping},
		"items": {google_items}
	});
//<!-- end google_trans -->
</script>