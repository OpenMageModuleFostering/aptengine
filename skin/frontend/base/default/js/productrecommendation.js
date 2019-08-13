jQuery(document).ready(function() {
	jQuery('.product-ordered').click(function() {
		var cartArr = addAllToCart.split("related_product");
		var skuCode = jQuery(this).data('sku');
		var selectedId = jQuery(this).val();
		jQuery('.add-single-item').hide();
		jQuery('.add-all-button').show();
		if (jQuery(this).is(":checked")) {
			totalPrice = totalPrice
					+ jQuery(this).data('price');
			if (cartArr[1].indexOf(selectedId) == -1) {
				addAllToCart = addAllToCart + selectedId + ',';
			}
			jQuery('#product-' + skuCode).show();
		} else {
			totalPrice = totalPrice
					- jQuery(this).data('price');
			if (cartArr[1].indexOf(selectedId) != -1) {
				var relatedId = cartArr[1];
				relatedId = relatedId.replace(selectedId, '');
				addAllToCart = addAllToCart.replace(cartArr[1],
						relatedId.replace(selectedId, ''));
				if(relatedId.match(/\d+/g) == null) {
					jQuery('.add-single-item').show();
					jQuery('.add-all-button').hide();
				}
			}
		
			jQuery('#product-' + skuCode).hide();
		}

		jQuery('.add-cart-url').val(addAllToCart);
		jQuery('.product-total').html(
				curr + parseFloat(totalPrice).toFixed(2));
		jQuery('.add-selected-button').show();
		if (totalPrice == 0) {
			jQuery('.add-selected-button').hide();
		}
	});

	jQuery('.add-selected-button').click(function() {
		window.location = jQuery('.add-cart-url').val();
	});

	jQuery('.product-total').html(
			curr + parseFloat(totalPrice).toFixed(2));
});