(function($) {

    Theme.parentHeights.push('.parent-height');

	$('[data-ratio]').ratio({ bgImg: true, vAlign: '#featured-image-content' });

    $('#social-menu').find('li').on('click', function(e) {
        if(e.target.childNodes[0] && e.target.childNodes[0].tagName == 'A')
        {
            window.location = e.target.childNodes[0].href;
        }
    });

//    var pageIntro = $('#page-intro');
//    if(pageIntro.length) {
//        $('span', '#page-title').each(function() {
//            $(this).addClass('flip');
//            this.style.top = -($(this).outerHeight()-parseInt($(this.parentNode).css('margin-top'))-1)+'px';
//        });
//    }

    var campaignDonationSelect = $('#campaign-donation-select');
    if(campaignDonationSelect.length)
    {
        var donationBoxes = campaignDonationSelect.siblings('.campaign-donation');
        campaignDonationSelect.customSelect().on('change', function(e) {
            donationBoxes.filter(':visible').addClass('transition-out').fadeOut(function() {
                $(this).removeClass('transition-out')
            });
            donationBoxes.filter(':eq('+e.target.value+')').fadeIn();
//            var newBox = donationBoxes.filter(':eq('+e.target.value+')');
//            newBox.css({display: 'block', opacity: 0}).parent().css('height', newBox.height());
//            newBox.css({display: 'none', opacity: ''}).animate({ opacity: 'show' });
        });

        if($_GET['campaign']) {
            donationBoxes.each(function() {
                var campaignID = $(this).attr('rel');

                if(campaignID == $_GET['campaign']) {
                    var _scrollNow = document.body.scrollTop;

                    campaignDonationSelect.val($(this).index()).trigger('change');

                    setTimeout(function() {
                        var containerBox = $('#campaign-donation-box');
                        if(document.body.scrollTop == _scrollNow) {
                            $("html, body").animate({scrollTop: containerBox.offset().top - 100}, 1000, 'easeInOutQuad');
                        }
                    }, 500)
                }
            });
        }
    }

    var aboutDonationsNav = $('#about-donations');
    if(aboutDonationsNav.length)
    {
        (function() {
            var items = $('a.box-link', aboutDonationsNav),
                directionalNav = $('button', aboutDonationsNav),
                targetBlocks = $();

            items.each(function() {
                var href = this.href;
                var target = $(href.slice(href.indexOf('#')))
                targetBlocks = targetBlocks.add(target);
            })

            items.on('click', function(e) {
                e.preventDefault();

                var index = items.index( this );
                targetBlocks.filter(':visible').fadeOut();
                targetBlocks.filter(':eq('+index+')').fadeIn();

            });

            targetBlocks.css({ top: aboutDonationsNav.height() + 60 });
            var timeout;
            $(window).resize(function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    targetBlocks.css({ top: aboutDonationsNav.height() + 60 });
                }, 50);
            });

            items.filter(':first-child').trigger('click');
        })();
    }

    $('#menu').children().on('click', function(e) {
        if(e.target.tagName != 'A' && !e.isDefaultPrevented()) {
            window.location = $(e.target).children('a').attr('href');
        }
    });

    $('input').filter('[data-prefix]').each(function() {
        var prefix = this.getAttribute('data-prefix');

        this.value = prefix + this.value;

        $(this).on('keydown', function(e) {
            // Is one through 9
            pass = e.keyCode < 65 && e.keyCode >46;

            // Is period
            pass||(pass=e.keyCode==190);

            // Is pre
            e.keyCode!=8||pass||(pass=e.target.value.length!=prefix.length);

            pass||e.preventDefault();
        });
    });

    var prompt = Theme.$_GET('prompt');
    if(prompt) {
        switch(prompt) {
            case 'sign-in':
                $(document).ready(function() { $('#menu').find('.login-link').trigger('click'); });
                break;
        }
    }

    var tooltips = $('[data-toggle="tooltip"]');
    if(tooltips.length) {
        tooltips.tooltip();
    }
	
})(jQuery);