(function(){
	
	function showAddressEditor(el) {
		if (typeof(el) == 'string') el = $(el)[0];
		popUpPage('store-address',{"i":"NEW","el":"#"+el.id});
		el.selectedIndex = 0;
	}
	
	$('select').change(function(e){
		var el = e.delegateTarget, id;
		if (el.getAttribute('name').substr(0,12) == 'deliveryType') {
			id = el.getAttribute('name').substr(13);
			if ($(el).val() == "on-site") {
				$('#paliSession-'+id).stop().slideDown();
				$('#_ADD_NOTE-'+id).stop().slideDown();
			} else {
				$('#paliSession-'+id).stop().slideUp();
				$('#_ADD_NOTE-'+id).stop().slideUp();
			}
			if ($(el).val() == "address") {
				$('#address-'+id).stop().slideDown();
				if ($('#address-'+id).val() == '' && $('#address-'+id)[0].options.length <= 2) {
					showAddressEditor('#address-'+id);
				}			
			} else {
				$('#address-'+id).stop().slideUp();
			}
		} else if ($(el).val() == "_ADDR:NEW") {
			showAddressEditor("#"+e.delegateTarget.id);
			return;
		}
	});
})();
