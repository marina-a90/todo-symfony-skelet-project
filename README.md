# TODO API

### Docker

Docker:

    $ sudo docker-compose stop 

    and 

    $ sudo docker-compose up -d

### Folder permission and parameters

Create folder(s) for media:

	mkdir public/upload public/uploads/media

Set permissions for media files:

    sudo chmod 0777 -R public/uploads/

Copy parameters: 

    cp config/parameters.yml.disc config/parameters.yml 

## Description

Api for Todo website.

#### Set up data

##### Install dependencies and edit configuration:

Into docker php container start:

    composer install

##### Out of docker container (into project folder) start only this command - 

	sudo chown <your_user>:<your_user> -R ./*
	
Need to change line 16 in backend/.env file

    DATABASE_URL=mysql://todo:todo@todo_devdb:3306/todo

##### NOTE: All command execute into docker todo_php7 container 

Create new database by executing following command from project root:

    bin/console doctrine:database:create

Migrate schema changes by executing following command from project root:

    bin/console do:sc:up --force

Populate database by executing following command from project root:

    bin/console hautelook:fixtures:load --purge-with-truncate

Generate media classification contexts

    bin/console sonata:media:fix-media-context

Install CKEditor

    bin/console ckeditor:install -n

Clear Cache

    ./cc
    
At the end add into /eth/hosts file (out of docker container)
	
	127.0.0.1   todo-api.com
	
Done!

## docker (some debian systems) hack for problems during --build phase
#### there was a problem with some debian systems therefore this command. In that case manualy use this command from the mashine

    chown -R _apt:root /var/lib/apt/lists/
	
	
## Email
To access email client locally go to http://todo-api.com:8081

