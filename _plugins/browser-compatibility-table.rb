# Jekyll plugin for displaying browser compatibility table
#
# by Rex McConnell <rex@rexmac.com> (http://rexmac.com/)
#
# My Ruby-Fu is extremely weak. I'm sure this code be vastly improved.
#
# Format:
#  {% browsercompat browser1[:X:Y] [..., browserN:X:Y] %}
#
#  Where X is an optional comma-separated list of supported versions,
#  and Y is an optional comma-separated list of unsupported versions.
#
#  If X and Y are not included, then support is assumed for ALL versions of that browser.
#
# Example usage:
#   The following would display a table showing support for all versions of Google Chrome
#   and Mozilla Firefox versions 8 and 9, but no support for Firefox version 7.
#
#   {% browsercompat chrome firefox:8,9:7 %}
#
#   The following would display a table showing support for version 26 of Chrome
#   and versions 8 and 9 of Firefox, but no support for Chrome versions 24 and 25
#   and Firefox version 7.
#
#   {% browsercompat chrome:26:25,24 firefox:8,9:7 %}
#
#   More of the same...
#
#   {% browsercompat chrome:26 firefox:20 ie:10 safari:5 opera:12 %}
#
#   To show only non-support versions, simply leave the X field blank:
#
#   {% browsercompat chrome::25 firefox::20 %}
#
module Jekyll
  class BrowserCompatibilityTable < Liquid::Tag
    #def initialize(tag_name, *browsers)
    def initialize(tag_name, markup, tokens)
      super
      #puts "Markup: #{markup}"
      @browsers = {}
      markup.split.each_with_index do |val, index|
        #puts "#{index} => #{val}"
        if val =~ /([^:]+)(?::([^:]*)(?::([^:]*))?)?/
          #puts "MATCH: #{$1} #{$2} #{$3}"
          unless $1.nil?
              @browsers[$1] = {
                  'versions' => $2.nil? ? $3.nil? ? ['all'] : $3.split(',') : $2.split(',') + ($3.nil? ? [] : $3.split(',')),
                  'pass' => []
              }
              @browsers[$1]["versions"] = @browsers[$1]["versions"].sort_by(&:to_f)
              @browsers[$1]["versions"].each_with_index do |version, index|
                if $2.nil? || $2.include?(version)
                  @browsers[$1]["pass"][index] = true
                end
                if !$3.nil? && $3.include?(version)
                  @browsers[$1]["pass"][index] = false
                end
              end
          end
        end
      end
      #puts @browsers
    end

    def render(context)
      headers = ''
      versions = ''
      browser_versions = []

      @browsers.each do |browser, hash|
        browser_versions = []
        headers += "<td><i class=\"icon-browser icon-browser-#{browser}\"></i></td>"
        hash["versions"].each_with_index do |version, index|
          browser_versions.push("<span class=\"#{hash["pass"][index] ? 'pass' : 'fail'}\">#{version}</span>")
        end
        versions += '<td>' + browser_versions.join(', ') + '</td>'
      end
      #html = '<table class="browser-compatibility"><thead><tr>'
      #html += '<td><a href="http://www.google.com/chrome/" title="Google Chrome"><i class="icon-browser icon-browser-chrome"></i></a></td>'
      #html += '<td><a href="http://www.mozilla.com/firefox/" title="Mozilla Firefox"><i class="icon-browser icon-browser-firefox"></i></a></td>'
      #html += '<td><a href="http://windows.microsoft.com/en-US/internet-explorer" title="Microsoft Internet Explorer"><i class="icon-browser icon-browser-ie"></i></a></td>'
      #html += '<td><a href="http://www.apple.com/safari/" title="Apple Safari"><i class="icon-browser icon-browser-safari"></i></a></td>'
      #html += '<td><a href="http://www.opera.com/" title="Opera Software - Opera Web Browser"><i class="icon-browser icon-browser-opera"></i></a></td>'
      #html += '</tr></thead><tbody><tr>' + versions + '</tr></tbody></table>'
      #%(#{html})
      %(<table class="browser-compatibility"><thead><tr>#{headers}</tr></thead><tbody><tr>#{versions}</tr></tbody></table>)
    end
  end
end

Liquid::Template.register_tag('browsercompat', Jekyll::BrowserCompatibilityTable)
