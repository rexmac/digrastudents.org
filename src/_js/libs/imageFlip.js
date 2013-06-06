// Rich Bradshaw <http://css3.bradshawenterprises.com/flip/>
if(!Modernizr.csstransforms3d) {
  var el = $('.profile-image-flip');
  el.click(function() {
    el.toggleClass('active');
    if(el.hasClass('active')) {
      el.flip({
        direction: 'lr',
        //content: el.find('.back.face').html()
        content: '<img src="/img/profile.photo.jpg" />'
      });
    } else {
      el.flip({
        direction: 'rl',
        content: '<img src="/img/profile.pixel.png" />'
      });
    }
  });
}
