#!/usr/bin/perl
# Compress HTML by striping whitespace
#
# Source: https://groups.google.com/d/msg/nanoc/XMDnYmjGU1M/UQ4dsFyVYPgJ
#
use warnings;
use strict;

my $count_pre  = 0;

while(<>) {
  #if($_ =~ m|<pre[^>]*>|)   { $count_pre  ++; }
  #if($_ =~ m|</pre>|)  { $count_pre  --; }

  #if($_ =~ m|<script|)   { $count_pre  ++; }
  #if($_ =~ m|</script>|)  { $count_pre  --; }

  if($_ =~ m/<(?:pre|script|style|textarea)[^>]*>/)   { $count_pre++; }
  if($_ =~ m/<\/(?:pre|script|style|textarea)>/)   { $count_pre--; }

  # If we're inside any number of <pre> or <script> tags, don't strip whitespace
  if(0 == $count_pre) {
    #print "\nNO PRE\n";
    #print "\nLINE: '$_'\n\n";
    $_ =~ s/[\r\n]/ /g; # convert line endings into spaces
    # reduce serial whitespace to 1 character, unless contained within a <span class="line"> element
    $_ =~ s/(?!<span class="line">)(.*)\s+(.*)(?!<\/span>)/$1 $2/g;
    $_ =~ s/>\s+</> </g; # strip whitespace between tags
    $_ =~ s/>\s+$/>/g;  # strip whitespace after tags
    $_ =~ s/^\s+</</g;  # strip whitespace before tags
    $_ =~ s/^\s*$//;    # strip empty lines
  } else {
    # If we're inside any number of <pre> tags, strip whitespace before the tag
    #$_ =~ s/^\s+<pre>/<pre>/;
    #$_ =~ s/^\s+<script/<script/;
    $_ =~ s/^\s+<(pre|script|style|textarea)/<$1/;
  }

  # print the result
  print;
}
