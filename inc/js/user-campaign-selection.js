(function($) {

    var scope = $('#campaign-user-selection'),
        form = $('form', scope),
        aBlocks = $('.action-block', scope);

    aBlocks.on('touchstart', function(e) {
        $(this).toggleClass('active');
    });

    // Prevent default form submission
    form.on('submit', function(e) { e.preventDefault(); });

    // Custom form submission based on user action
    form.on('click', function(e) {
        console.log(form.get().campaign);
    });

})(jQuery);