jQuery(function ($) {
    var newRepeatTimeout = 60 * 1000;
    var defaultRepeatTimeout = 5 * 1000;
    var badgeAnimationDuration = 800;

    function getAjax(type, element) {
        // var calls = 0;
        // calls++;
        // console.log('api calls ' + calls);

        var repeatTimeout = defaultRepeatTimeout;
        $.ajax({
            type: type,
            url: element.data('endpoint'),
            dataType: 'json',
            success: function (data) {
                var myData = data[Object.keys(data)[0]];
                // Radio is playing song
                if (myData.playing && myData.playing !== undefined) {
                    // putting values to coresponding dom elements if they exist
                    $.each(Object.keys(data[Object.keys(data)[0]]), function (index, value) {
                        var myValue = myData[value];
                        $item = element.find('.' + value);
                        if ($item.length && myValue) {
                            // console.log('item text: ' + $item.text() + ' - ' + $item.text().length);
                            // song data got update
                            if ($item.text() !== myValue) {

                                $item.prop($item.data('updateprop'), myValue);
                                if ($item.data('updatetext')) {
                                    $item.text(myValue);
                                    element.find('.badge').hide();
                                    element.find('.badge_updated').fadeIn(badgeAnimationDuration);
                                    element.find('.loader').hide();
                                    element.find('.result').show();
                                }
                            }
                            // no update, still playing same song
                            else {
                                element.find('.badge').hide();
                                element.find('.badge_onair').show();
                                // we extend the call interval a bit, songs are usually longer
                                repeatTimeout = newRepeatTimeout;
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
            },
            error: function () {
                console.log('error - called:' + element.data('endpoint'));
                element.find('.artist').text('');
                element.find('.title').text('');
            },
            complete: function () {
                // here we set next call based on previous calls result
                setTimeout(function () {
                    getAjax(type, element);
                }, repeatTimeout);
                //console.log('current timeout:' + repeatTimeout);
            }
        });
    }

    //ajaxify songs
    $(document).ready(function () {
        $('.ajax-onload').each(function (index) {
            getAjax('GET', $(this));
        });
    }).on('click', '[data-toggle="lightbox"]', function (event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
});