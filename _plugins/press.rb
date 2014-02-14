# Jekyll plugin to compress HTML, CSS, and JS files
#
# Original: https://github.com/stereobooster/jekyll_press/
#
require 'closure-compiler'
require 'sass'

module Jekyll
  module Compressor
    def output_file(dest, content)
      FileUtils.mkdir_p(File.dirname(dest))
      File.open(dest, 'w') do |f|
        f.write(content)
      end
    end

    def output_html(path, content)
      if !path.include? "feed.xml"
        #puts "Pressing HTML: " + path
        content = open('|/usr/bin/perl _plugins/press.pl', 'r+') do |io|
          io.write(content)
          io.close_write
          io.read
        end
      end
      self.output_file(path, content)
    end

    def output_css(path, content)
      print "Attempting to minify #{path}..."
      #self.output_file(path, Less::Parser.new.parse(content).to_css(:compress => true))
      self.output_file(path, Sass::Engine.new(content, :syntax => :scss, :style => :compressed).render)
      puts "done."
    rescue Exception => e
      puts "failed. - error occurred: #{e.message.strip}"
      puts "copying initial file"
      self.output_file(path, content)
    end

    def output_js(path, content)
      print "Attempting to minify #{path}..."
      self.output_file(path, Closure::Compiler.new.compile(content))
      puts "done."
    rescue Closure::Error => e
      puts "failed. - error occurred: #{e.message.strip}"
      puts "copying initial file"
      self.output_file(path, content)
    end
  end

  class Post
    include Compressor

    def write(dest)
      dest_path = self.destination(dest)
      self.output_html(dest_path, self.output)
    end
  end

  class Page
    include Compressor

    def write(dest)
      dest_path = self.destination(dest)
      self.output_html(dest_path, self.output)
    end
  end

  class StaticFile
    include Compressor

    def copy_file(path, dest_path)
      FileUtils.mkdir_p(File.dirname(dest_path))
      FileUtils.cp(path, dest_path)
    end

    def write(dest)
      dest_path = self.destination(dest)

      return false if File.exist?(dest_path) and !self.modified?
      @@mtimes[path] = mtime

      #case File.extname(dest_path)
      #when '.js'
      #  puts "JS: " + path + " " + dest_path
      #  if dest_path =~ /.min.js$/
      #    copy_file(path, dest_path)
      #  else
      #    self.output_js(dest_path, File.read(path))
      #  end
      #when '.css'
      #  puts "CSS: " + path + " " + dest_path
      #  if dest_path =~ /.min.css$/
      #    copy_file(path, dest_path)
      #  else
      #    self.output_css(dest_path, File.read(path))
      #  end
      #else
        #puts "Other: " + path + " " + dest_path
        copy_file(path, dest_path)
      #end

      true
    end
  end
end
