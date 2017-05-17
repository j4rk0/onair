jQuery(function ($) {
    var $sucessRepeatTimeout = 5000;
    var $errorRepeatTimeout = 20000;
    var $badgeAnimationDuration = 800;

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
            success: function (data) {
                //console.log(Object.keys(data[Object.keys(data)[0]]));
                //console.log(data[Object.keys(data)[0]].playing);
                var myData = data[Object.keys(data)[0]];
                if(myData.playing && myData.playing !== undefined) {
                    // putting values to coresponding dom elements if they exist
                    $.each(Object.keys(data[Object.keys(data)[0]]), function (index, value) {
                        var myValue = myData[value];
                        $item = element.find('.' + value);
                        if ( $item.length && myValue) {
                            if ( $item.text() !== myValue ) {
                                console.log('item: '+$item.text());
                                console.log('value: '+myValue);
                                $item.text(myValue).prop('title', myValue);
                                element.find('.badge_loading').hide();
                                element.find('.badge_onair').hide();
                                element.find('.badge_updated').fadeIn($badgeAnimationDuration);
                                element.find('.loader').hide();
                                element.find('.result').show();
                            }
                            else {
                                element.find('.badge_updated').hide();
                                element.find('.badge_loading').hide();
                                element.find('.badge_onair').fadeIn($badgeAnimationDuration);
                            }
                        }
                    });
                }
                else {
                    element.find('.badge_loading').hide();
                    element.find('.badge_offline').fadeIn($badgeAnimationDuration);
                    element.find('.loader').show();
                    element.find('.result').hide();
                }
                setTimeout(function () {
                    getAjax(type, element);
                }, $sucessRepeatTimeout);
            },
            error: function () {
                console.log('error - called:' + element.data('endpoint'));
                element.find('.artist').text('');
                element.find('.title').text('');
                setTimeout(function () {
                    getAjax(type, element);
                }, $errorRepeatTimeout);
            }
        });
    }
    $(document).ready(function () {
        $('.ajax-onload').each(function (index) {
            //console.log( index + ": " + $( this ).data('endpoint') );
            getAjax('GET', $(this));
        });
    });
});