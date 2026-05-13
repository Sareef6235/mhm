(function($){
  'use strict';
  const toast = (message, error=false) => {
    const item = $('<div/>').text(message).css(error ? {background:'linear-gradient(135deg,#ef4444,#f97316)'} : {});
    $('.agum-toast').append(item); setTimeout(()=>item.fadeOut(220,()=>item.remove()), 3500);
  };
  window.agumToast = toast;
  $(document).on('click','.agum-menu-toggle',()=>$('.agum-app').toggleClass('sidebar-open'));
  let timer;
  $(document).on('input change','#agum-search,#agum-role-filter,.agum-live-search',function(){
    clearTimeout(timer); timer=setTimeout(function(){
      const search = $('#agum-search').val() || $('.agum-live-search').val() || '';
      const role = $('#agum-role-filter').val() || '';
      const wrap = $('#agum-table-wrap'); if(!wrap.length){ return; }
      wrap.css('opacity',.55);
      $.get(agumAdmin.ajaxUrl,{action:'agum_search_users',nonce:agumAdmin.nonce,search:search,role:role}).done(function(resp){
        if(resp.success){ wrap.html(resp.data.html); toast(resp.data.total+' records found'); }
      }).always(()=>wrap.css('opacity',1));
    }, 280);
  });
  $(document).on('change','.agum-select-all',function(){ $('.agum-row-check').prop('checked', this.checked); });
  $(document).on('click','.agum-bulk-delete',function(){
    const ids = $('.agum-row-check:checked').map(function(){return this.value;}).get();
    if(!ids.length){ toast('Select at least one user.', true); return; }
    if(!confirm(agumAdmin.i18n.confirmDelete)){ return; }
    $.post(agumAdmin.ajaxUrl,{action:'agum_delete_users',nonce:agumAdmin.nonce,ids:ids}).done(function(resp){
      if(resp.success){ toast('Deleted '+resp.data.deleted+' users'); $('#agum-search').trigger('input'); }
      else{ toast('Delete failed', true); }
    });
  });
  $(document).on('click','.agum-otp',function(){
    $.post(agumAdmin.ajaxUrl,{action:'agum_generate_otp',nonce:agumAdmin.nonce,user_id:$(this).data('id')}).done(function(resp){
      resp.success ? toast('OTP: '+resp.data.otp) : toast('OTP failed', true);
    });
  });
})(jQuery);
(function($){
  'use strict';
  $(document).on('click','.agum-clear-activity',function(){
    if(!confirm('Clear recent activity logs?')){ return; }
    $.post(agumAdmin.ajaxUrl,{action:'agum_clear_activity',nonce:agumAdmin.nonce}).done(function(resp){
      if(resp.success){ window.agumToast && window.agumToast(resp.data.message); $('.agum-timeline').empty().append('<div><strong>Cleared</strong><p>No recent activity.</p></div>'); }
    });
  });
  $(document).on('click','.agum-add-column',function(){
    const wrap=$('#agum-column-manager'); const i=wrap.children().length;
    wrap.append('<div class="agum-column-row" draggable="true"><span class="agum-drag">↕</span><input name="columns['+i+'][order]" value="'+i+'" type="hidden" class="agum-column-order"><input name="columns['+i+'][key]" placeholder="column_key"><input name="columns['+i+'][label]" placeholder="Column label"><label class="agum-check"><input type="checkbox" name="columns['+i+'][enabled]" value="1" checked> Enabled</label><button class="agum-icon agum-remove-column" type="button">×</button></div>');
  });
  $(document).on('click','.agum-remove-column',function(){ $(this).closest('.agum-column-row').remove(); updateColumnOrder(); });
  let dragged=null;
  $(document).on('dragstart','.agum-column-row',function(){ dragged=this; });
  $(document).on('dragover','.agum-column-row',function(e){ e.preventDefault(); });
  $(document).on('drop','.agum-column-row',function(e){ e.preventDefault(); if(dragged&&dragged!==this){ $(this).before(dragged); updateColumnOrder(); } });
  function updateColumnOrder(){ $('#agum-column-manager .agum-column-row').each(function(i){ $(this).find('.agum-column-order').val(i); }); }
})(jQuery);
