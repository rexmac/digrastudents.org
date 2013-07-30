require 'yaml'
require 'closure-compiler'
require 'colorize'
require 'digest'
require 'fileutils'

ENV['JEKYLL_ENV'] = ENV['JEKYLL_ENV'] ? ENV['JEKYLL_ENV'] : "production"
CONFIG = YAML.load_file("_config.yml")

dest_dir   = CONFIG['destination'] || "_site"
src_dir = CONFIG['source'] || "."
js_cache_dir = ".js-cache"
rsync_params = "-cvzr #{dest_dir}/ #{CONFIG['rsync']['user']}@#{CONFIG['rsync']['host']}:#{CONFIG['rsync']['path']}"

def ask(message, valid_options)
  if valid_options
    answer = get_stdin("#{message} #{valid_options.to_s.gsub(/"/, '').gsub(/, /,'/')} ") while !valid_options.include?(answer)
  else
    answer = get_stdin(message)
  end
  answer
end

def cache_js(content, cache_dir, output_file)
  hash = Digest::SHA256.hexdigest(content)
  vmessage "  Hash: ".colorize(:cyan) + hash
  cache_file = File.join(cache_dir, hash + ".js")

  # Is there a cached version of the file?
  if File.readable?(cache_file)
    vmessage "  Cached file exists".colorize(:cyan)
  else
    vmessage "  Cached file does NOT exist; creating now...".colorize(:cyan)
    # Run content through Google Closure Compiler and cache results
    begin
      content = Closure::Compiler.new.compile(content)
    rescue Closure::Error => e
      message "  Failed to minimize JS file, #{output_file}:".colorize(:red)
      message "    " + e.message.gsub(/\n/, "\n  ")
      raise e
    else
      File.open(cache_file, 'w') do |f|
        f.write(content)
      end
      vmessage "  File cached as ".colorize(:cyan) + cache_file
    end
  end

  cache_file
end

def copy_file_with_path(src, dest)
  FileUtils.mkdir_p(File.dirname(dest)) unless File.directory?(File.dirname(dest))
  FileUtils.cp(src, dest)
end

def get_stdin(message)
  print message
  STDIN.gets.chomp
end

def message(msg, nl = true)
  print msg + (nl ? "\n" : "") unless Rake.verbose == false || Rake.application.options.silent == true
end

def vmessage(msg, nl = true, alt = nil)
  if Rake.verbose == true
    print msg + (nl ? "\n" : "")
  elsif alt != nil
    message alt, nl
  end
end

def which(cmd)
  exts = ENV['PATHEXT'] ? ENV['PATHEXT'].split(';') : ['']
  ENV['PATH'].split(File::PATH_SEPARATOR).each do |path|
    exts.each { |ext|
      exe = "#{path}/#{cmd}#{ext}"
      return exe if File.executable? exe
    }
  end
  return nil
end

task :default => :build

desc "Clean out caches and public directory"
task :clean do
  message "Cleaning caches and public directory...", false
  #rm_rf ["#{dest_dir}/**", ".pygments-cache/**", ".sass-cache/**", "#{js_cache_dir}/**"]
  rm_rf [Dir.glob("#{dest_dir}/*"), Dir.glob(".pygments-cache/*"), Dir.glob(".sass-cache/*"), Dir.glob("#{js_cache_dir}/*")], {:verbose => Rake.verbose == true}
  message "done".colorize(:green) + "."
end

desc "Build website"
task :build do
  message "Building website..."
  Rake::Task["css"].invoke
  Rake::Task["js"].invoke
  if !which("jekyll")
    message "failed".colorize(:red) + ". - jekyll executable not found"
  else
    #system "jekyll" + ENV['JEKYLL_ENV'] !== 'development' ? " > /dev/null 2>&1"
    system "jekyll"
    message "done".colorize(:green) + '.'
  end
end

