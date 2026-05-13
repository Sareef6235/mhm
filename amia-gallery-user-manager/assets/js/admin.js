(function($){
  'use strict';
  const toast = (message, error=false) => {
    const item = $('<div/>').text(message).css(error ? {background:'linear-gradient(135deg,#ef4444,#f97316)'} : {});
    $('.agum-toast').append(item); setTimeout(()=>item.fadeOut(220,()=>item.remove()), 3500);
  };
  window.agumToast = toast;
  $(document).on('click','.agum-menu-toggle',()=>$('.agum-app').toggleClass('sidebar-open'));
  let timer, pendingDeleteIds=[];
  $(document).on('input change','#agum-search,#agum-role-filter,.agum-live-search',function(){
    clearTimeout(timer); timer=setTimeout(function(){
      const search = $('#agum-search').val() || $('.agum-live-search').val() || '';
      const role = $('#agum-role-filter').val() || '';
      const page = $('#agum-search').data('page') || 1; const perPage = $('#agum-search').data('per-page') || $('.agum-per-page').val() || 20;
      const wrap = $('#agum-table-wrap'); if(!wrap.length){ return; }
      wrap.css('opacity',.55);
      $.get(agumAdmin.ajaxUrl,{action:'agum_search_users',nonce:agumAdmin.nonce,search:search,role:role,paged:page,per_page:perPage}).done(function(resp){
        if(resp.success){ wrap.html(resp.data.html); toast(resp.data.total+' records found'); }
      }).always(()=>wrap.css('opacity',1));
    }, 280);
  });
  $(document).on('change','.agum-select-all',function(){ $('.agum-row-check').prop('checked', this.checked); });
  let pendingDeleteCallback=null;
  window.agumConfirmDelete = function(options){
    const modal=$('#agum-delete-modal'); pendingDeleteCallback=options.onConfirm || null; pendingDeleteIds=[];
    modal.find('.agum-delete-image').attr('src', options.image || modal.find('.agum-delete-image').attr('src'));
    modal.find('.agum-delete-name').text(options.name || 'Selected item');
    modal.find('.agum-delete-count').text(options.message || 'Type DELETE to confirm.');
    modal.find('.agum-delete-confirm-text').val(''); modal.find('.agum-confirm-delete').prop('disabled',true);
    modal.addClass('is-open').attr('aria-hidden','false'); setTimeout(()=>modal.find('.agum-delete-confirm-text').trigger('focus'),80);
  };
  function openDeleteModal(ids, row){
    pendingDeleteCallback=null;
    pendingDeleteIds=ids; const modal=$('#agum-delete-modal');
    const img=row && row.find('.agum-user-photo').attr('src'); const data=row && row.data('user');
    modal.find('.agum-delete-image').attr('src', img || modal.find('.agum-delete-image').attr('src'));
    modal.find('.agum-delete-name').text(data && data.name ? data.name+' (@'+data.username+')' : ids.length+' selected user(s)');
    modal.find('.agum-delete-count').text(ids.length+' record(s) selected');
    modal.find('.agum-delete-confirm-text').val(''); modal.find('.agum-confirm-delete').prop('disabled',true);
    modal.addClass('is-open').attr('aria-hidden','false'); setTimeout(()=>modal.find('.agum-delete-confirm-text').trigger('focus'),80);
  }
  $(document).on('click','.agum-bulk-delete',function(){
    const ids = $('.agum-row-check:checked').map(function(){return this.value;}).get();
    if(!ids.length){ toast('Select at least one user.', true); return; }
    openDeleteModal(ids, null);
  });
  $(document).on('click','.agum-delete-user',function(){ const row=$(this).closest('tr'); openDeleteModal([String($(this).data('id'))], row); });
  $(document).on('input','.agum-delete-confirm-text',function(){ $('.agum-confirm-delete').prop('disabled', $(this).val() !== 'DELETE'); });
  $(document).on('click','.agum-confirm-delete',function(){
    const confirmText=$('.agum-delete-confirm-text').val(); if(confirmText!=='DELETE'){ toast('Type DELETE to confirm.', true); return; }
    if(pendingDeleteCallback){ pendingDeleteCallback(confirmText); return; }
    $.post(agumAdmin.ajaxUrl,{action:'agum_delete_users',nonce:agumAdmin.nonce,ids:pendingDeleteIds,confirm_text:confirmText}).done(function(resp){
      if(resp.success){ toast(resp.data.message || 'Users deleted.'); $('#agum-delete-modal').removeClass('is-open').attr('aria-hidden','true'); $('#agum-search').trigger('input'); window.agumPollNotifications && window.agumPollNotifications(); }
      else{ toast(resp.data && resp.data.message ? resp.data.message : 'Delete failed', true); }
    }).fail(function(xhr){ toast(xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : 'Delete failed', true); });
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
    window.agumConfirmDelete({name:'Recent activity logs',message:'This will permanently clear activity logs.',onConfirm:function(confirmText){
      $.post(agumAdmin.ajaxUrl,{action:'agum_clear_activity',nonce:agumAdmin.nonce,confirm_text:confirmText}).done(function(resp){
        if(resp.success){ window.agumToast && window.agumToast(resp.data.message); $('#agum-delete-modal').removeClass('is-open'); $('.agum-timeline').empty().append('<div><strong>Cleared</strong><p>No recent activity.</p></div>'); }
      });
    }});
  });
  $(document).on('click','.agum-add-column',function(){
    const wrap=$('#agum-column-manager'); const i=wrap.children().length; const key='custom_field_'+Date.now(); const label='Custom Field '+(i+1);
    wrap.append('<div class="agum-column-row agum-column-row-advanced" draggable="true"><span class="agum-drag">↕</span><input name="columns['+i+'][order]" value="'+i+'" type="hidden" class="agum-column-order"><input name="columns['+i+'][key]" value="'+key+'" placeholder="column_key"><input name="columns['+i+'][label]" value="'+label+'" placeholder="Column label"><select name="columns['+i+'][type]"><option>text</option><option>number</option><option>email</option><option>password</option><option>select</option><option>date</option><option>image</option><option>file</option><option>textarea</option><option>toggle</option></select><input name="columns['+i+'][options]" placeholder="Options: A, B, C"><div class="agum-field-flags"><label class="agum-check"><input type="checkbox" name="columns['+i+'][enabled]" value="1" checked> Show</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][required]" value="1"> Required</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][form]" value="1" checked> Add Form</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][edit]" value="1" checked> Edit Modal</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][csv]" value="1" checked> CSV</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][bulk_upload]" value="1" checked> Bulk Upload</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][image_field]" value="1"> Image Field</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][searchable]" value="1"> Search</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][filterable]" value="1"> Filter</label><label class="agum-check"><input type="checkbox" name="columns['+i+'][export]" value="1" checked> Export</label></div><button class="agum-icon agum-remove-column" type="button">×</button></div>'); updateColumnOrder(); $('.agum-column-form').trigger('agum-preview-update').trigger('agum-save-now'); window.agumToast && window.agumToast('New column added. Saving…');
  });
  $(document).on('click','.agum-remove-column',function(){ const row=$(this).closest('.agum-column-row'); const key=row.find('[name$="[key]"]').val(); window.agumConfirmDelete({name:'Column '+key,message:'This removes the field from settings, validation, CSV, forms, tables and database.',onConfirm:function(confirmText){ $.post(agumAdmin.ajaxUrl,{action:'agum_delete_column',nonce:agumAdmin.nonce,key:key,confirm_text:confirmText}).done(function(resp){ if(resp.success){ row.remove(); updateColumnOrder(); $('#agum-delete-modal').removeClass('is-open'); $('.agum-column-form').trigger('agum-preview-update'); window.agumToast && window.agumToast(resp.data.message); } else { window.agumToast && window.agumToast(resp.data.message || 'Column delete failed', true); } }); }}); });
  let dragged=null;
  $(document).on('dragstart','.agum-column-row',function(){ dragged=this; });
  $(document).on('dragover','.agum-column-row',function(e){ e.preventDefault(); });
  $(document).on('drop','.agum-column-row',function(e){ e.preventDefault(); if(dragged&&dragged!==this){ $(this).before(dragged); updateColumnOrder(); } });
  function updateColumnOrder(){ $('#agum-column-manager .agum-column-row').each(function(i){ $(this).find('.agum-column-order').val(i); }); }
})(jQuery);

