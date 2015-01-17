// JavaScript Document
$(function() {
	$('#chart li').each(function() {
        var pc = $(this).attr('title');
		pc = pc > 100 ? 100 : pc;
		$(this).children('.percent').html(pc+'%');
		var ww = $(this).width();
		var len = parseInt(ww, 10) * parseInt(pc, 10) / 100;
		$(this).children('.bar').css('width', len);
    });
});