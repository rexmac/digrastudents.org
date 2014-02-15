# encoding: utf-8
#
# https://github.com/MattHall/truncatehtml
#
# (The MIT License)
#
# Copyright (c) 2011 Matt Hall
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the 'Software'), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
 #copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
#
require 'rubygems'
require 'nokogiri'

module Jekyll
  module TruncateHTMLFilter

    def truncatehtml(raw, max_length = 15, continuation_string = "...")
     doc = Nokogiri::HTML.fragment(String.encode('UTF-8', :invalid => :replace, :undef => :replace, :replace => ''))
      current_length = 0
      deleting = false
      to_delete = []

      depth_first(doc.children.first) do |node|

        if !deleting && node.class == Nokogiri::XML::Text
          current_length += node.text.length
        end

        if deleting
          to_delete << node
        end

        if !deleting && current_length > max_length
          deleting = true

          trim_to_length = current_length - max_length + 1
          node.content = node.text[0..trim_to_length] + continuation_string
          #node.content = node.text[0..trim_to_length] + Nokogiri::HTML.parse(continuation_string)
        end
      end

      to_delete.map(&:remove)

      doc.inner_html
    end

    def truncatehtmlwords(raw, max_length = 15, continuation_string = "...")
     doc = Nokogiri::HTML.fragment(raw.encode('UTF-8', :invalid => :replace, :undef => :replace, :replace => ''))
      current_length = 0
      deleting = false
      to_delete = []

      depth_first(doc.children.first) do |node|

        if !deleting && node.class == Nokogiri::XML::Text
          current_length += node.text.count(' ') + 1
        end

        if deleting
          to_delete << node
        end

        if !deleting && current_length > max_length
          deleting = true

          w = []
          node.text.scan(/ /) do |c|
            w << Regexp.last_match.offset(0).first
          end
          if w.length > 0
            trim_to_length = w[current_length - max_length - 1] - 1
            node.content = node.text[0..trim_to_length] + continuation_string
          end
        end
      end

      to_delete.map(&:remove)

      doc.inner_html
    end

  private

    def depth_first(root, &block)
      parent = root.parent
      sibling = root.next
      first_child = root.children.first

      yield(root)

      if first_child
        depth_first(first_child, &block)
      else
        if sibling
          depth_first(sibling, &block)
        else
          # back up to the next sibling
          n = parent
          while n && n.next.nil? && n.name != "document"
            n = n.parent
          end

          # To the sibling - otherwise, we're done!
          if n && n.next
            depth_first(n.next, &block)
          end
        end
      end
    end

  end
end

Liquid::Template.register_filter(Jekyll::TruncateHTMLFilter)
