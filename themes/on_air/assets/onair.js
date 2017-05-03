jQuery(function($) {
    $( document ).ready(function() {
      console.log('document ready');
      $('.ajax-onload').each(function( index ) {
        console.log( index + ": " + $( this ).data('endpoint') );
      });
    });
});

