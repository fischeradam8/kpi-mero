#Install

1. git clone

2. app/config/parameters.yml -> see parameters.yml.dist

3. create a new loginCredentials.yml in app/config
    
        parameters:
            credentials: 'jirausername:jirapassword'
            
4. update your database
    
        app/console doctrine:database:create
        app/console doctrine:schema:update --force