desc "Create a new post; extra arguments will form the post's title"
task :new do
  throw "No title given" unless ARGV[1]
  title = ""
  ARGV[1..ARGV.length - 1].each { |v| title += " #{v}" }
  title.strip!
  now = Time.now
  path = "#{src_dir}/_posts/#{now.strftime('%F')}-#{title.downcase.gsub(/[\s\.]/, '-').gsub(/[^\w\d\-]/, '')}.md"
  if File.exist?(filename)
    abort("rake aborted!") if ask("#{path} already exists. Do you want to overwrite?", ['y', 'n']) == 'n'
  end

  File.open(path, "w") do |f|
    f.puts "---"
    f.puts "layout: post"
    f.puts "title: \"#{title}\""
    f.puts "date: #{now.strftime('%F %T')}"
    f.puts "category: "
    f.puts "tags:"
    f.puts "  - "
    f.puts "---"
    f.puts ""
  end

  `open -a "Sublime Text 2" #{path}`
  exit
end

desc "Generate stylesheets; runs 'compass compile'"
task :css do
  message "Generating stylesheets...", false
  if !which("compass")
    message "failed".colorize(:red) + ". - compass executable not found"
  else
    system "compass clean -q"
    system "compass compile -q"
    message "done".colorize(:green) + '.'
  end
end

desc "Generate JavaScript; concatenates and minifies JS files according to 'js' setting in _config.yml"
task :js do
  message "Generating javascript...", false
  begin
    # Create js_cache_dir if it doesn't exist
    FileUtils.mkdir_p(js_cache_dir) unless File.directory?(js_cache_dir)

    run_closure = false
    processed = Array.new
    # Get configuration
    if CONFIG.key?('js')
      config = CONFIG['js']
      vmessage "Read config: ".colorize(:green) + config.to_s, true
      config.each {|files|
        files.each {|output_file, input_files|
          output_file = File.join(src_dir, "js", output_file)

          vmessage "Output file: ".colorize(:cyan) + output_file.to_s

          # Concatenate input files
          content = ''
          input_files.each {|file|
            file = File.join(src_dir, "_js", file)
            processed.push(file)
            vmessage "  Input file: ".colorize(:cyan) + file
            unless File.readable?(file)
              #message "Failed to read file ".colorize(:red) + file
              raise "Failed to read file ".colorize(:red) + file
            end

            content.concat(File.read(file))
          }

          # Cache concatenated JS
          cached_file = cache_js(content, js_cache_dir, output_file)

          # Copy cached file to destination directory
          copy_file_with_path(cached_file, output_file)
        }
      }
    else
      raise "No config found!".colorize(:red)
    end

    # Minfiy all JS files with names beginning with an underscore character that were not processed above
    a = Dir.glob(File.join(src_dir, '_js', '**/_*.js')).select {|file|
      processed.index(file).nil?
    }
    a.each {|file|
      vmessage "Minifying file: ".colorize(:cyan) + file
      processed.push(file)
      cached_file = cache_js(File.read(file), js_cache_dir, file)
      output_file = File.join(src_dir, 'js', File.basename(file, '.js').gsub(/^_/, '') + '.min.js')
      vmessage "  Writing minified file to: ".colorize(:cyan) + output_file
      copy_file_with_path(cached_file, output_file)
    }

    # Copy any remaining JS files that were not processed above
    a = Dir.glob(File.join(src_dir, '_js', '**/*.*')).select {|file|
      processed.index(file).nil?
    }
    a.each {|file|
      vmessage "Copying file: ".colorize(:cyan) + file
      output_file = File.join(File.dirname(file).gsub(/^#{File.join(src_dir, '_js')}/, File.join(src_dir, 'js')), File.basename(file))
      vmessage "  To: ".colorize(:cyan) + output_file
      copy_file_with_path(file, output_file)
    }
  rescue RuntimeError => e
    message e.message
  else
    message "done".colorize(:green) + '.'
  end
end

desc "Deploy website to server"
task :deploy do
  if ARGV[1] && ARGV[1] == 'test'
    rsync_params = "-n " + rsync_params
  end

  message "Deploying to remote server...", false
  begin
    if !which("rsync")
      raise "rsync executable not found"
    elsif rsync_params.nil?
      raise "no rsync settings found"
    else
      system "rsync #{rsync_params}"
    end
  rescue
    message "failed".colorized(:red) + ". - #{e.message}"
  else
    message "done".colorize(:green) + '.'
  end

  exit
end
