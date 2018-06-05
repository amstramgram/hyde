(function($) {

	$.Caroubou = function(options, element) {

		this.options = $.extend(true, {}, $.Caroubou.defaults, options)

		this._current = null
		this._currentPrev = null
		this._currentNext = null
		this._indexes = []
		this._datas = null

		this.$element = element
		this._type = this.$element.tagName

		this.initialize();
	}

	$.Caroubou.defaults = {
		'count' : 0,
		'selectorClass' : '.item.image',
		'modalContainer' : '.modal-container',
		'modalClass' : 'modal gallery',
		'modalClassActive' : 'modal-active',
		'containerID' : 'lightbox',
		'containerClass' : 'modal-lightbox',
		'slidesClass' : 'slides',
		'slidesWrapperClass' : 'slides-wrapper',
		'itemClass' : 'item',
		'loadingClass' : 'loading'
	}

	$.Caroubou.prototype = {
		initialize : function() {


			if( this._type == "SCRIPT" ){
				// get les datas
				this._datas = JSON.parse($.trim(this.$element.innerHTML.replace(/[\t\n]+/g, '')))
				this.options.count = this._datas.length - 1
			}else{
				this.options.containerID = "gallery-"+Math.random().toString(36).substr(2)
				$(this.$element).attr('ID', this.options.containerID)
				$figures = $("#"+this.options.containerID).find('figure')
				$figures.each(function(index){
					$(this).addClass('item').attr("data-index", index)
					if(index == 0){
						$(this).addClass('current')
					}
				})
				this.options.count = $figures.length - 1
			}

			this.buttons()
			this.events()

			if( this._type == "SCRIPT" ){
				// create la modal
				this.$modal = $('<div id="'+ this.options.containerID +'" class="'+ this.options.containerClass +' '+ this.options.modalClass +'" />')

				// add buttons to modal
				this.$modal.append(this.$controlClose, this.$controlPrev, this.$controlNext)

				// create wrapperSlides
				$wrapperSlides = $('<div class="'+ this.options.slidesWrapperClass +'">')
				this.$modal.append($wrapperSlides)
				// create slides
				this.$slides = $('<div class="'+ this.options.slidesClass +'">')
				// append slides in modal
				this.$modal.find('.'+this.options.slidesWrapperClass).append(this.$slides)
			}else{
				$("#"+this.options.containerID).append(this.$controlPrev, this.$controlNext)
				$('#'+this.options.containerID).find('.control.previous').addClass('hidden')
			}
		},
		construct : function() {
			if(!$('#'+this.options.containerID).length){
				$(this.options.modalContainer).append(this.$modal)
			}

			this.slides()

			$('body').addClass(this.options.containerClass+' '+this.options.modalClassActive)
		},
		deconstruct : function() {
			$('body').removeClass(this.options.modalClassActive)
			$('#'+this.options.containerID).find('.'+this.options.slidesClass).remove()
			$('#'+this.options.containerID).find('.control.previous, .control.next').removeClass('hidden')

			this.$slides = $('<div class="'+ this.options.slidesClass +'">')
			this._indexes = []
		},
		previous : function() {
			// prepend
			oldIndex = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').data('index') - 1

			this._current =  oldIndex - 1

			oldCurrent = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').index()

			if(this._current >= 0){
				if(oldCurrent == 1 && this._type == "SCRIPT"){
					$item = $('<img src="'+_this._datas[this._current].url+'">').wrap('<figure data-index="'+this._current+'" class="'+ _this.options.itemClass +'">')
					$('#'+this.options.containerID+' .'+this.options.slidesClass).prepend($item.parent())
				}
			}else{
				$('#'+this.options.containerID).find('.control.previous').addClass('hidden')
			}

			indexCurrent = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').index() - 1

			if(indexCurrent >= 0){
				$('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass).removeClass('current')
				$('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass).eq(indexCurrent).addClass('current')
			}

			this.translate()

		},
		next : function() {

			// append
			oldIndex = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').data('index') + 1

			this._current =  oldIndex + 1

			oldCurrent = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').index() + 1
			countItem = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass).length

			if(this._current <= this.options.count){
				if((countItem - oldCurrent) == 1 && this._type == "SCRIPT"){
					$item = $('<img src="'+_this._datas[this._current].url+'">').wrap('<figure data-index="'+this._current+'" class="'+ _this.options.itemClass +'">')
					$('#'+this.options.containerID+' .'+this.options.slidesClass).append($item.parent())
				}
			}else{
				$('#'+this.options.containerID).find('.control.next').addClass('hidden')
			}

			indexCurrent = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').index() + 1

			console.log(indexCurrent)
			console.log(this.options.count)

			if(indexCurrent <= this.options.count){
				$('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass).removeClass('current')
				$('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass).eq(indexCurrent).addClass('current')
			}

			this.translate()
		},
		slides : function () {
			console.log('-- slides --')
			this.slide()

			$('#'+this.options.containerID)
			.find('.'+this.options.slidesWrapperClass)
			.append(this.$slides)

			this.translate()
		},
		slide : function (){
			_this = this
			$.each(this._indexes, function(i, item){
				current = (item == _this._current)? 'current' : ''
				$item = $('<img src="'+_this._datas[item].url+'">').wrap('<figure data-index="'+item+'" class="'+ _this.options.itemClass +' '+ current +'">')
				_this.$slides.append($item.parent())
			})
		},
		translate : function() {
			console.log("-- translate --")
			index = $('#'+this.options.containerID+' .'+this.options.slidesClass).find('.'+this.options.itemClass+'.current').index()
			translate = ( this._type == "SCRIPT" )? (-95 * index)+"vw" : (-100 * index)+"%";

			console.log(translate)

			$('#'+this.options.containerID).find('.'+this.options.slidesClass).css({'transform':'translateX( '+translate+' )'})
		},
		events : function() {
			var self = this
			this.$controlPrev.on( 'click', $.proxy( this.controls, this, 'previous' ) )
			this.$controlNext.on( 'click', $.proxy( this.controls, this, 'next' ) )
			this.$controlClose.on( 'click', $.proxy( this.controls, this, 'close' ) )

			$(document).on('click', this.options.selectorClass, $.proxy(this.controls, this))
			$(document).on('keydown', $.proxy(this.controls, this))
		},
		controls : function(event) {
			action = event
			if( typeof event == "object" ){
				action = this.action(event)
			}

			switch (action) {
				case 'click':
				this.indexes(event)
				this.construct()
				break;
				case 'close':
				this.deconstruct()
				break;
				case 'previous':
				$('#'+this.options.containerID).find('.control.previous, .control.next').removeClass('hidden')
				this.previous()
				break;
				case 'next':
				$('#'+this.options.containerID).find('.control.previous, .control.next').removeClass('hidden')
				this.next()
				break;
			}
		},
		action : function(event) {
			var type = ''
			if(event.type == 'keydown'){
				if($('body').hasClass(this.options.modalClassActive)){
					console.log(event.keyCode)
					switch (event.keyCode) {
						case 27:
						type = "close"
						break;
						case 37:
						type = "previous"
						break;
						case 32:
						case 39:
						type = "next"
						break;
					}
				}else{
					type = null
				}

			}else{
				type = event.type
			}

			return type
		},
		indexes : function(event) {
			this._indexes = []
			if(!!event){
				this._current = $(event.currentTarget).index()
			}

			if(this._current > 0){
				this._currentPrev = this._current - 1
				this._indexes.push(this._currentPrev)
			}else{
				this.$controlPrev.addClass('hidden')
			}

			this._indexes.push(this._current)
			if(this._current < this.options.count){
				this._currentNext = this._current + 1
				this._indexes.push(this._currentNext)
			}else{
				this.$controlNext.addClass('hidden')
			}
		},
		buttons : function() {
			console.log('-- buttons --')
			this.$controlClose = $('<div class="control close"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet" fill="#FFF"><circle class="icon-bg" cx="50" cy="50" r="47.5"></circle><polygon class="icon-close" points="64.5,39.8 60.2,35.5 50,45.7 39.8,35.5 35.5,39.8 45.7,50 35.5,60.2 39.8,64.5 50,54.3 60.2,64.5 64.5,60.2 54.3,50"></polygon></svg></div>')
			this.$controlPrev = $('<div class="control previous"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 60 60" xml:space="preserve" fill="#FFF"><circle class="icon-bg" cx="30" cy="30" r="30"></circle><path class="icon-arrow" d="M36.8,36.4L30.3,30l6.5-6.4l-3.5-3.4l-10,9.8l10,9.8L36.8,36.4z"></path></svg></div>')
			this.$controlNext = $('<div class="control next"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 60 60" xml:space="preserve" fill="#FFF"><circle class="icon-bg" cx="30" cy="30" r="30"></circle><path class="icon-arrow" d="M24.2,23.5l6.6,6.5l-6.6,6.5l3.6,3.5L37.8,30l-10.1-9.9L24.2,23.5z"></path></svg></div>')
		}
	}

	$.fn.Caroubou = function(options) {

		this.each(function() {
			var instance = $.data( this, 'Caroubou' );
			if ( instance ) {
				instance.initialize();
			}
			else {
				instance = $.data( this, 'Caroubou', new $.Caroubou( options, this ) );
			}
		});

		return this;
	};
})( jQuery );