(function($) {
    $(document).ready(function() {
        var loginLink = $('.login-link > a', '#menu');

        loginLink.on('click', function(e) {
            e.preventDefault();
            $( loginLink.attr('href') ).modal('toggle');
        })
    });
})(jQuery);