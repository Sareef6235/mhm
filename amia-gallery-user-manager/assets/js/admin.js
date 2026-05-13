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
      if(resp.success){ toast(resp.data.message || ('Deleted '+resp.data.deleted+' users')); $('#agum-search').trigger('input'); window.agumPollNotifications && window.agumPollNotifications(); }
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
    wrap.append('<div class="agum-column-row agum-column-row-advanced" draggable="true"><span class="agum-drag">↕</span><input name="columns['+i+'][order]" value="'+i+'" type="hidden" class="agum-column-order"><input name="columns['+i+'][key]" placeholder="column_key"><input name="columns['+i+'][label]" placeholder="Column label"><select name="columns['+i+'][type]"><option>text</option><option>number</option><option>email</option><option>password</option><option>select</option><option>date</option><option>image</option><option>file</option><option>textarea</option><option>toggle</option></select><input name="columns['+i+'][options]" placeholder="Options: A, B, C"><div class="agum-field-flags"><label class="agum-check"><input type="checkbox" name="columns['+i+'][enabled]" value="1" checked> Show</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][required]" value="1"> Required</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][form]" value="1" checked> Add Form</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][edit]" value="1" checked> Edit Modal</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][csv]" value="1" checked> CSV</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][bulk_upload]" value="1" checked> Bulk Upload</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][image_field]" value="1"> Image Field</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][searchable]" value="1"> Search</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][filterable]" value="1"> Filter</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][export]" value="1" checked> Export</label></div><button class="agum-icon agum-remove-column" type="button">×</button></div>');
  });
  $(document).on('click','.agum-remove-column',function(){ $(this).closest('.agum-column-row').remove(); updateColumnOrder(); });
  let dragged=null;
  $(document).on('dragstart','.agum-column-row',function(){ dragged=this; });
  $(document).on('dragover','.agum-column-row',function(e){ e.preventDefault(); });
  $(document).on('drop','.agum-column-row',function(e){ e.preventDefault(); if(dragged&&dragged!==this){ $(this).before(dragged); updateColumnOrder(); } });
  function updateColumnOrder(){ $('#agum-column-manager .agum-column-row').each(function(i){ $(this).find('.agum-column-order').val(i); }); }
})(jQuery);

