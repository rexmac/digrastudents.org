# Tag cloud generator plugin for Jekyll
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/tag_cloud.rb
#
module Jekyll
  class TagCloudTag < Liquid::Tag
    @@max_size = 280
    @@min_size = 75
    safe = true

    def render(context)
      @@cloud ||= generate_cloud(context)
    end

    private

      def generate_cloud(context)
        site = context.registers[:site]

        tags = site.tags.map do |tag|
          {
            :title => tag[0],
            :slug => tag[0].slugize,
            :posts => tag[1]
          }
        end

        if tags.length == 0
          return ''
        end

        tags.sort! { |a, b| a[:title] <=> b[:title] }
        min_count = tags.min { |a, b| a[:posts].length <=> b[:posts].length }[:posts].length
        max_count = tags.max { |a, b| a[:posts].length <=> b[:posts].length }[:posts].length
        diff = max_count - min_count

        weights = tags.inject({}) do |result, tag|
          result[tag[:title]] = (tag[:posts].length - min_count) * (@@max_size - @@min_size) / (diff.nonzero? || 1) + @@min_size
          result
        end

        tags.inject("") do |html, tag|
          length = tag[:posts].length

          url = ("#{site.config['url']}/#{site.config['tag_dir']}/#{tag[:slug]}/").gsub("//#{site.config['domain']}", "//blog.#{site.config['domain']}").gsub("/blog/", "/")
          html << %(
            <span style="font-size: #{weights[tag[:title]].to_i}%">
              <a href="#{url}" title="#{length} post#{"s" if length != 1}">#{tag[:title]}</a>
            </span>&nbsp;
          )

          #html = 'min_count: ' + min_count.to_s + '<br>' + 'max_count: ' + max_count.to_s

          html
        end
      end
  end
end

Liquid::Template.register_tag('tag_cloud', Jekyll::TagCloudTag)
