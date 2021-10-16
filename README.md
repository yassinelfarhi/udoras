uDoras-Web
==========

Requirements
------------
  * Redis
  * Node.js

Installation
------------
  * If you use Jenkins. Add to shared files spool/ folder 
  * Add cron job with command - "app/console swiftmailer:spool:send --message-limit=10 --env=prod"
  * install wkhtmltopdf and add wkhtmltopdf and wkhtmltoimage paths to parameters.yml 
