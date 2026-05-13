(function($){'use strict';
  $(function(){ $('.agum-stat strong').each(function(){ const el=$(this), max=parseInt(el.text(),10)||0; $({n:0}).animate({n:max},{duration:650,step:function(v){el.text(Math.ceil(v));},complete:function(){el.text(max);}}); }); });
})(jQuery);
