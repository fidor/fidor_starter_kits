require 'fidor_starter_kits/version'
require 'fileutils'
require 'tmpdir'
require 'zip'
require 'json'

module FidorStarterKits

  STARTER_KITS = %w{ node_tx golang_plain php_plain ruby_oauth_plain java_servlet }

  class << self

    # Directory path to starter kits
    def path
      File.expand_path('../starter_kits', File.dirname(__FILE__))
    end

    # Check if an app with the given name exists in the starter kits directory
    # @param [String] app_name
    # @return [Boolean]
    def exists?(app_name)
      File.exists? File.join(path, app_name)
    end

    # @param [Hash] opts options for building a starter kit
    # @options opts [String] :app_name directory name of a starter kit
    # @options opts [String] :client_id app client id
    # @options opts [String] :client_secret app client secret
    # @options opts [String] :app_url full url to the application
    # @options opts [String] :fidor_oauth_url full url to the oauth endpoints e.g http://fidor.de/oauth
    # @options opts [String] :fidor_api_url full url to the api or api_sandbox no trailing slashes e.g http://fidor.de/api_sandbox
    # @return [Nil | String] path to zipped example im /tmp folder or nil if app does not exists
    def build(opts)
      app_name = opts[:app_name]
      return if !app_name || !exists?(app_name) || !(STARTER_KITS).include?(app_name)

      # move example to a safe location
      example_src_path = File.join(path, app_name)
      tmp_src_dir = Dir.mktmpdir(app_name)
      FileUtils.copy_entry example_src_path, tmp_src_dir

      # read example files and replace placeholder with id/secret
      example_files =  File.join(tmp_src_dir, "**", "[Ee]xample.*")

      Dir.glob(example_files) do |example_file|
        content = File.read(example_file)
        %w(client_id client_secret app_url fidor_oauth_url fidor_api_url).each do |field|
          content.gsub!("<#{field.upcase}>", opts[field.to_sym]) if opts[field.to_sym]
        end
        File.write(example_file, content)
      end

      # create zip file in tmp dir
      zip_file_path = File.join(tmp_src_dir, "#{app_name}.zip")
      Zip::File.open(zip_file_path, Zip::File::CREATE) do |zipfile|
        Dir.glob(File.join(tmp_src_dir, '**', '**'),  File::FNM_DOTMATCH).each do |file|
          zipfile.add(file.sub("#{tmp_src_dir}/", ''), file) unless file.end_with? '.'
        end
      end
      zip_file_path
    end

    def all
      @conf ||= {}
      return @conf unless @conf.length == 0

      STARTER_KITS.each do |kit|
        base = File.join(path, kit)
        meta = File.join(base, ".fidor_meta.json")
        if File.exists? meta
          File.open(meta) {|f| @conf[kit] = JSON.parse(f.read)}
        else
          @conf[kit] = {"error" => ".fidor_meta.json not found"}
        end
      end
      return @conf
    end

    def get app_name
      all[app_name]
    end

    def each
      all.each_value { |conf| yield conf }
    end

  end
end
