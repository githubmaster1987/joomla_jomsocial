var isAnimationEnable = 0;
var interVal;

$(document).ready(function() {

  $(".showEmotions").hover(function() {

    if (isAnimationEnable == 0) {
      $(".emoji-reactions").show().css('opacity', '1');
      $(".emoji-reactions span").velocity("transition.bounceUpIn", {
        stagger: 80
      });
      isAnimationEnable = 1;
      interVal = setInterval(function() {
        if (isAnimationEnable == 1) {
          cursorListener();
        }
      }, 500);
    }

  }, function() {

  });

  function cursorListener() {
    var isHovered = !!$('.emoji-reactions , .actionBox').
    filter(function() {
      return $(this).is(":hover");
    }).length;
    console.log(isHovered);
    if (!isHovered) {
      $(".emoji-reactions").velocity("transition.fadeOut", {
        delay: 1000
      });
      clearInterval(interVal);
      isAnimationEnable = 0;

    }
  }

});