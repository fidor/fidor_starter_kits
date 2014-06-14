require 'spec_helper'

describe FidorStarterKits do

  context 'path' do
    it 'exists' do
      expect( File.exists?( FidorStarterKits.path) ).to be
    end
  end

  context 'build' do

    it 'creates zip file' do
      res = FidorStarterKits.build('sinatra_plain', '123', '12345', 'localhost')
      expect( File.exists?(res) ).to be
    end

    it 'replaces placeholders in example.rb' do
      res = FidorStarterKits.build('sinatra_plain', 'my-client-id', 'my-client-secret', 'fidor-url')
      content = File.read(File.join(File.dirname(res), 'example.rb'))
      expect( content ).to include 'my-client-id'
      expect( content ).to include 'my-client-secret'
      expect( content ).to include 'fidor-url'
    end
  end
end