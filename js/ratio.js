(function($) {

    $.fn.ratio = function(args) {

        this.each(function() {
            var ratioSizes = { xs : 420, sm: 768, md: 1200, lg: 0 }

            var defaults = {
                width: $(this).width(),
                height: $(this).height(),
                ratio: false,
                ratioSize: 'md',
                ratioSizes: ratioSizes,
                ratios: {},
                maxWidth: false,
                bgImg: false,
                vAlign: ''
            }

            if(args) $.extend(defaults, args);

            var scaler = new Scaler(this, defaults);
        });

        function Scaler(target, defaults) {
            var self = this, jTarget = $(target), timeout;

            // Selector for a child element to vertically align
            if(jTarget.data('ratio-valign')) { defaults.vAlign = jTarget.data('ratio-valign'); }
            // Find the vAlign object within the scope of the ratio object
            defaults.vAlign = $(defaults.vAlign, jTarget);

            if(defaults.vAlign.length) defaults.vAlign.addClass('ratio-valign');

            if(jTarget.data('ratio-maxwidth')) defaults.maxWidth = jTarget.data('ratio-maxwidth');

            defaults.ratios.length = 0;
            if(jTarget.data('ratio')) { defaults.ratios.md = jTarget.data('ratio'); defaults.ratios.length++; }
            for(size in defaults.ratioSizes) {
                var name = size+'ratio';
                if(jTarget.data(name)) { defaults.ratios[size] = jTarget.data(name); defaults.ratios.length++; }
            }

            if(defaults.ratios.md) {
                var parts = defaults.ratios.md.split(':');
                defaults.width = parseInt(parts[0]);
                if(parts[1]) defaults.height = parseInt(parts[1]);
            }

            if(!defaults.maxWidth) defaults.maxWidth = defaults.width;

            if(!defaults.ratios.md) defaults.ratios.md = defaults.width +":"+defaults.height+":"+(defaults.width/defaults.height);

            if(defaults.height) target.style.height = defaults.height + 'px';

            $.extend(this, defaults, {
                runonce: false,
                // Checks to see if our object has changed in size, returns width if true, 0 if target width is the same
                shouldScale: function() {
                    var shouldScale = 0, widthNow = jTarget.outerWidth();

                    if(widthNow < this.width)
                    {
                        // Target has scaled down
                        shouldScale = widthNow;
                    } else if(widthNow > this.width && this.width != this.maxWidth) {
                        // Target has scaled up and hasn't already hit maxWidth
                        shouldScale = widthNow > this.maxWidth ? this.maxWidth : widthNow;
                    } else if(!this.runonce) {
                        shouldScale = defaults.width||widthNow;
                    }

                    if(shouldScale) { this.width = shouldScale; }

                    return shouldScale;
                },
                /**
                 *	Checks for a width update and updates the height of the target element
                 *	@option.bgImg - if set to true, the new height will also be applied to the background-image size
                 **/
                doScale: function() {
                    var width = this.shouldScale();
                    if(width) {
                        // update our ratio dimensions if needed
                        this.setRatioSize();
                        target.style.height = width/this.ratio + 'px';
                        if(this.bgImg) target.style.backgroundSize = 'auto '+target.style.height;
                        this.doVAlign();
                        return true;
                    }
                    return false;
                },
                /**
                 *  Checks if additional ratios have been set for viewport sizes and
                 *  updates the internal ratio
                 **/
                setRatioSize: function() {
                    if(this.ratios.length > 1) {
                        var viewportWidth = this.viewportWidth(),
                            ratioSize = 'md';

                        for(size in this.ratioSizes) {
                            if(this.ratios[size] && (viewportWidth < this.ratioSizes[size] || !this.ratioSizes[size])) {
                                ratioSize = size;
                                break;
                            }
                        }

                        if(ratioSize != this.ratioSize)
                        {
                            this.ratioSize = ratioSize;
                            this.ratio = this.getRatio(ratioSize);
                        }
                    }
                },
                getRatio: function(ratioSize) {
                    var parts = this.ratios[ratioSize].split(':'),
                        ratio;

                    if(parts[2]) {
                        ratio = parseInt(parts[2]);
                    } else {
                        parts[1] || (parts[1]=jTarget.height());
                        ratio = parseInt(parts[0])/parseInt(parts[1]);
                        this.ratio[ratioSize] = parts[0] +":"+parts[1]+":"+ratio;
                    }

                    return ratio;
                },
                viewportWidth: function() {
                    var e = window, a = 'inner';
                    if (!('innerWidth' in window )) {
                        a = 'client';
                        e = document.documentElement || document.body;
                    }
                    return e[ a+'Width' ];
                },
                doVAlign: function() {
                    if(this.vAlign && this.vAlign.length === 1) {
                        var childHeight = this.vAlign.outerHeight(),
                            height = parseInt(target.style.height) || jTarget.outerHeight();
                        if(childHeight < height) {
                            this.vAlign.css({ 'margin-top': ((height-childHeight)/2)+'px', 'margin-bottom': 0 });
                        } else if(childHeight == height) {
                            this.vAlign.css({ 'margin-top': 0, 'margin-bottom': 0 });
                        } else {
                            this.vAlign.css({ 'margin-top': '', 'margin-bottom': '' });
                        }
                    }
                }
            });


            if(!this.ratio) { this.ratio = this.getRatio('md'); }

            $(window).on('resize', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    self.doScale();
                }, 500);
            });

            this.doScale();
            this.runonce = true;

            jTarget.data('ratioScaler', this);
        }

        return this;
    }
})(jQuery);