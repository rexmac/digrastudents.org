// Rich Bradshaw <http://css3.bradshawenterprises.com/flip/>
if(!Modernizr.csstransforms3d) {
  var $el = $('.profile-image-flip'),
      $front = $el.find('.front.face'),
      $back = $el.find('.back.face');

  $el.click(function() {
    $el.toggleClass('active');
    if($el.hasClass('active')) {
      $el.flip({
        direction: 'lr',
        //content: '<img src="/img/profile.photo.jpg" />'
        content: $front.html()
      });
    } else {
      $el.flip({
        direction: 'rl',
        //content: '<img src="/img/profile.pixel.png" />'
        content: $back.html()
      });
    }
  });
}
