<!-- Google Analytics --><script> 
{disable_google_cookies}
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga'); 
	ga('create', '{google_tracking_code}'); // set tracking code
	ga('send', 'pageview'); 
	//<!-- begin google_trans -->
	ga('require', 'ecommerce', 'ecommerce.js');
	ga('ecommerce:addTransaction', {'id': '{google_order_id}', 'affiliation': '{google_affiliation}', 'revenue': '{google_total}', 'shipping': '{google_shipping}', 'tax': '{google_tax}' });
	//<!-- begin google_items -->
	ga('ecommerce:addItem', { 'id': '{google_order_id}', 'name': '{google_item_name}', 'sku': '{google_sku_code}', 'category': '{google_category}', 'price': '{google_price}', 'quantity': '{google_quantity}' });//<!-- end google_items -->
	ga('ecommerce:send');
	//<!-- end google_trans -->
</script><!-- End Google Analytics -->