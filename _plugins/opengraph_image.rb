# Title: OpenGraph image plugin for Jekyll
# Authors: Rex McConnell <rex@rexmac.com>
# Description: Output OpenGraph meta tags for a page thumbnail using an image URL
#
# Syntax {% opengraph_image http[s]://path/to/image %}
#
require 'fastimage'

module Jekyll
  class OpenGraphImageTag < Liquid::Tag
    @markup = nil

    def initialize(tag_name, markup, tokens)
      @markup = markup
      super
    end

    def render(context)
      if @markup =~ /([\w]+(?:\.[\w]+)*)/
        @markup = lookup(context, $1)
      end

      if @markup =~ /(?<url>http(?<secure>s)?\S+)/i
        img = {}
        img['url'] = $~['url']
        if $~['secure']
          img['secure_url'] = $~['url']
        end

        size = FastImage.size(img['url'])
        type = FastImage.type(img['url'])
        if !size.nil?
          img['width'] = size[0]
          img['height'] = size[1]
        end
        if !type.nil?
          img['mimetype'] = "image/#{type}"
        end

        out = "<meta property=\"og:image\" content=\"#{img['url']}\">"
        if img['secure_url']
          out += "<meta property=\"og:image:secure_url\" content=\"#{img['secure_url']}\">"
        end
        out += "<meta property=\"og:image:type\" content=\"#{img['mimetype']}\">"
        out += "<meta property=\"og:image:width\" content=\"#{img['width']}\">"
        out += "<meta property=\"og:image:height\" content=\"#{img['height']}\">"
        out
      else
        throw "Error processing input, expected syntax {% opengraph_image http[s]://path/to/image %}, but was given #{@markup}"
      end
    end

    private
      def lookup(context, name)
        lookup = context
        name.split(".").each do |value|
          lookup = lookup[value]
        end

        lookup
      end
  end
end

Liquid::Template.register_tag('opengraph_image', Jekyll::OpenGraphImageTag);
