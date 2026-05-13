(function($){'use strict';
  const allowedExt = /\.(jpe?g|png|webp)$/i;
  const allowedMime = ['image/jpeg','image/png','image/webp'];
  const validateLocal = (input, file) => {
    if(!file){ return 'Profile photo is required.'; }
    if(file.size > 5242880){ return 'Maximum upload size is 5MB.'; }
    if(allowedMime.indexOf(file.type)===-1 || !allowedExt.test(file.name)){ return 'Only JPG, JPEG, PNG, and WEBP images are allowed.'; }
    return '';
  };
  $(document).on('change','input[type="file"]',function(){
    const file=this.files&&this.files[0]; const form=$(this).closest('form'); const preview=form.find('.agum-preview');
    if($(this).attr('name') === 'csv_file'){ return; }
    const error = validateLocal(this, file);
    if(error){ window.agumToast && window.agumToast(error, true); this.value=''; form.find('[name="image_path"],[name="profile_photo"]').val(''); return; }
    if(file.type.indexOf('image/')===0){
      const src=URL.createObjectURL(file);
      preview.html($('<img class="agum-profile-preview" alt="Preview">').attr('src',src));
      form.find('[name="image_path"],[name="profile_photo"]').val(src);
      const fd = new FormData(); fd.append('action','agum_validate_image'); fd.append('nonce',agumAdmin.nonce); fd.append('image',file);
      $.ajax({url:agumAdmin.ajaxUrl,method:'POST',data:fd,processData:false,contentType:false}).done(function(resp){
        if(resp.success){ window.agumToast && window.agumToast(resp.data.message); }
        else { window.agumToast && window.agumToast(resp.data.message || 'Image validation failed.', true); }
      });
    }
  });
  $(document).on('submit','.agum-user-form',function(e){
    const form=$(this); let missing=[];
    ['student_id','admission_no','name','class','dob','role','username','email','phone_number','image_path','profile_photo'].forEach(function(name){ if(!form.find('[name="'+name+'"]').val()){ missing.push(name); } });
    const isEdit = !!form.find('[name="id"]').val();
    if(!isEdit && !form.find('[name="password"]').val()){ missing.push('password'); }
    if(form.find('[name="password"]').val() && form.find('[name="password"]').val().length < 8){ missing.push('password_min_8'); }
    if(missing.length){ e.preventDefault(); window.agumToast && window.agumToast('Missing/invalid required fields: '+missing.join(', '), true); }
  });
  $(document).on('submit','.agum-upload-zone',function(){ $(this).find('.agum-progress span').animate({width:'100%'},900); });
})(jQuery);
