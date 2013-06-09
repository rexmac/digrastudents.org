<footer>
<div class="row{% if page.fluid %}-fluid{% endif %}">
<div class="span8" markdown="1">
&copy; {{ site.time | date: '%Y' }} {{ site.author.name }}. Please read [the license page]({{ site.url }}/license) for details about the licensing of this website's content.

Site created by [rexmac](http://rexmac.com/) and powered by [Jekyll](http://jekyllrb.com/), [Bootstrap](http://twitter.github.com/bootstrap/), and [H5BP](http://html5boilerplate.com/). Icons courtesy of [FontAwesome](http://fortawesome.github.com/Font-Awesome/) by Dave Gandy. Social media badges courtesy of [SimekOneLove](http://simekonelove.deviantart.com/#/d45qg9a).{% if page.credits %} {{ page.credits }}{% endif %} This site is proudly hosted on [Site5](http://www.site5.com/in.php?id=23116) because they share their midi-chlorians.
</div>
<div class="span4">
{% if page.lastmod %}<p class="pull-right">Last modified: {{ page.lastmod }}</p>{% endif %}
</div>
</div>
</footer>
