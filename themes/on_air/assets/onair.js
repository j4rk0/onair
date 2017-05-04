jQuery(function($) {
    function getAjax(type, element) {
       $.ajax({
        type: type,
        url: element.data('endpoint'),
        dataType: 'json',
        /*
        data: { postVar1: 'theValue1', postVar2: 'theValue2' },
        beforeSend:function(){
          $('#ajax-panel').html('<div class="loading"><img src="/images/loading.gif" alt="Loading..." /></div>');
        },
        */
        success:function(data){
          //$response = ;
          //console.log(Object.keys(data[Object.keys(data)[0]]));
          $.each( Object.keys(data[Object.keys(data)[0]]), function( index, value ) {
            // console.log( index + ": " + value );
            element.find('.'+value).text(data[Object.keys(data)[0]][value]);
          });
          element.find('.cssload-conveyor').hide();
          element.find('.result').show();
//        },
        error:function(){
          console.log('error' );
        }
       });
    }

    $( document ).ready(function() {
        console.log('document ready');
        $('.ajax-onload').each(function( index ) {
          //console.log( index + ": " + $( this ).data('endpoint') );
          getAjax('GET', $(this));
        });
    });
});

