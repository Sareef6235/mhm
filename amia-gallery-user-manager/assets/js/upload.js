(function($){'use strict';
  $(document).on('change','input[type="file"]',function(){
    const file=this.files&&this.files[0]; const preview=$(this).closest('form').find('.agum-preview'); preview.empty();
    if(!file){ return; }
    if(file.size > 5242880){ window.agumToast && window.agumToast('Maximum upload size is 5MB.', true); this.value=''; return; }
    const allowed=['image/jpeg','image/png','image/webp','text/csv','application/vnd.ms-excel'];
    if(allowed.indexOf(file.type)===-1 && !file.name.match(/\.csv$/i)){ window.agumToast && window.agumToast('Unsupported file type.', true); this.value=''; return; }
    if(file.type.indexOf('image/')===0){ const img=$('<img alt="Preview">'); img.attr('src',URL.createObjectURL(file)); preview.html(img); }
  });
  $(document).on('submit','.agum-upload-zone',function(){ $(this).find('.agum-progress span').animate({width:'100%'},900); });
})(jQuery);
