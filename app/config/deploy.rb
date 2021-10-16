set :stages, %w(dev testing demo dev_stage_2 test_stage_2 dev_stage_4)
set :default_stage, "dev"
set :stage_dir, "app/config/deploy"
require "capistrano/ext/multistage"