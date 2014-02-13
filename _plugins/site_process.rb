# Monkeypatch(?) of Jekyll Site::process
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/site_process.rb
#
require 'growl'

module Jekyll
  class Site
    def process
      self.reset
      self.read

      # Remove uncompiled JS files from list of static files
      if self.config.key?('jsmincat')
        self.config['jsmincat'].each {|f|
          self.static_files.select! {|s|
            if s.class == StaticFile
              s.path != File.join(self.source, f)
            else
              true
            end
          }
        }
      end

      self.generate
      self.render

      # these must come after render
      self.generate_archives
      #self.generate_projects

      self.cleanup
      self.write

      # Growl
      Growl.notify "Build complete.", :title => "Jekyll"
    end
  end
end
