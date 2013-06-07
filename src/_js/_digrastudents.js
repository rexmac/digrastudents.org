$(function() {
  // Navigation
  $('.navbar-inner').find('ul.nav').first().children().each(function() {
    var path = window.location.pathname;
    if(0 === window.location.host.toUpperCase().indexOf('BLOG.')) {
      path = '/blog';
    }
    if(path === '/') {
      path = '/home';
    }
    if(0 === path.replace(/^\//, '').toUpperCase().indexOf($(this).text().replace(/^ /, '').toUpperCase())) {
      $(this).addClass('active');
    }
  });

  /*
  // Lights off!
  if($.cookie('lightsoff')) {
    $('html').addClass('lightsoff');
    $('.navbar .brand img').attr('src', '/img/logo.white.png');
  }
  $('#light-switch').click(function(e) {
    var $html = $('html');
    e.preventDefault();
    if($html.hasClass('lightsoff')) {
      $html.removeClass('lightsoff');
      $('.navbar .brand img').attr('src', '/img/logo.png');
      $.removeCookie('lightsoff', {domain: 'digrastudents.org', path: '/'});
    } else {
      $html.addClass('lightsoff');
      $('.navbar .brand img').attr('src', '/img/logo.white.png');
      $.cookie('lightsoff', false, {expires:30, domain: 'digrastudents.org', path:'/'});
    }
  });
*/

  // Tooltips
  $('a[rel=tooltip]').tooltip();
});
