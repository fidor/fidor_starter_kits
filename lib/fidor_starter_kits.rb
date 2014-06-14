require 'fidor_starter_kits/version'
require 'fileutils'
require 'tmpdir'
require 'zip'

module FidorStarterKits

  class << self

    # Path to starter kits
    def path
      File.expand_path('../starter_kits', File.dirname(__FILE__))
    end

    def exists?(app_name)
      File.exists? File.join(path, app_name)
    end

    # @param [String] app_name  directory name of a starter kit
    # @param [String] client_id
    # @param [String] client_secret
    # @param [String] fidor_url
    # @return [String] path to zipped example im /tmp folder
    def build(app_name, client_id, client_secret, fidor_url)

      # move example to a save location
      example_src_path = File.join(path, app_name)
      tmp_src_dir = Dir.mktmpdir(app_name)
      FileUtils.copy_entry example_src_path, tmp_src_dir

      # read example files and replace placeholder with id/secret
      example_files =  File.join(tmp_src_dir, 'example.*')
      Dir.glob(example_files) do |example_file|
        content = File.read(example_file)
        changed_content = content.gsub(/<CLIENT-ID>/, client_id)
                                  .gsub(/<CLIENT-SECRET>/, client_secret)
                                  .gsub(/<FIDOR-URL>/, fidor_url)
        File.write(example_file, changed_content)
      end

      # create zip file in tmp dir
      zip_file_path = File.join(tmp_src_dir, "#{app_name}.zip")
      Zip::File.open(zip_file_path, Zip::File::CREATE) do |zipfile|
        Dir[File.join(tmp_src_dir, '**', '**')].each do |file|
          zipfile.add(file.sub("#{tmp_src_dir}/", ''), file)
        end
      end
      zip_file_path
    end

  end
end
