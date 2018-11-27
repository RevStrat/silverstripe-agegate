$(document).ready(function() {
    var form = $('.AgeGate form');
    $(':submit', form).click(function() {
        if ($(this).attr('name')) {
            $(form).append(
                $("<input type='hidden'>").attr({
                    name: $(this).attr('name'),
                    value: $(this).attr('value')
                })
            );
        }
    });
    $(form).submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        })
        .done(function (response) {
            var result = JSON.parse(response);
            if (result.success) {
                $('.AgeGate').fadeOut();
            } else {
                window.location = result.redirect;
            }
        });
    });
});