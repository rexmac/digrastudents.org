# https://gist.github.com/ilkka/710577
module Jekyll
  class TagPage < Page
    include Convertible
    attr_accessor :site, :pager, :name, :ext
    attr_accessor :basename, :dir, :data, :content, :output

    # Initializes a new TagPage
    #
    #  site          - The Site object.
    #  base          - The String path to the source.
    #  dir           - The String path between the source and the file.
    #  name          - The String filename of the generated file.
    #  tag           - The String tag currently being processed.
    #  posts         - The Array of posts tagged with this tag.
    def initialize(site, base, dir, name, tag, posts)
      @site = site
      @base = base
      @dir = dir
      @name = name
      @tag = tag
      #@tag_dir_name = @tag.downcase # sanitize?
      template_path = File.join(base, site.config['layouts'], 'tag.html')
      self.process(name)
      if File.exists?(template_path)
        @perform_render = true
        self.read_yaml(File.dirname(template_path), File.basename(template_path))

        title_prefix = site.config['tag_title_prefix'] || 'Posts tagged '
        meta_description_prefix = site.config['tag_meta_description_prefix'] || 'Posts tagged '
        self.data = {
          'tag'         => tag,
          'type'        => 'tag',
          'title'       => "#{title_prefix}#{tag}",
          'description' => "#{meta_description_prefix}#{tag}",
          'posts'       => posts
        }.deep_merge(self.data)
      else
        throw "No 'tag_index' layout found."
        @perform_render = false
      end
    end
  end
end
