if (typeof oak == "undefined" || !oak) {
    var oak = {};
}

oak.orders_list = {
	elementsUrl: null,
    init: function() {
        $('.order-index .show-details').on('click', function(e) {
            var self = this;
            var thisTableRow = $(self).closest('tr');
			//alert($(e.target.tagName).parent().html());
			// if($(e.target.tagName).parent().get(0).tagName == 'A' | e.target.tagName == 'A') {
			// 	return null;
			// }
			if(thisTableRow.next('tr').hasClass('order-detail')) {
				thisTableRow.next('tr').remove();
			}
			else {
				var id = thisTableRow.data('key');

				if(id) {
					$(tr).find('td').css('opacity', '0.3');

					$.post(oak.orders_list.elementsUrl, {ajax: true, orderId: id},
						function(json) {
							$(tr).after('<tr class="order-detail"><td colspan="100">'+json.elementsHtml+'</td></tr>');
							$(tr).find('td').css('opacity', '1');
						}, "json");
				}

				var tr = $(thisTableRow);
			}
		});
    },
};

oak.orders_list.init();
