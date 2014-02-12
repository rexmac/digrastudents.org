# Add full_url to post Hash for use in Liquid templates.
#
module Jekyll
  class Post
    #def full_url
    #  self.url
    #end

    # Convert this post into a Hash for use in Liquid templates.
    #
    # Returns <Hash>
    alias_method :original_to_liquid, :to_liquid
    def to_liquid(attrs = nil)
      original_to_liquid(attrs).deep_merge({
        "title"      => self.data["title"] || self.slug.split('-').select {|w| w.capitalize! || w }.join(' '),
        "url"        => self.url,
        "full_url"   => (site.config['url'] + self.url).gsub("//#{site.config['domain']}", "//blog.#{site.config['domain']}").gsub("/blog/", "/"),
        "date"       => self.date,
        "id"         => self.id,
        "categories" => self.categories,
        "next"       => self.next,
        "previous"   => self.previous,
        "tags"       => self.tags,
        "content"    => self.content,
        "excerpt"    => content.match('<!--more-->') ? content.split('<!--more-->').first : nil
      })
    end
  end

  module Filters
    def mark_excerpt(content)
      content.gsub('<!--more-->', '<a name="more" class="readmore-anchor">&nbsp;</a>')
    end
  end
end
