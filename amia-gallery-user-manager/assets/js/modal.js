(function($){'use strict';
  const fill = (modal, data) => { Object.keys(data||{}).forEach(k => modal.find('[name="'+k+'"]').val(data[k])); modal.find('[name="password"]').val(''); };
  $(document).on('click','.agum-open-modal',function(){ const modal=$($(this).data('target')); fill(modal,{}); modal.addClass('is-open').attr('aria-hidden','false'); });
  $(document).on('click','.agum-modal-close',function(){ $(this).closest('.agum-modal').removeClass('is-open').attr('aria-hidden','true'); });
  $(document).on('click','.agum-edit-user',function(){ const row=$(this).closest('tr'); const data=row.data('user'); const modal=$('#agum-user-modal'); fill(modal,data); modal.addClass('is-open').attr('aria-hidden','false'); });
  $(document).on('keydown',function(e){ if(e.key==='Escape'){ $('.agum-modal').removeClass('is-open').attr('aria-hidden','true'); } });
})(jQuery);
