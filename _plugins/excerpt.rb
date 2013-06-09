# https://gist.github.com/stympy/986665#file-post-html-L6
module Jekyll
  class Post
    alias_method :original_to_liquid, :to_liquid
    def to_liquid
      original_to_liquid.deep_merge({
        'excerpt' => content.match('<!--more-->') ? content.split('<!--more-->').first : nil
      })
    end
  end

  module Filters
    def mark_excerpt(content)
      content.gsub('<!--more-->', '<a name="more" class="readmore-anchor">&nbsp;</a>')
    end
  end
end
