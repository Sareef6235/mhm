(function($){'use strict';
  const toast=(m)=>{const el=$('<div/>').text(m);$('.amia-toast').append(el);setTimeout(()=>el.fadeOut(220,()=>el.remove()),3200)};
  $(window).on('load',()=>{$('body').removeClass('amia-loading')});
  $(document).on('click','.amia-menu-toggle',()=>$('.amia-primary-menu').toggleClass('is-open'));
  $(document).on('click','.amia-theme-toggle',()=>{$('html').attr('data-theme',$('html').attr('data-theme')==='dark'?'light':'dark')});
  $(document).on('click','.amia-search-toggle',()=>$('.amia-search-popup').addClass('is-open').find('input').trigger('focus'));
  $(document).on('click','.amia-search-popup',function(e){if(e.target===this){$(this).removeClass('is-open')}});
  $(document).on('click','.amia-notification-toggle',()=>$('.amia-notification-dropdown').toggleClass('is-open'));
  $(document).on('click','.amia-profile-toggle',()=>$('.amia-profile-dropdown').toggleClass('is-open'));
  let timer;$(document).on('input','.amia-ajax-search',function(){const q=this.value;clearTimeout(timer);timer=setTimeout(()=>{$.get(amiaTheme.ajaxUrl,{action:'amia_theme_search',nonce:amiaTheme.nonce,query:q}).done(r=>{if(r.success){$('.amia-search-results').html(r.data.html)}})},260)});
  $(document).on('click','.amia-clear-activity',function(){if(!confirm('Clear recent activity?'))return;$.post(amiaTheme.ajaxUrl,{action:'amia_clear_activity',nonce:amiaTheme.nonce}).done(r=>{if(r.success){toast(r.data.message);$('.amia-activity-list').html('<div class="amia-card">No recent activity.</div>')}})});
  $(document).on('change','input[type=file][multiple]',function(){const grid=$(this).closest('form').find('.amia-preview-grid').empty();Array.from(this.files||[]).forEach(file=>{if(file.type.indexOf('image/')===0){grid.append($('<img loading="lazy" alt="Preview">').attr('src',URL.createObjectURL(file)))}})});
  $(document).on('submit','.amia-upload-zone',function(){$(this).find('.amia-progress span').animate({width:'100%'},1000)});
  const obs=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('amia-animate')}}),{threshold:.12});document.querySelectorAll('.amia-card,.amia-section').forEach(el=>obs.observe(el));
if('serviceWorker' in navigator){navigator.serviceWorker.register('/wp-content/themes/amia-gallery-premium/assets/js/sw.js').catch(()=>{});} })(jQuery);
if('Notification' in window && Notification.permission==='default'){document.addEventListener('click',()=>Notification.requestPermission(),{once:true});}
let touchStartX=0;document.addEventListener('touchstart',e=>{touchStartX=e.changedTouches[0].screenX},{passive:true});document.addEventListener('touchend',e=>{if(e.changedTouches[0].screenX-touchStartX>90){document.querySelector('.amia-primary-menu')?.classList.add('is-open')}},{passive:true});
