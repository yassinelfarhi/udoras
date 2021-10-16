# Deployment server info
set :application, "udoras_dev_stage_4"
set :domain,      "144.76.81.36"
#default_run_options[:pty] = true
set :deploy_to,   "/var/www/udoras/dev_stage_4"
set :app_path,    "app"
set :web_path, 	  "web"
set :maintenance_basename, 	"maintenance"

# SCM info
set :repository,  "."
set :scm,         :none
set :deploy_via,  :copy
set :copy_exclude, [".git/*", ".svn/*", ".rsync_cache/*"]

set :model_manager, "doctrine"
set :dump_assetic_assets, true
before 'deploy:create_symlink', 'symfony:doctrine:schema:update'

after "symfony:doctrine:schema:update", "symfony:doctrine:migrations:migrate"

# Role info. I don't think this is particularly important for Capifony...
role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain,      :primary => true   # This may be the same as your `Web` server
role :db,         domain      # This is where Symfony2 migrations will run

# General config stuff
set :keep_releases,  5
after "deploy:update", "deploy:cleanup"

set :symfony_env_prod, "prod"

set :writable_dirs,     ["app/cache", "app/logs", "app/spool", "web/images", "web/media"]
set :shared_files,      ["app/config/parameters.yml"] # This stops us from having to recreate the parameters file on every deploy.
set :shared_children,   [app_path + "/logs", web_path + "/images", web_path + "/media", app_path + "/spool"]
set :permission_method, :acl
set :use_composer, true
set :clear_controllers, false
set :use_set_permissions, true

#testing jenkins for fails
#after "symfony:assetic:dump", "symfony:test"

# Confirmations will not be requested from the command line.
set :interactive_mode, false

# The following line tells Capifony to deploy the last Git tag.
# Since Jenkins creates and pushes a tag following a successful build this should always be the last tested version of the code.
set :branch, 'master'

# User details for the production server
set :user, "root"
set :use_sudo, false
ssh_options[:forward_agent] = true

# Uncomment this if you need more verbose output from Capifony
logger.level = Logger::MAX_LEVEL