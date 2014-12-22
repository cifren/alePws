/*
 * Fuel UX Wizard
 * https://github.com/ExactTarget/fuelux
 *
 * Copyright (c) 2012 ExactTarget
 * Licensed under the MIT license.
 */

;(function ($, document, window){
	var old = $.fn.wizard;

	// WIZARD CONSTRUCTOR AND PROTOTYPE

	var Wizard = function (element, options) {
		var that = this;
		this.$element = $(element);
		this.options = $.extend({}, $.fn.wizard.defaults, options);
		this.options.disablePreviousStep = ( this.$element.data().restrict === "previous" ) ? true : false;
		this.currentStep = this.options.selectedItem.step;
		this.numSteps = this.$element.find('.steps li').length;
		this.parent = this.$element.parent();
		this.$prevBtn = this.parent.find(this.options.prevButtons);
		this.$nextBtn = this.parent.find(this.options.nextButtons);

		this.$nextBtn.each(function(){
				$(this).data('originalHTML',$(this).html());
		});
		
		// handle events
		this.$element.parent().on('click', this.options.prevButtons, function(e){
			e.stopPropagation();
			that.previous.call(that,e);
		});
		
		this.$element.parent().on('click', this.options.nextButtons, function(e){
			e.stopPropagation();
			that.next.call(that,e);			
		});
		
		this.$element.on('click', 'li.complete', function(e){
			e.stopPropagation();
			that.stepclicked.call(that,e);
		});
		
		if(this.currentStep > 1) {
			this.selectedItem(this.options.selectedItem);
		}
		
		if(this.currentStep == 1){
			this.$prevBtn.css('display', 'none');
		} else {
			this.$prevBtn.css('display', 'inline-block');
		}		
	};

	Wizard.prototype = {

		constructor: Wizard,

		setState: function () {
			var canMovePrev = (this.currentStep > 1);
			var firstStep = (this.currentStep === 1);
			var lastStep = (this.currentStep === this.numSteps);

			// disable buttons based on current step
			if( !this.options.disablePreviousStep ) {
				if(firstStep === true || canMovePrev === false){
					this.$prevBtn.css('display', 'none');
				} else {
					this.$prevBtn.css('display', 'inline-block');
				}
			}
			
			// change button text of last step, if specified
			var data = this.$nextBtn.data();
			if (data && data.originalHTML) {
				if (typeof data.originalHTML !== 'undefined') {
					// replace text
					if(lastStep === true){
						this.$nextBtn.html(this.options.text.finished);
						this.$nextBtn.addClass('btn-success');
					} else {
						this.$nextBtn.html(data.originalHTML);
						this.$nextBtn.removeClass('btn-success');
					}					
				}
			}

			// reset classes for all steps
			var $steps = this.$element.find('.steps li');
			$steps.removeClass('active').removeClass('complete');
			$steps.find('span.badge').removeClass('badge-info').removeClass('badge-success');

			// set class for all previous steps
			var prevSelector = '.steps li:lt(' + (this.currentStep - 1) + ')';
			var $prevSteps = this.$element.find(prevSelector);
			$prevSteps.addClass('complete');
			$prevSteps.find('span.badge').addClass('badge-success');

			// set class for current step
			var currentSelector = '.steps li:eq(' + (this.currentStep - 1) + ')';
			var $currentStep = this.$element.find(currentSelector);
			$currentStep.addClass('active').trigger('shown');
			$currentStep.find('span.badge').addClass('badge-info');

			// set display of target element
			var target = $currentStep.data().target;
			this.$element.next('.step-content').find('.step-pane').removeClass('active');
			$(target).addClass('active').trigger('shown');
			
			// reset the wizard position to the left
			this.$element.find('.steps').first().attr('style','left: 0');

			// check if the steps are wider than the container div
			var totalWidth = 0;
			this.$element.find('.steps > li').each(function () {
				totalWidth += $(this).outerWidth();
			});
			var containerWidth = 0;
			if (this.$element.find('.actions').length) {
				containerWidth = this.$element.width() - this.$element.find('.actions').first().outerWidth();
			} else {
				containerWidth = this.$element.width();
			}
			
			if (totalWidth > containerWidth) {
				var containerCenter = containerWidth / 2,
					$activeStep = this.$element.find('li.active').first(),
					$activeStepCenter = $activeStep.outerWidth() / 2;
		
				// newMargin = $activeStep.position().left;
				newMargin = (containerCenter - $activeStepCenter) - $activeStep.position().left;
				if (newMargin > 0) {
					this.$element.find('.steps').first().attr('style','left: 0');
				} else {
					this.$element.find('.steps').first().attr('style','left: ' + newMargin + 'px');
				}
			}
		},

		stepclicked: function (e) {
			var li          = $(e.currentTarget);
			var index       = this.$element.find('.steps li').index(li);
			var canMovePrev = true;

			if( this.options.disablePreviousStep ) {
				if( index < this.currentStep ) {
					canMovePrev = false;
				}
			}

			if( canMovePrev ) {
				var evt = $.Event('stepclick');
				this.$element.trigger(evt, {step: index + 1});
								
				if (evt.isDefaultPrevented()) return;

				this.currentStep = (index + 1);
				this.setState();
			}
		},

		previous: function () {
			var canMovePrev = (this.currentStep > 1);
			if( this.options.disablePreviousStep ) {
				canMovePrev = false;
			}
			if (canMovePrev) {
				var e = $.Event('changeStep');
				this.$element.trigger(e, {step: this.currentStep, direction: 'previous'});
				
				if (e.isDefaultPrevented()) return;

				this.currentStep -= 1;
				this.setState();
			}
		},

		next: function () {
			var canMoveNext = (this.currentStep + 1 <= this.numSteps);
			var lastStep = (this.currentStep === this.numSteps);

			if (canMoveNext) {
				var e = $.Event('changeStep');
				
				var cu = this.$element.find('.steps li').eq((this.currentStep-1)),
				ne = this.$element.find('.steps li').eq(this.currentStep);
				
				this.$element.trigger(e, {step: this.currentStep, direction: 'next', current:cu, next:ne});
				
				
				if (e.isDefaultPrevented()) return;

				this.currentStep += 1;
				this.setState();
			}
			else if (lastStep) {
				this.$element.trigger('finished',{step: this.currentStep});
			}
		},

		selectedItem: function (selectedItem) {
			var retVal, step;

			if(selectedItem) {

				step = selectedItem.step || -1;

				if(step >= 1 && step <= this.numSteps) {
					this.currentStep = step;
					this.setState();
				}

				retVal = this;
			}
			else {
				retVal = { step: this.currentStep };
			}

			return retVal;
		}
	};


	// WIZARD PLUGIN DEFINITION

	$.fn.wizard = function (option) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var methodReturn;

		var $set = this.each(function () {
			var $this   = $( this );
			var data    = $this.data( 'wizard' );
			var options = typeof option === 'object' && option;

			if( !data ) $this.data('wizard', (data = new Wizard( this, options ) ) );
			if( typeof option === 'string' ) methodReturn = data[ option ].apply( data, args );
		});

		return ( methodReturn === undefined ) ? $set : methodReturn;
	};

	$.fn.wizard.defaults = {
        selectedItem: {step:1},
		nextButtons: '.btn-next',
		prevButtons: '.btn-prev',
		text:{
			finished: 'Finished'
		}
	};

	$.fn.wizard.Constructor = Wizard;

	$.fn.wizard.noConflict = function () {
		$.fn.wizard = old;
		return this;
	};


	// WIZARD DATA-API

	$(function () {
		$('body').on('mouseover.wizard.data-api', '.wizard', function () {
			var $this = $(this);
			
			if ($this.data('wizard')) return;
			$this.wizard($this.data());
		});
	});
}(jQuery, document, window));
