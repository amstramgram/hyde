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

	$('#caroubou-datas').Caroubou()

})(jQuery)