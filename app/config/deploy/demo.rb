# Deployment server info
#Path to project
set :deploy_to, "/var/www/udoras/demo"

set :application, "udoras_demo"
set :domain, "144.76.81.36"
set :app_path, "app"
set :web_path, "web"
set :maintenance_basename, "maintenance"

# SCM info
set :repository, "."
set :scm, :none
set :deploy_via, :copy
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
set :keep_releases, 5
after "deploy:update", "deploy:cleanup"
set :writable_dirs,       ["app/cache", "app/logs", "web/images", "web/media", "app/spool"]
set :shared_files,      ["app/config/parameters.yml"] # This stops us from having to recreate the parameters file on every deploy.
set :shared_children,   [app_path + "/logs", web_path + "/images", web_path + "/media", app_path + "/spool"]
set :permission_method, :acl
set :use_composer, true
set :clear_controllers, false
set :use_set_permissions, true
# Confirmations will not be requested from the command line.
set :interactive_mode, false

# The following line tells Capifony to deploy the last Git tag.
# Since Jenkins creates and pushes a tag following a successful build this should always be the last tested version of the code.
set :branch, 'master'

# User details for the production server
set :user, "root"
set :use_sudo, true
ssh_options[:forward_agent] = true

# Uncomment this if you need more verbose output from Capifony
logger.level = Logger::MAX_LEVEL