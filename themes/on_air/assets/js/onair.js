jQuery(function ($) {
    var sucessRepeatTimeout = 4000;
    var errorRepeatTimeout = 1000;
    var badgeAnimationDuration = 800;

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
                // Radio is playing song
                if (myData.playing && myData.playing !== undefined) {
                    // putting values to coresponding dom elements if they exist
                    $.each(Object.keys(data[Object.keys(data)[0]]), function (index, value) {
                        var myValue = myData[value];
                        $item = element.find('.' + value);
                        if ($item.length && myValue) {
                            if ($item.text() !== myValue) {
                                //console.log('item: '+$item.text());
                                //console.log('value: '+myValue);
                                $item.prop($item.data('updateprop'), myValue);
                                if ($item.data('updatetext')) {
                                   $item.text(myValue);
                                   element.find('.badge').hide();
                                   element.find('.badge_updated').fadeIn(badgeAnimationDuration);
                                   element.find('.loader').hide();
                                   element.find('.result').show();
                                }
                                //console.log($item.data('updateprop'));

                            }
                            else {
                                element.find('.badge').hide();
                                element.find('.badge_onair').show();
                                //element.find('.badge_onair').fadeIn(badgeAnimationDuration);
                            }
                        }
                    });
                }
                // Radio currently doesn't play any song
                else {
                    element.find('.badge_loading').hide();
                    element.find('.badge_offline').fadeIn(badgeAnimationDuration);
                    element.find('.loader').show();
                    element.find('.result').hide();
                }
                setTimeout(function () {
                    getAjax(type, element);
                }, sucessRepeatTimeout);
            },
            error: function () {
                console.log('error - called:' + element.data('endpoint'));
                element.find('.artist').text('');
                element.find('.title').text('');
                setTimeout(function () {
                    getAjax(type, element);
                }, errorRepeatTimeout);
            }
        });
    }
    
    //ajaxify songs
    $(document).ready(function () {
        $('.ajax-onload').each(function (index) {
            getAjax('GET', $(this));
        });
    }).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
});