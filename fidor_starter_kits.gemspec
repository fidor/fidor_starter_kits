# coding: utf-8
lib = File.expand_path('../lib', __FILE__)
$LOAD_PATH.unshift(lib) unless $LOAD_PATH.include?(lib)
require 'fidor_starter_kits/version'

Gem::Specification.new do |spec|
  spec.name          = "fidor_starter_kits"
  spec.version       = FidorStarterKits::VERSION
  spec.authors       = ["Georg Leciejewski"]
  spec.email         = ["dev@fidortecs.de"]
  spec.summary       = %q{Starter Kits for building fidor apps.}
  spec.description   = %q{Fidor App examples for different languages }
  spec.homepage      = ""
  spec.license       = "MIT"

  spec.files         = `git ls-files -z`.split("\x0")
  spec.executables   = spec.files.grep(%r{^bin/}) { |f| File.basename(f) }
  spec.test_files    = spec.files.grep(%r{^(test|spec|features)/})
  spec.require_paths = ['lib']

  # spec.add_runtime_dependency "zipruby"
  spec.add_runtime_dependency "rubyzip"
  spec.add_development_dependency "bundler", "~> 1.5"
  spec.add_development_dependency "rake"
  spec.add_development_dependency 'rspec'
end