(function($){
  'use strict';
  let saveTimer;
  function saveSettings(form, silent){
    const data=form.serializeArray(); data.push({name:'action',value:'agum_save_settings'},{name:'nonce',value:agumAdmin.nonce});
    $('.agum-autosave-state').text('Saving…');
    return $.post(agumAdmin.ajaxUrl,$.param(data)).done(function(r){ $('.agum-autosave-state').text(r.success?'Saved':'Save failed'); if(!silent){ window.agumToast && window.agumToast(r.success ? r.data.message : 'Settings save failed', !r.success); } });
  }
  function updatePreview(){
    const rows=$('#agum-column-manager .agum-column-row'); const form=$('.agum-form-preview').empty(); const table=$('.agum-table-preview').empty(); const headers=[];
    rows.each(function(){
      const row=$(this), key=row.find('[name$="[key]"]').val(), label=row.find('[name$="[label]"]').val()||key;
      const enabled=row.find('[name$="[enabled]"]').is(':checked'), required=row.find('[name$="[required]"]').is(':checked'), inForm=row.find('[name$="[form]"]').is(':checked');
      if(enabled && inForm){ form.append('<span class="agum-preview-field">'+label+(required?' *':'')+'</span>'); }
      if(enabled){ headers.push(label); }
    });
    table.append('<div class="agum-preview-table-row">'+headers.slice(0,8).map(h=>'<span>'+h+'</span>').join('')+'</div>');
  }
  $(updatePreview);
  window.agumSaveColumnSettings = saveSettings;
  $(document).on('click','.agum-settings-save-ajax',function(e){ e.preventDefault(); saveSettings($(this).closest('form'), false); });
  $(document).on('input change','#agum-column-manager input,#agum-column-manager select',function(){ updatePreview(); clearTimeout(saveTimer); const form=$(this).closest('form'); saveTimer=setTimeout(()=>saveSettings(form,true),1200); });
  $(document).on('click','.agum-settings-reset',function(){ window.agumConfirmDelete({name:'Plugin settings',message:'Reset settings and field rules to defaults.',onConfirm:function(confirmText){ $.post(agumAdmin.ajaxUrl,{action:'agum_reset_settings',nonce:agumAdmin.nonce,confirm_text:confirmText}).done(function(r){ $('#agum-delete-modal').removeClass('is-open'); window.agumToast && window.agumToast(r.data.message); location.reload(); }); }}); });
  $(document).on('click','.agum-settings-export',function(){ $.post(agumAdmin.ajaxUrl,{action:'agum_export_settings',nonce:agumAdmin.nonce}).done(function(r){ if(r.success){ const blob=new Blob([JSON.stringify(r.data.settings,null,2)],{type:'application/json'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='agum-settings.json'; a.click(); } }); });
  $(document).on('click','.agum-next-page,.agum-prev-page,.agum-page-number',function(){ const page=$(this).data('page'); if(page){ $('#agum-search').data('page',page).trigger('input'); } });
  $(document).on('change','.agum-per-page',function(){ $('#agum-search').data('per-page',$(this).val()).trigger('input'); });
  $(document).on('agum-preview-update','.agum-column-form',function(){ if(typeof updatePreview==='function'){ updatePreview(); } });
  $(document).on('agum-save-now','.agum-column-form',function(){ saveSettings($(this), true); });
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
  $(document).on('click','.agum-delete-notification',function(){ const id=$(this).closest('.agum-notification').data('id'); window.agumConfirmDelete({name:'Notification #'+id,message:'Delete this notification permanently.',onConfirm:function(confirmText){ $.post(agumAdmin.ajaxUrl,{action:'agum_delete_notification',nonce:agumAdmin.nonce,id:id,confirm_text:confirmText}).done(function(r){ $('#agum-delete-modal').removeClass('is-open'); window.agumToast && window.agumToast(r.data.message); pollNotifications(); }); }}); });
  $(document).on('click','.agum-clear-notifications',function(){ window.agumConfirmDelete({name:'All notifications',message:'Clear the entire notification history.',onConfirm:function(confirmText){ $.post(agumAdmin.ajaxUrl,{action:'agum_clear_notifications',nonce:agumAdmin.nonce,confirm_text:confirmText}).done(function(r){ $('#agum-delete-modal').removeClass('is-open'); window.agumToast && window.agumToast(r.data.message); pollNotifications(); }); }}); });
  let touchStartX=0;
  $(document).on('touchstart','.agum-notification',function(e){ touchStartX=e.originalEvent.touches[0].clientX; });
  $(document).on('touchend','.agum-notification',function(e){ const dx=e.originalEvent.changedTouches[0].clientX-touchStartX; if(Math.abs(dx)>90){ $(this).find('.agum-delete-notification').trigger('click'); } });
})(jQuery);
