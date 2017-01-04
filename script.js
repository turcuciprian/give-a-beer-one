jQuery(document).ready(function($) {
  var widgetDivClass = $('.gaboWidget');
  if(widgetDivClass[0]){
    widgetDivClass.click(function(){
      var restURL = siteUrl+'/wp-json/gabo/update';
      $.ajax({
  method: "POST",
  url: restURL,
  data: { userIP: userIP }
})
  .done(function( response ) {
    alert('Thank you very much for your beer!');
  });
    });
  }
});
