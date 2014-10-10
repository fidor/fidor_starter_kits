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
      opts = {
        app_name: 'sinatra_plain',
        client_id: '123',
        client_secret: '12345',
        app_url: 'localhost'
      }
      res = FidorStarterKits.build(opts)
      expect( File.exists?(res) ).to be
    end

    it 'replaces placeholders in example.rb' do
      opts = {
        app_name: 'sinatra_plain',
        client_id: 'my-client-id',
        client_secret: 'my-client-secret',
        app_url: 'my-app-url',
        fidor_oauth_url: 'fidor-oauth-url',
        fidor_api_url: 'fidor-api-url'
      }
      res = FidorStarterKits.build(opts)
      content = File.read(File.join(File.dirname(res), 'example.rb'))
      expect( content ).to include 'my-client-id'
      expect( content ).to include 'my-client-secret'
      expect( content ).to include 'my-app-url'
      expect( content ).to include 'fidor-api-url'
      expect( content ).to include 'fidor-oauth-url'
    end
  end
end