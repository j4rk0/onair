jQuery(function($) {
    var $sucessRepeat = 5000;
    var $errorRepeat  = 20000;
    function getAjax(type, element) {
        $.ajax({
         type: type,
         url: element.data('endpoint'),
         dataType: 'json',
         /*
         data: { postVar1: 'theValue1', postVar2: 'theValue2' },
         beforeSend:function(){
           //console.log( "lorem ipsum");
         },
         */
         success:function(data){
           //console.log(Object.keys(data[Object.keys(data)[0]]));
           $.each( Object.keys(data[Object.keys(data)[0]]), function( index, value ) {
             //console.log( index + ": " + value );
             $value = data[Object.keys(data)[0]][value];
             element.find('.'+value).text($value).prop('title', $value);
           });
           element.find('.loader').hide();
           element.find('.result').show();
           setTimeout(function(){ 
            getAjax(type, element);
           }, $sucessRepeat);
         },
         error:function(){
           console.log('error - called:' + element.data('endpoint'));
           setTimeout(function(){ 
            getAjax(type, element);
           }, $errorRepeat);
         }
        });
    }
    $( document ).ready(function() {
        $('.ajax-onload').each(function( index ) {
          //console.log( index + ": " + $( this ).data('endpoint') );
          getAjax('GET', $(this));
        });
    });
});

