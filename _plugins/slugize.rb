# Add 'slugize' method to String class
#
# Original: https://github.com/BlackBulletIV/blackbulletiv.github.com/blob/master/_plugins/core_ext.rb
#
class String
  def slugize
    self.downcase.gsub(/[\s\.]/, '-').gsub(/[^\w\d\-]/, '')
  end
end
