# Assorted Jekyll filters
#
# Sources:
#   https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/filters.rb
#   https://github.com/imathis/octopress/blob/master/plugins/octopress_filters.rb
#
require './_plugins/backtick_code_block'
require './_plugins/post_filters'
require './_plugins/raw'
#require './plugins/date'
#require 'rubypants'

module OctopressFilters
  include BacktickCodeBlock
  include TemplateWrapper
  def pre_filter(input)
    input = render_code_block(input)
    input.gsub /(<figure.+?>.+?<\/figure>)/m do
      safe_wrap($1)
    end
  end
  def post_filter(input)
    if input.nil?
      return input
    end
    input = unwrap(input)
    #RubyPants.new(input).to_html
  end
end

module Jekyll
  class ContentFilters < PostFilter
    include OctopressFilters
    def pre_render(post)
      if post.ext.match('html|textile|markdown|md|haml|slim|xml')
        post.content = pre_filter(post.content)
      end
    end
    def post_render(post)
      if post.ext.match('html|textile|markdown|md|haml|slim|xml')
        post.content = post_filter(post.content)
      end
    end
  end

  module Filters
    def slugize(text)
      text.slugize
    end

    def format_date(date)
      #"#{date.strftime('%B')} #{date.strftime('%d')}, #{date.strftime('%Y')}"
      #date.strftime('%B %d, %Y at %H:%M %z')
      date.utc.strftime('%B %d, %Y @ %R GMT')
    end

    def expand_urls(input, url='')
      url ||= '/'
      input.gsub /(\s+(href|src)\s*=\s*["|']{1})(\/[^\"'>]*)/ do
        $1+url+$3
      end
    end

    def full_url(input)
      site = @context.registers[:site]
      (site.config['url'] + input).gsub("//#{site.config['domain']}", "//blog.#{site.config['domain']}").gsub("/blog/", "/")
    end

    def length(obj)
      obj.length if obj.respond_to? :length
    end

    # Improved version of Liquid's truncate:
    # - Doesn't cut in the middle of a word.
    # - Uses typographically correct ellipsis (…) insted of '...'
    def truncate(input, length)
      if input.length > length && input[0..(length-1)] =~ /(.+)\b.+$/im
        $1.strip + ' &hellip;'
      else
        input
      end
    end

    # Improved version of Liquid's truncatewords:
    # - Uses typographically correct ellipsis (…) insted of '...'
    def truncatewords(input, length)
      truncate = input.split(' ')
      if truncate.length > length
        truncate[0..length-1].join(' ').strip + '&hellip;'
      else
        input
      end
    end

  end
end
