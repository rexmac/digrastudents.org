# Extension of Jekyll markdown converter.
#
# Add support for the following:
#   - replace known abbreviations (defined in _abbreviations.yml) with abbr elements
#   - replace 'gist #n' with link to gist (from https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/markdown_ext.rb)
#

module Jekyll
  class MarkdownConverter
    alias :old_convert :convert
    @@abbr_dict = nil

    def convert(content)
      # Abbreviations
      # Inspired by https://github.com/kogakure/jekyll-plugin-abbr
      if @@abbr_dict.nil?
        puts "Loading abbreviations..."
        @@abbr_dict = YAML.load(File.open(File.join(@config['source'], "_abbreviations.yml")))
      end
      if @@abbr_dict != false
        @@abbr_dict.each do |abbr, title|
          #content.gsub!(/\b#{abbr}\b(?![^">]+">|<\/abbr>|">)/, "<abbr title=\"#{title}\">#{abbr[/[A-Za-z0-9\.\s\;\&]+/]}</abbr>")
          content.gsub!(/\b#{abbr}(s)?\b(?![^">]+">|<\/abbr>|">)/, "<abbr title=\"#{title}\">#{abbr}</abbr>\\1")
        end
      end

      # This adds the gist #1 syntax.
      old_convert content.gsub(/gist #(\d+)/, '[gist #\1](https://gist.github.com/\1)')
    end
  end
end
