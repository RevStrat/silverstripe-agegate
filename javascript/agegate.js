$('.AgeGate form').submit(function(e) {
    e.preventDefault();
    var $form = $(this);
    $.ajax({
        type: $form.attr('method'),
        url: $form.attr('action'),
        data: $form.serialize()
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