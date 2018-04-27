# coding: utf-8
lib = File.expand_path('../lib', __FILE__)
$LOAD_PATH.unshift(lib) unless $LOAD_PATH.include?(lib)
require 'fidor_starter_kits/version'

Gem::Specification.new do |spec|
  spec.name          = "fidor_starter_kits"
  spec.version       = FidorStarterKits::VERSION
  spec.authors       = ["Fidor Solutions AG"]
  spec.email         = ["connect@fidor.com"]
  spec.summary       = %q{BETA - Starter Kits for building fidor apps.}
  spec.description   = %q{Fidor application examples for different languages. They rely on the BETA Version of the API so things may change.}
  spec.homepage      = ""
  spec.license       = "MIT"

  spec.files         = `git ls-files -z`.split("\x0")
  spec.executables   = spec.files.grep(%r{^bin/}) { |f| File.basename(f) }
  spec.test_files    = spec.files.grep(%r{^(test|spec|features)/})
  spec.require_paths = ['lib']

  spec.add_runtime_dependency "rubyzip"
  spec.add_development_dependency "bundler", "~> 1.16"
  spec.add_development_dependency "rake"
  spec.add_development_dependency 'rspec'
  spec.add_development_dependency 'byebug'
end
