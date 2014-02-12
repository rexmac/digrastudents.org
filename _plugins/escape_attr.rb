module Jekyll
  module EscapeHtmlAttribute
    def escape_attr(input)
      input.gsub(/&/, "&amp;").gsub(/</, "&lt;").gsub(/>/, "&gt;").gsub(/"/, "&quot;")
    end
  end
end

Liquid::Template.register_filter(Jekyll::EscapeHtmlAttribute)
