jQuery(document).ready(function($){
	var $currentId = $('input#currentId');

	$('.account').on('click', function(){
		$currentId.attr('value', $(this).find('div.hidden').text());
		$(this).addClass('selected').siblings('.account').removeClass('selected');
	});
});