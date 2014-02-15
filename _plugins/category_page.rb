module Jekyll
  class CategoryPage < Page
    include Convertible
    attr_accessor :site, :pager, :name, :ext
    attr_accessor :basename, :dir, :data, :content, :output

    #  Initializes a new CategoryPage
    #
    #  site          - The Site object.
    #  template_path - The String path to the layout template to use.
    #  base          - The String path to the source.
    #  dir           - The String path between the source and the file.
    #  name          - The String filename of the generated file.
    #  category      - The String category currently being processed.
    #  posts         - The Array of posts in the category.
    def initialize(site, template_path, base, dir, name, category, posts)
      @site = site
      @base = base
      @dir = dir
      @name = name
      @category = category

      self.process(name)
      if File.exist?(template_path)
        @perform_render = true
        self.read_yaml(File.dirname(template_path), File.basename(template_path))

        title_prefix = site.config['category_title_prefix'] || 'Category: '
        meta_description_prefix = site.config['category_meta_description_prefix'] || 'Category: '
        self.data = {
          'category'    => category,
          'type'        => 'category',
          'title'       => "#{title_prefix}#{category}",
          'description' => "#{meta_description_prefix}#{category}",
          'posts'       => posts
        }.deep_merge(self.data)
      else
        throw "No layout found: #{template_path}"
        @perform_render = false
      end
    end

    def render?
      @perform_render
    end

  end

  class CategoryIndexPage < CategoryPage
    #  Initializes a new CategoryIndexPage
    #
    #  site          - The Site object
    #  base          - The String path to the source
    #  dir           - The String path between the source and the file.
    #  category      - The String category currently being processed
    #  posts         - The Array of posts in the category
    def initialize(site, base, dir, category, posts)
      template_path = File.join(base, site.config['layouts'], 'category.html')
      super(site, template_path, base, dir, 'index.html', category, posts)
    end
  end

  class CategoryIndexFeedPage < CategoryPage
    #  Initializes a new CategoryIndexFeedPage
    #
    #  site          - The Site object
    #  base          - The String path to the source
    #  dir           - The String path between the source and the file.
    #  category      - The String category currently being processed
    #  posts         - The Array of posts in the category
    def initialize(site, base, dir, category, posts)
      template_path = File.join(base, site.config['layouts'], 'category_feed.xml')
      super(site, template_path, base, dir, 'feed.xml', category, posts)

      # Set the correct feed URL
      self.data['feed_url'] = "#{dir}/#{name}" if render?
    end
  end
end
