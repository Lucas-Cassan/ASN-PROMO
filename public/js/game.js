$(document).ready(function(){
    $('.cardPiocheOther').mouseenter(function (){
        $('.cardPiocheOther1').css('transform', 'rotate(-10deg)');
        $('.cardPiocheOther2').css('transform', 'rotate(10deg)');
    });
    $('.cardPiocheOther').mouseleave(function (){
        $('.cardPiocheOther1').css('transform', 'rotate(0deg)');
        $('.cardPiocheOther2').css('transform', 'rotate(0deg)');
    });
});