module Jekyll
  class TagPageGenerator < Generator
    safe true

    def generate(site)
      site.tags.each do |tag, posts|
        site.pages << TagPage.new(site, site.config['tag_dir'] || '', tag, posts)
      end
    end
  end
end
