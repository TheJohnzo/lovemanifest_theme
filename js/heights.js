(function($) {
	$.fn.heights = function(args) {
		
		var defaults = {
			height: 0,
			group: ''
		}
		
		if(args) $.extend(defaults, args);
		
		var sizer = new heightSizer(this, defaults);
		
		this.each(function() {
			
			var group = $(this).data('ratio');
			
			if(ratioAttr) {
				var parts = ratioAttr.split(':');
				if(parts[0]) defaults.width = parts[0];
				if(parts[1]) defaults.height = parts[1];
				if(parts[2]) defaults.ratio = parts[2];
			}
			
		});
		
		function heightSizer(target, defaults) {
			var self = this, jTarget = $(target), timeout;
			
			if(!defaults.ratio) defaults.ratio = defaults.width/defaults.height;
			if(!defaults.maxWidth) defaults.maxWidth = defaults.width;
			
			$.extend(this, {
				width: defaults.width,
				maxWidth: defaults.width,
				height: defaults.maxWidth,
				ratio: defaults.ratio,
				bgImg: defaults.bgImg,
				// Checks to see if our object has changed in size, returns width if true, 0 if target width is the same
				shouldScale: function() {
					var shouldScale = 0, widthNow = jTarget.width();
					
					if(widthNow < this.width)
					{
						// Target has scaled down
						shouldScale = widthNow;
					} else if(widthNow > this.width && this.width != this.maxWidth){
						// Target has scaled up and hasn't already hit maxWidth
						shouldScale = widthNow > this.maxWidth ? this.maxWidth : widthNow;
					}
					
					if(shouldScale) this.width = shouldScale;
					
					return shouldScale;
				},
				/**
				 *	Checks for a width update and updates the height of the target element
				 *	@option.bgImg - if set to true, the new height will also be applied to the background-image size
				**/
				doScale: function() {
					var width = this.shouldScale();
					if(width) {
						target.style.height = width/this.ratio + 'px';
						if(this.bgImg) target.style.backgroundSize = 'auto '+target.style.height;
					}
				}
			});
			
			$(window).on('resize', function() {
				clearTimeout(timeout);
				timeout = setTimeout(function() {
					self.doScale();
				}, 500);
			});
			
			self.doScale();
		}
		
		return this;
	}
})(jQuery);