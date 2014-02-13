# https://gist.github.com/ilkka/710577
module Jekyll
  class TagPage < Page
    include Convertible
    attr_accessor :site, :pager, :name, :ext
    attr_accessor :basename, :dir, :data, :content, :output

    def initialize(site, dir, tag, posts)
      @site = site
      @dir = dir
      @tag = tag
      @tag_dir_name = @tag.downcase # sanitize?
      self.ext = '.html'
      self.basename = 'index'
      self.content = <<-EOS
{% for post in page.posts %}
<h3>{{ post.date | date: "%A %d.%m." }} &mdash; <a href="{{ post.url }}">{{ post.title }}</a></h3>

<p>{{ post.content | truncatewords: 20 }}</p>

<p>
{% if post.categories != empty %}
In {{ post.categories | array_to_sentence_string }}.
{% endif %}
{% if post.tags != empty %}
Tagged {{ post.tags | array_to_sentence_string }}.
</p>
{% endif %}
{% endfor %}
EOS
      #self.content = File.read(File.join(site.config['source'], site.config['layouts'], 'tag.html'))
      #self.content = File.read(File.join(site.config['source'], '_includes', 'recent-post-listing.html'))
      self.content = <<-EOS
{% for post in page.posts %}
  <h3 class="no-bottom"><a href="{{ post.full_url }}">{{ post.title }}</a></h3>
  <h5 class="no-top">
    Posted on {{ post.date | format_date }}
    {% unless no_category %}
      {% if post.category %}
        in <a href="/{{ site.category_dir }}/{{ post.category | slugize }}">{{ post.category }}</a>
      {% endif %}
    {% endunless %}
  </h5>

  {{ post.content | postmorefilter: post.full_url, "Continue reading &raquo;", 75 }}
{% endfor %}
EOS
      self.data = {
        'layout' => 'default',
        'type' => 'tag',
        'title' => "Posts tagged #{@tag}",
        'posts' => posts
      }
    end

    def render(layouts, site_payload)
      payload = {
        'page' => self.to_liquid,
        'paginator' => pager.to_liquid
      }.deep_merge(site_payload)
      do_layout(payload, layouts)
    end

    def url
      File.join("/tags", @tag, "index.html")
    end

    def path
      return self.basename + self.ext
    end

    def to_liquid
      self.data.deep_merge({
        'url' => self.url,
        'content' => self.content
      })
    end

    def destination(dest)
      File.join('/', dest, @dir, @tag_dir_name, 'index.html')
    end

    #def write(dest_prefix, dest_suffix = nil)
    #  dest = dest_prefix
    #  dest = File.join(dest, dest_suffix) if dest_suffix
    #  path = File.join(dest, CGI.unescape(self.url))
    #  FileUtils.mkdir_p(File.dirname(path))
    #  File.open(path, 'w') do |f|
    #    f.write(self.output)
    #  end
    #end

    def html?
      true
    end
  end
end
