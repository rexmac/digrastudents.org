# Modified BacktickCodeBlock plugin for Jekyll from Octopress
#
# Modifications:
#   - Optional line numbers via appending ':noln' to language option
#   - Resets options for each block
#
# Original: https://github.com/imathis/octopress/blob/master/plugins/backtick_code_block.rb
#
# Syntax:
# ``` [language[:noln]] [title] [url] [link text]
# code snippet
# ```
#
require './_plugins/pygments_code'

module BacktickCodeBlock
  include HighlightCode
  AllOptions = /([^\s]+)\s+(.+?)(https?:\/\/\S+)\s*(.+)?/i
  LangCaption = /([^\s]+)\s*(.+)?/i
  def render_code_block(input)
    options = nil
    caption = nil
    lang = nil
    url = nil
    title = nil
    include_line_numbers = nil

    input.gsub(/^`{3} *([^\n]+)?\n(.+?)\n`{3}/m) do
      options = $1 || ''
      str = $2
      caption = nil
      lang = nil
      url = nil
      title = nil
      include_line_numbers = true

      if options =~ AllOptions
        lang = $1
        caption = "<figcaption><span>#{$2.rstrip()}</span><a href='#{$3}'>#{$4 || 'link'}</a></figcaption>"
      elsif options =~ LangCaption
        lang = $1
        caption = "<figcaption><span>#{$2.rstrip()}</span></figcaption>"
      end

      if(lang.match(/^(.*?):noln$/))
        include_line_numbers = false
        lang = $1
      end

      if str.match(/\A( {4}|\t)/)
        str = str.gsub(/^( {4}|\t)/, '')
      end
      if lang.nil? || lang == '' || lang == 'plain'
        code = tableize_code(str.gsub('<','&lt;').gsub('>','&gt;'), 'plain', include_line_numbers)
        "<figure class='code'>#{caption}#{code}</figure>"
      else
        if lang.include? "-raw"
          raw = "``` #{options.sub('-raw', '')}\n"
          raw += str
          raw += "\n```\n"
        else
          code = highlight(str, lang, include_line_numbers)
          "<figure class='code'>#{caption}#{code}</figure>"
        end
      end
    end
  end
end
