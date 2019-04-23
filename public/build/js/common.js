$(function(){
    $(".non-border-input").focus(function(){
      $(this).css("border-bottom","1px solid");
    }).blur(function(){
      $(this).css("border-bottom","none");
    });
})