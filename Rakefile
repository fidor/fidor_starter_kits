require 'bundler/gem_tasks'
require 'rspec'
require 'rspec/core/rake_task'

desc 'Run specs'
RSpec::Core::RakeTask.new
task :default => :spec
<<<<<<< HEAD

# override push to prevent public release
module Bundler
  class GemHelper
    def rubygem_push(path)
      Bundler.ui.confirm "No Push to rubygems.org .. this is a CLOSED SOURCE lib and you will get sued!"
    end
  end
end
Bundler::GemHelper.install_tasks
=======
>>>>>>> fidor/master
