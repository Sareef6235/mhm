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
