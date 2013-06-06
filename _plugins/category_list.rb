# Category list Liquid tag for Jekyll
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/category_list.rb
#
module Jekyll
  class CategoryList < Liquid::Tag
    safe = true

    def render(context)
      result = ""
      categories = context.registers[:site].categories

      categories.keys.each do |category|
        result << %(<a href="/blog/categories/#{category.slugize}"><strong>#{category}</strong></a> (#{categories[category].length})<br />)
      end

      result
    end
  end
end

Liquid::Template.register_tag('category_list', Jekyll::CategoryList)
