(function(w, $, undefined) {
	
	
	// Modernizr for IE8. Fallback is pretty basic, but lets not worry too much
	// about IE8.
	var Modernizr = w.Modernizr;
	
	
	$.blockSlide = function(options, content, element) {
		
		// In case we need to select this.
		$this = this;
		
		// Some variables and selectors
		this.element = element;
		
		// The element for jQuery
		this.$element = $(element);
		
		// Is this a mobile device?
		this.mob = false;
		
		// The position from the top of the screen
		this.fromTop = 50;
		
		// Run the start function
		this.start(options, content, element);
		
	};
	
	$.blockSlide.defaults = {
		
		// The background for the header
		//imgurl : 'bg_white.jpg', /*von mir geändert*/
		
		// The width of the individual boxes
		width : 150,       // 160
		
		// The height of the individual boxes
		height : 150,     //160
		
		// The increased width after the user clicks on it
		incwidth : 360,
		
		// The increased width after the user clicks on it
		incheight : 320,
		
		// The margin between the boxes
		margin : 20,  // 40
		
		// The animation you wish to use
		animation: 'zoom', 
		
		// The length of the animation
		animationLength : 1,
		
		// The delay between boxes coming in
		delayBetween : 0.2,
		
		// The text on the button 
		showText : 'Mehr',
		
		// Blur the background image using webkit blurs. Can cause performance issues
		// Also there are a few issues using this option in Chrome, so be careful. Might
		// not want to enable this on a real project.
		blur : false,
	
		// Mobile factor, the amount you wish to divide the original height and width by to get the mobile height
		// and width. You might want to change this if you add  more than  4 items to your image carousel, as the 
		// images may not fit perfectly onto the mobile screen.  Increase it to make the icons smaller on mobile.
		mobileFactor : 3
		
		
	};
	
	$.blockSlide.prototype = {
		
		start: function(options, content, element) {
			
			// Array content 
			this.content = content;
			
			// Extend options 
			this.options = $.extend(true, {}, $.blockSlide.defaults, options);
			
			// An array for handling positions		
			this.posArray = [];
			
			// Mobile 
			this.mobileCorrect();
			
			// Append the data
			this.append();
			
			// Modernizr
			if(!Modernizr.cssanimations) {
				
				this.$element.find('[class^="bpage"]').css({'top' : '100px'});
				this.$element.find('.modal').hide();
				
			} else {	
			
				// Create the CSS Animations
				this.createAnimations();
				
			}
			
			// Run events
			this.events();
		},
		
		append: function() {
			
			// A variable called x is equal to zero
			var x = 0;
			
			// Remove any preexisting elements
			$('.pseudoblanket, [class^="bpage"], #pages').remove();
			
			// Append the pseudo blanket, a fade in dark div.
			this.$element.append('<div class="pseudoblanket"></div><div id="pages"></div>');
			
			// Run a loop to loop all the array data into appropriate containers
			for(var key in this.content) {
				
				// We have to do it this way because of IE8. Cannot append this data, so we have to append it separately.
				var backData = String(this.content[key]);
				
				
				var $appendedData = '<div class="bpage-'+x+'"style="width: '+this.options.width+'px; height: '+this.options.height+'px;">'
						$appendedData += '<img src="'+key+'" alt="" />'
						
						if(this.mob === true) {
						
							$appendedData += '<div class="mobile-button"> </div>'
						
						} else {
						
							$appendedData += '<div class="info">'
								$appendedData += '<input type="submit" value="'+this.options.showText+'" class="active-button">'
							$appendedData += '</div>'
						
						}
						
						$appendedData += '<div class="modal">'
							$appendedData += '<span class="close">&#x2421;</span>'
							$appendedData += '<div class="content"></div>'
						$appendedData += '</div>'
					$appendedData += '</div>';

					
				
				// Append the data
				this.$element.find('#pages').append($appendedData);
				
				// Create an array. This gives us quick access to the number of elements as well
				// as other things.
				this.posArray[x] = ((this.options.width*x)+(this.options.margin*x));
								
				// Set up the animation for each page. 
				this.$element.find('.bpage-'+x).css({
					'left' : this.posArray[x]+'px',
					'animation' : 'pageStart '+this.options.animationLength+'s linear '+(this.options.delayBetween*x)+'s forwards',
					'transition' : 'left 0.01s linear '+(this.options.animationLength/2)+'s'
				});
				
				this.$element.find('.bpage-'+x+' .modal .content').html(backData);
				
				
				// Keep id of each entry
				++x;
						
			}
			
			// Set the total width of the div so we can center it.
			var totalWidth = (this.options.width + this.options.margin) * x;
			this.$element.children('#pages').css({'width' : totalWidth+'px'});
			
			if(this.options.blur === false) {
				// Change the background image. We're using an image so we can blur it with webkit filters.
				this.$element.css({'background': 'url('+this.options.imgurl+')'});
			} else {
				this.$element.append('<img src='+this.options.imgurl+' alt="header-image" class="img" />');
			}
		},
		
		events: function() {
		
			// For when the user clicks 'Learn more'	
			$('.active-button, .mobile-button').on('click touchstart', function() {
				
				var that = $(this);
				// Assuming previous animations have been removed
				//if($(this).css('animation') == undefined) {
					// Remove any previous 'disappear' classes'
					$('[class^="bpage"]').removeClass('disappear');
				
					// Set the animation halfway through se we can have the modal animation.
					setTimeout(function showBack() {
						
						that.parents('[class^="bpage"]').appendTo('body').css({
							'z-index' : '999999',
							'animation' : $this.options.animation+'In '+($this.options.animationLength/2)+'s linear forwards',
							'margin-left' :'-'+(($this.options.incwidth) / 2)+'px',
							'left' : '50%',
							'position' : 'fixed'
						});
						
						that.parents('[class^="bpage"]').find('.modal').show();
				
					
					}, ($this.options.animationLength / 2) * 1000);
				
					$('.mobile-button').hide();
					
					// Alternative route for browsers that do not support animations
					if(!Modernizr.cssanimations) {
						// Add a class and then we can edit it a little bit
						$(this).parents('[class^="bpage"]').addClass('opened').css({
							'width' : $this.options.incwidth+'px', 
							'height' : $this.options.incheight+'px'
						});
					
					} else {
						// Otherwise animations are supported as add the class clicked and an animation
						$(this).parents('[class^="bpage"]').addClass('clicked')
						.css({
							'animation' : 'verticalGo '+($this.options.animationLength/2)+'s linear forwards'
						});
						
					}
					
					// For ease, the darkness will fade in rather than use an animation. 
					$('.pseudoblanket').fadeIn();
				//}
				
			});
			
			// When the user wishes to close the box, then run this function
			$('.close').on('click touchstart', function() {
				
				if(!Modernizr.cssanimations) {
					$(this).parents('[class^="bpage"]').css({
						'width' : $this.options.width+'px', 
						'height' : $this.options.height+'px'
					});
				}
				
				var that = $(this);
				
				// Get the id of the page
				var id = parseFloat($(this).parents('[class^="bpage"]').attr('class').split('-')[1]);
								
				// Then add an animation as well as the disappear class. Remove clicked and opened since
				// they are no longer valid
				$(this).parents('[class^="bpage"]').addClass('disappear').removeClass('clicked opened')
				.css({
					'animation' : $this.options.animation+'Out '+($this.options.animationLength/2)+'s linear forwards'
				});
				
					
				// Reset the animation!
				setTimeout(function hideBack() {
				
					that.parents('[class^="bpage"]').css({
						
						'z-index' : '0',
						'animation' : 'verticalLeave '+($this.options.animationLength/2)+'s linear forwards',
						'margin-left' : '0', 
						'left' : $this.posArray[id]+'px',
						'position' : 'absolute'
						
					}).appendTo($this.$element.children('#pages'));
					
					that.parents('[class^="bpage"]').find('.modal').hide();
					
					$('.mobile-button').show();
					
				}, ($this.options.animationLength / 2) * 1000);
				
				// And fade out the darkness
				$('.pseudoblanket').fadeOut();
				
			});
		},
		
		createAnimations : function() {
		
			// ========================================
			// Code from http://mzl.la/15lqyhC. THANKS!
			// ========================================
			
			var animationstring = 'animation',
			    keyframeprefix = '',
			    domPrefixes = 'Webkit Moz O ms Khtml'.split(' '),
			    pfx  = '',
			    selector = this.$element.find('[class^="bpage"]')[0];
			 
			for(var i = 0; i < domPrefixes.length; i++) {
				if(selector.style[ domPrefixes[i] + 'AnimationName' ] !== undefined) {
					pfx = domPrefixes[ i ];
					animationstring = pfx + 'Animation';
					keyframeprefix = '-' + pfx.toLowerCase() + '-';
					animation = true;
					break;
				}
			}
			
			// Some commonly used CSS contained in variables for ease
			var $widthHeight = 'width: '+$this.options.incwidth+'px; height: '+$this.options.incheight+'px;'
			var $widthHeightTop = 'width: '+$this.options.incwidth+'px; height: '+$this.options.incheight+'px; top: '+$this.fromTop+'px;'
		 
			// Our animations array. Feel free to add more animations. 
			var keyframes = {
				
				// Core animations
				'pageStart' :  		'60% { top: 50px; } 100% { top: 100px; }',
				'verticalGo' :		'0% { top: 100px; } 100% { top: -500px; }',
				'verticalLeave' : 	'0% { top: 500px; } 100% { top: 100px; }',
								
				// The way the modal will come in. Each animation has two parts, 'in' and 'out'.
				// =============================================================================
			
				// SLIDE
				// =====		
				'slideIn' :			'0% { opacity: 0; top: -500px; '+$widthHeight+' } 50% { opacity: 0; } 100% { '+$widthHeightTop+' }',			
				'slideOut' :		'0% { '+$widthHeightTop+' } 50% { opacity: 1; } 100% {  opacity: 0; top: 500px; '+$widthHeight+' }',
				
				// FLY
				// =====
				'flyIn' :			'0% { '+$widthHeightTop+' opacity: 0; left: -1000px; '+$widthHeight+' } 100% { '+$widthHeightTop+' opacity: 1; }',			
				'flyOut' :			'0% { '+$widthHeightTop+' opacity: 1; } 100% {  opacity: 0; left: 2000px; '+$widthHeight+' '+$widthHeightTop+' }',

				// ZOOM
				// ====				
				'zoomIn' :			'0% { opacity: 0; '+$widthHeightTop+' transform: scale(0.1); -webkit-transform: scale(0.1); }'
									+'50% { opacity: 1; transform: scale(1); -webkit-transform: scale(1); } 100% { '+$widthHeightTop+'  }',	
				'zoomOut' :			'0% { '+$widthHeightTop+' transform: scale(1); -webkit-transform: scale(1); }'
									+'100% { opacity: 0; '+$widthHeightTop+' transform: scale(0.1); -webkit-transform: scale(0.1); }',
			
				// ROTATE
				// ======
				'rotateIn' :		'0% { opacity: 0; '+$widthHeightTop+' transform: rotateX(90deg); -webkit-transform: rotateY(90deg); }'
									+'100% { opacity: 1; '+$widthHeightTop+' transform: rotateY(0deg); -webkit-transform: rotateX(0deg); }',
				'rotateOut' :		'0% { '+$widthHeightTop+' transform: rotateY(0); -webkit-transform: rotateY(0); }'
									+'100% { opacity: 0; '+$widthHeightTop+' transform: rotateY(90deg); -webkit-transform: rotateY(90deg); }',
				
				// ROTATE
				// ======
				'rotatepointIn' :	'0% { opacity: 0; '+$widthHeightTop+' transform: scale(0.1) rotateZ(0deg); -webkit-transform: scale(0.1) rotateZ(0deg); }'
									+'100% { opacity: 1; transform: scale(1) rotateZ(720deg); -webkit-transform: scale(1) rotateZ(720deg); '+$widthHeightTop+' }',
				'rotatepointOut' :	'0% { '+$widthHeightTop+' transform: scale(1); -webkit-transform: scale(1); }'
									+'100% { opacity: 0; '+$widthHeightTop+' transform: scale(0.1); -webkit-transform: scale(0.1); }',
				
				// FALL
				// ====
				'fallIn' : 			'0% { opacity: 0; '+$widthHeightTop+' transform: scale(3); -webkit-transform: scale(3); }'
									+'50% { opacity: 1; } 100% { '+$widthHeightTop+' transform: scale(1); -webkit-transform: scale(1); }',
				'fallOut' :			'0% { '+$widthHeightTop+' transform: scale(1); -webkit-transform: scale(1); }'
									+'100% { opacity: 0; top: 500px; '+$widthHeightTop+' transform: scale(3); -webkit-transform: scale(3); }',
				
				// GOROUND
				// =======
				'goroundIn' :		'0% { '+$widthHeightTop+' opacity: 1; '+$widthHeight+' -webkit-transform: rotateY(-90deg); transform: rotateY(-90deg); -webkit-transform-origin: 50% 50% -120px; transform-origin: 50% 50% -120px; } 100% { '+$widthHeightTop+' -webkit-transform: rotateY(0deg); transform: rotateY(0deg); -webkit-transform-origin: 50% 50% -120px; transform-origin: 50% 50% -120px; opacity: 1; }',			
				'goroundOut' :		'0% { '+$widthHeightTop+' -webkit-transform: rotateY(0deg); transform: rotateY(0deg); -webkit-transform-origin: 50% 50% -120px; transform-origin: 50% 50% -120px; } 100% { '+$widthHeightTop+' '+$widthHeight+' -webkit-transform: rotateY(90deg); transform: rotateY(90deg); -webkit-transform-origin: 50% 50% -120px; transform-origin: 50% 50% -120px; }'
				
			}; 
			 
			// Append the animations to the stylesheet
			if(document.styleSheets && document.styleSheets.length) {
			 	
			 	for(var key in keyframes) {
					document.styleSheets[0].insertRule('@'+keyframeprefix+'keyframes '+key+' {'+keyframes[key]+'}', 0);
				}
			 
			} else {
			 
				var s = document.createElement('style');
				s.innerHTML = keyframes;
				
				document.getElementsByTagName('head')[0].appendChild(s);
			}
		
		},
		
		mobileCorrect : function() {
			
			var factor = 3,
				orientCorrect = 1;
			
			// A little function for adapting screen sizes on smaller devices. This resizes the icons based on
			// the mobileFactor you provided earlier.
						
			(screenSize = function() {
				
				if($(w).width() < 900) {
				
					$this.mob = true;
					$this.fromTop = 0;
				
					$this.options.incheight = $this.options.incheight / ($this.options.mobileFactor / 2.8);
					$this.options.incwidth = $this.options.incwidth / ($this.options.mobileFactor / 2.8);
				
				}
			
				// Get the factors for each so we can resize the icons to an appropriate size on mobile devices.
				if($(w).width() < 900 && $(w).width() > 400) {
				
					factor = 2;
					$this.$element.css({'height' : '350px'});
				
				} else if($(w).width() < 400) {
				
					factor = 1.1;
					$this.$element.css({'height' : '260px'});
				
				}
				
				// Change width of icons based on this stuff
				$this.options.width = ($this.options.width / ($this.options.mobileFactor / factor)) * orientCorrect;
				$this.options.height = ($this.options.height / ($this.options.mobileFactor / factor)) * orientCorrect;
				$this.options.margin = ($this.options.margin / ($this.options.mobileFactor / factor)) * orientCorrect;				
				
			})();
			
			// Run on orientation change.
			w.onorientationchange = function() {
				
				if($(w).width() < 900 && $(w).width() > 400) {
					
					orientCorrect = 2.73;
					
				} else if($(w).width() < 400) {
					
					orientCorrect = 1.5;
				}
				
				screenSize();
			
				// Reappend
				$this.append();
				
				// Events again	
				$this.events();
			
			};
		}
		
		
	};
	
	$.fn.blockSlide = function(options, content) {
		
		return this.each(function() {
			
			new $.blockSlide(options, content, this);
			
		});
	
	};

})(window, jQuery);