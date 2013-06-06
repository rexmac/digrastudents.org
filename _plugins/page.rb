# Extensions to the Jekyll Page class
module Jekyll
  class Page
    def body_id
      self.data['title'] ? "page-" + self.data['title'].slugize : nil
    end

    def last_mod_date
      path = self.full_path_to_source
      return unless File.file?(path)

      latest_date = File.mtime(path)
      layouts = site.layouts
      layout = layouts[self.data['layout']]
      while layout
        date = File.mtime(layout.full_path_to_source)
        latest_date = date if(date > latest_date)
        layout = layouts[layout.data['layout']]
      end
      latest_date
    end

    alias orig_to_liquid to_liquid
    def to_liquid
      h = orig_to_liquid
      h['body_id'] = self.body_id
      h['lastmod'] = self.last_mod_date
      h
    end
  end
end