(function($){
  'use strict';
  $(document).on('click','.agum-settings-save-ajax',function(e){
    e.preventDefault(); const form=$(this).closest('form'); const data=form.serializeArray(); data.push({name:'action',value:'agum_save_settings'},{name:'nonce',value:agumAdmin.nonce});
    $.post(agumAdmin.ajaxUrl,$.param(data)).done(function(r){ window.agumToast && window.agumToast(r.success ? r.data.message : 'Settings save failed', !r.success); });
  });
  $(document).on('click','.agum-settings-reset',function(){ if(!confirm('Reset plugin settings?'))return; $.post(agumAdmin.ajaxUrl,{action:'agum_reset_settings',nonce:agumAdmin.nonce}).done(function(r){ window.agumToast && window.agumToast(r.data.message); location.reload(); }); });
  $(document).on('click','.agum-settings-export',function(){ $.post(agumAdmin.ajaxUrl,{action:'agum_export_settings',nonce:agumAdmin.nonce}).done(function(r){ if(r.success){ const blob=new Blob([JSON.stringify(r.data.settings,null,2)],{type:'application/json'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='agum-settings.json'; a.click(); } }); });
  $(document).on('click','.agum-next-page,.agum-prev-page,.agum-page-number',function(){ const page=$(this).data('page'); if(page){ $('#agum-search').data('page',page).trigger('input'); } });
  $(document).on('change','.agum-per-page',function(){ $('#agum-search').data('per-page',$(this).val()).trigger('input'); });
})(jQuery);
(function($){
  'use strict';
  let lastSeenId = 0, noteTimer;
  function escapeHtml(value){ return $('<div/>').text(value || '').html(); }
  function activeFilters(){ return { search: $('.agum-notification-search:focus').val() || $('.agum-notification-search').first().val() || '', category: $('.agum-notification-filter:focus').val() || $('.agum-notification-filter').first().val() || 'all' }; }
  function renderNotifications(items){
    const boxes=$('.agum-notifications'); if(!boxes.length){return;}
    boxes.each(function(){
      const box=$(this); box.empty();
      if(!items || !items.length){ box.append('<div class="agum-notification-empty">No notifications found.</div>'); return; }
      items.forEach(function(n){
        const type=escapeHtml(n.type || 'info'), id=parseInt(n.id,10)||0, unread=parseInt(n.is_read,10)===0;
        box.append('<div class="agum-notification agum-notification-'+type+(unread?' is-unread':'')+'" data-id="'+id+'" data-category="'+escapeHtml(n.category || 'all')+'"><img class="agum-notification-avatar" src="'+escapeHtml(n.profile_image)+'" alt=""><div class="agum-notification-body"><strong>'+escapeHtml(n.title)+'</strong><p>'+escapeHtml(n.message)+'</p><div class="agum-notification-meta"><span>@'+escapeHtml(n.username || 'system')+'</span><span>'+escapeHtml(n.role || '—')+'</span><span>'+escapeHtml(n.source_system || 'Plugin')+'</span><time>'+escapeHtml(n.time_ago || n.created_at)+'</time></div><small>By '+escapeHtml(n.actor_name || 'System')+'</small></div><button type="button" class="agum-delete-notification" aria-label="Delete notification">×</button></div>');
      });
    });
  }
  function pollNotifications(){
    if(typeof agumAdmin==='undefined'){return;}
    const f=activeFilters();
    $.get(agumAdmin.ajaxUrl,{action:'agum_get_notifications',nonce:agumAdmin.nonce,limit:30,search:f.search,category:f.category}).done(function(r){
      if(!r.success){return;}
      const unread=parseInt(r.data.unread,10)||0;
      $('.agum-notification-count').text(unread).toggleClass('is-zero', unread===0);
      const items=r.data.items||[];
      if(items.length){
        const newest=parseInt(items[0].id,10)||0;
        if(lastSeenId && newest>lastSeenId){ items.filter(item => (parseInt(item.id,10)||0)>lastSeenId).reverse().forEach(item => window.agumToast && window.agumToast(item.title+': '+item.message, item.type==='error')); }
        lastSeenId=Math.max(lastSeenId,newest);
      }
      renderNotifications(items);
    });
  }
  function debouncedPoll(){ clearTimeout(noteTimer); noteTimer=setTimeout(pollNotifications,180); }
  window.agumPollNotifications = pollNotifications;
  setInterval(pollNotifications,15000); $(pollNotifications);
  $(document).on('input','.agum-notification-search',debouncedPoll);
  $(document).on('change','.agum-notification-filter',pollNotifications);
  $(document).on('click','.agum-bell',function(e){ e.preventDefault(); const center=$(this).closest('.agum-notification-center'); center.toggleClass('is-open'); $(this).attr('aria-expanded', center.hasClass('is-open') ? 'true' : 'false'); if(center.hasClass('is-open')){ pollNotifications(); } });
  $(document).on('click',function(e){ if(!$(e.target).closest('.agum-notification-center').length){ $('.agum-notification-center').removeClass('is-open'); $('.agum-bell').attr('aria-expanded','false'); } });
  $(document).on('click','.agum-mark-notifications-read',function(){ $.post(agumAdmin.ajaxUrl,{action:'agum_mark_notifications_read',nonce:agumAdmin.nonce}).done(function(r){ window.agumToast && window.agumToast(r.data.message); pollNotifications(); }); });
  $(document).on('click','.agum-delete-notification',function(){ const id=$(this).closest('.agum-notification').data('id'); $.post(agumAdmin.ajaxUrl,{action:'agum_delete_notification',nonce:agumAdmin.nonce,id:id}).done(function(r){ window.agumToast && window.agumToast(r.data.message); pollNotifications(); }); });
  $(document).on('click','.agum-clear-notifications',function(){ if(!confirm('Clear all notifications?')){return;} $.post(agumAdmin.ajaxUrl,{action:'agum_clear_notifications',nonce:agumAdmin.nonce}).done(function(r){ window.agumToast && window.agumToast(r.data.message); pollNotifications(); }); });
  let touchStartX=0;
  $(document).on('touchstart','.agum-notification',function(e){ touchStartX=e.originalEvent.touches[0].clientX; });
  $(document).on('touchend','.agum-notification',function(e){ const dx=e.originalEvent.changedTouches[0].clientX-touchStartX; if(Math.abs(dx)>90){ $(this).find('.agum-delete-notification').trigger('click'); } });
})(jQuery);
