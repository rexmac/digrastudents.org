module Jekyll
  class CategoryPageGenerator < Generator
    safe true
    priority :low

    def generate(site)
      @site = site
      write_category_indexes
    end

    # Creates an instance of CategoryIndex for each category page, renders it, and
    # writes the output to a file.
    #
    #  category - The String category currently being processed
    def write_category_index(category, posts)
      target_dir = self.class.category_dir(@site.config['category_dir'] || 'category', category)
      @site.pages << CategoryIndexPage.new(@site, @site.source, target_dir, category, posts)

      # Create an Atom-feed for each index.
      @site.pages << CategoryIndexFeedPage.new(@site, @site.source, target_dir, category, posts)
    end

    # Loops through the list of category pages and processes each one
    def write_category_indexes
      if @site.layouts.key? 'category'
        @site.categories.each do |category, posts|
          write_category_index(category, posts)
        end
      else
        throw "No 'category_index' layout found."
      end
    end

    # Processes the given dir and removes leading and trailing slashes. Falls
    # back on the default if no dir is provided.
    def self.category_dir(base_dir, category)
      #base_dir = (base_dir || CATEGORY_DIR).gsub(/^\/*(.*)\/*$/, '\1')
      base_dir = base_dir.gsub(/^\/*(.*)\/*$/, '\1')
      category = category.gsub(/_|\P{Word}/, '-').gsub(/-{2,}/, '-').downcase
      File.join(base_dir, category)
    end

  end
end
