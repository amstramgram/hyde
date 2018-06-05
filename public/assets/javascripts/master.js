(function( $ ){
	//console.log('hello')

	var $layout = $('.layout-container'),
	$buttonMenu = $('#buttonMenu')

	$buttonMenu.on('click', function(e){
		if( $layout.hasClass('menu-active') ){
			$layout.removeClass('menu-active')
			$buttonMenu.find('i').text('menu')
		}else{
			$layout.addClass('menu-active')
			$buttonMenu.find('i').text('close')
		}
	})

	if( $('#caroubou-datas').length > 0){
		$('#caroubou-datas').Caroubou()
	}

	if( $('.gallery').length > 0){
		$('.gallery').Caroubou()
	}

})(jQuery)