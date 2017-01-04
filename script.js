jQuery(document).ready(function($) {
  var widgetDivClass = $('.gaboWidget');
  if(widgetDivClass[0]){
    widgetDivClass.click(function(){
      var restURL = pluginUrl+'wp-json/gabo/update';
      console.log(restURL);
    });
  }
});
