module Jekyll
  class TagPageGenerator < Generator
    safe true

    def generate(site)
      site.tags.each do |tag, posts|
        target_dir = self.class.tag_dir(site.config['tag_dir'] || 'tag', tag)
        site.pages << TagPage.new(site, site.source, target_dir, 'index.html', tag, posts)
      end
    end

    def self.tag_dir(base_dir, tag)
      base_dir = base_dir.gsub(/^\/*(.*)\/*$/, '\1')
      tag = tag.gsub(/_|\P{Word}/, '-').gsub(/-{2,}/, '-').downcase
      File.join(base_dir, tag)
    end
  end
end
