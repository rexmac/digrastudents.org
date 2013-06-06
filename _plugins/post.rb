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
    def to_liquid
      self.data.deep_merge({
        "title"      => self.data["title"] || self.slug.split('-').select {|w| w.capitalize! || w }.join(' '),
        "url"        => self.url,
        "full_url"   => (site.config['url'] + self.url).gsub(/\/\/rexmac/, '//blog.rexmac').gsub(/\/blog\//, '/'),
        "date"       => self.date,
        "id"         => self.id,
        "categories" => self.categories,
        "next"       => self.next,
        "previous"   => self.previous,
        "tags"       => self.tags,
        "content"    => self.content
      })
    end
  end
end
