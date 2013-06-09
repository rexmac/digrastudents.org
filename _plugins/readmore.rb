module PostMore
  def postmorefilter(input, url, text, limit)
    if input.include? '<!--more-->'
      input.split(' <!--more-->').first + "&hellip;<a href=\"#{url}/#more\">#{text}</a>"
    elsif !limit.nil? && limit > 0
      truncatewords(strip_html(input), limit) + "<a href=\"#{url}/#more\">#{text}</a>"
    else
      input
    end
  end

  #def truncatewords(input, length)
  #  truncate = input.split(' ')
  #  if truncate.length > length
  #    truncate[0..length-1].join(' ').strip
  #  else
  #    input
  #  end
  #end
end

Liquid::Template.register_filter(PostMore)
