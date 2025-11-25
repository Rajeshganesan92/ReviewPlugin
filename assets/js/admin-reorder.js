jQuery(function($){
  $('#rs_reorder_list').sortable();
  $('#rs_save_order').on('click', function(){
    var order = [];
    $('#rs_reorder_list .rs_item').each(function(){ order.push($(this).data('id')); });
    $.post(rsReorder.ajax_url, {action:'rs_save_order', order: order, _ajax_nonce: rsReorder.nonce}, function(res){
      if (res.success) alert('Saved'); else alert('Error');
    });
  });
});
