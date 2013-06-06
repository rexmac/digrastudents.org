# Custom page generator to pull pages from the _pages directory
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/custom_page.rb
#
module Jekyll
  class CustomPageGenerator < Generator
    safe false
    priority :lowest

    def generate(site)
      @site = site

      Dir.glob(File.join(site.source, '_pages', '**/*.md')).each do |file|
        generate_page file
      end
    end

    def generate_page(file)
      dir = File.dirname(file).gsub(@site.source, '')
      page = Page.new(@site, @site.source, dir, File.basename(file))

      # Override page directory
      page.dir = dir.gsub(/^\/_pages/, '')

      page.render(@site.layouts, @site.site_payload)
      page.write(@site.dest)
      @site.pages << page
    end
  end
end
