# Category list Liquid tag for Jekyll
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/category_list.rb
#
module Jekyll
  class CategoryList < Liquid::Tag
    safe = true

    def render(context)
      result = ""
      site = context.registers[:site]
      categories = site.categories

      categories.keys.each do |category|
        result << %(<a href="/#{site.config['category_dir']}/#{category.slugize}"><strong>#{category}</strong></a> (#{categories[category].length})<br />)
      end

      result
    end
  end
end

Liquid::Template.register_tag('category_list', Jekyll::CategoryList)
