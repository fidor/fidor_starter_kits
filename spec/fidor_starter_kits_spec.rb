require 'spec_helper'

describe FidorStarterKits do

  describe '.exists?' do
    it 'is true' do
      expect( FidorStarterKits.exists?('ruby_oauth_plain') ).to be
    end
    it 'is false' do
      expect( FidorStarterKits.exists?('ruby_oauth_plain') ).to be
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
        app_name: 'ruby_oauth_plain',
        client_id: '123',
        client_secret: '12345',
        app_url: 'localhost'
      }
      res = FidorStarterKits.build(opts)
      expect( File.exists?(res) ).to be
    end

    it 'replaces placeholders in example.rb' do
      opts = {
        app_name: 'ruby_oauth_plain',
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

  describe '.all' do
    it 'lists all starter kits' do
      expect(FidorStarterKits.all.count).to eq(FidorStarterKits::STARTER_KITS.size)
    end

    it 'loads the json meta data' do
      conf = FidorStarterKits.all
      expect(conf["ruby_on_rails_api_demo"]["display_name"]).to eq("Ruby on Rails Fidor API Demo Application")
      expect(conf["golang_transactions"]["display_name"]).to eq("Go Transactions")
      expect(conf["node_tx"]["description"]).to eq("NodeJS app getting transactions and accounts. Handling oAuth login.")
      expect(conf["php_oauth_plain"]["app_name"]).to eq("php_oauth_plain")
      expect(conf["ruby_oauth_plain"]["app_url"]).to eq("http://localhost:4567")
      expect(conf["java_servlet"]["callback_urls"]).to eq("http://localhost:8080/JavaServlet/Example")
    end
  end

end
