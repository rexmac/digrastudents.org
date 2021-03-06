# Jekyll plugin for embedding Youtube videos
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/youtube.rb
#
module Jekyll
  class Youtube < Liquid::Tag
    @@width = 640
    @@height = 385

    def initialize(name, id, tokens)
      super
      @id = id
    end

    def render(context)
      %(<p class="text-center"><iframe class="youtube" width="#{@@width}" height="#{@@height}" src="http://www.youtube.com/embed/#{@id}" frameborder="0" allowfullscreen></iframe></p>)
    end
  end
end

Liquid::Template.register_tag('youtube', Jekyll::Youtube)
