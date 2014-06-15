require 'spec_helper'

describe FidorStarterKits do

  describe '.exists?' do
    it 'is true' do
      expect( FidorStarterKits.exists?('sinatra_plain') ).to be
    end
    it 'is false' do
      expect( FidorStarterKits.exists?('sinatra_plain') ).to be
    end
  end

  describe '.path' do
    it 'exists' do
      expect( File.exists?( FidorStarterKits.path) ).to be
    end
  end

  describe '.build' do

    it 'creates zip file' do
      res = FidorStarterKits.build('sinatra_plain', '123', '12345', 'localhost')
      expect( File.exists?(res) ).to be
    end

    it 'replaces placeholders in example.rb' do
      res = FidorStarterKits.build('sinatra_plain', 'my-client-id', 'my-client-secret','my-app-url' ,'fidor-url')
      content = File.read(File.join(File.dirname(res), 'example.rb'))
      expect( content ).to include 'my-client-id'
      expect( content ).to include 'my-client-secret'
      expect( content ).to include 'fidor-url'
      expect( content ).to include 'my-app-url'
    end
  end
end