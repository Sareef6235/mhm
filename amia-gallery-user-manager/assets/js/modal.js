(function($){'use strict';
  const setRequiredMode = (modal, isEdit) => {
    const password = modal.find('[name="password"]');
    if (typeof password.data('agum-required') === 'undefined') { password.data('agum-required', password.prop('required') ? 1 : 0); }
    password.prop('required', !isEdit && password.data('agum-required') === 1);
    modal.find('[name="profile_photo_file"],[name="image_path"],[name="profile_photo"]').prop('required', false);
    modal.find('.agum-field').each(function(){ const field=$(this); const show=isEdit ? field.data('agum-edit') !== 0 : field.data('agum-form') !== 0; field.toggle(show); field.find(':input').prop('disabled', !show); });
    if (isEdit) { password.attr('placeholder','Leave blank to keep current password'); }
    else { password.attr('placeholder','Minimum 8 characters'); }
  };
  const fill = (modal, data) => {
    Object.keys(data||{}).forEach(k => modal.find('[name="'+k+'"]').val(data[k]));
    modal.find('[name="password"]').val('');
    const img = data && (data.profile_photo || data.image_path);
    if (img) { modal.find('.agum-profile-preview').attr('src', img); }
  };
  $(document).on('click','.agum-open-modal',function(){ const modal=$($(this).data('target')); fill(modal,{}); setRequiredMode(modal,false); modal.addClass('is-open').attr('aria-hidden','false'); });
  $(document).on('click','.agum-modal-close',function(){ $(this).closest('.agum-modal').removeClass('is-open').attr('aria-hidden','true'); });
  $(document).on('click','.agum-edit-user',function(){ const row=$(this).closest('tr'); const data=row.data('user'); const modal=$('#agum-user-modal'); fill(modal,data); setRequiredMode(modal,true); modal.addClass('is-open').attr('aria-hidden','false'); });
  $(document).on('keydown',function(e){ if(e.key==='Escape'){ $('.agum-modal').removeClass('is-open').attr('aria-hidden','true'); } });
})(jQuery);
