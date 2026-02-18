# Instructions for building and starting the application

Version to use:   
PHP: 8.2.  
Symfony: 7.4.  
React: 19.2

There  are currently two ways to do it.
1. The first is to start the Symfony backend and the React frontend separately.  
Once the backend is running the frontend will work normally diplay the datas.

2. The second if you have docker install on your machine is to use Docker command to run both app.

## Let's start with the first one:

### 1- Start backend server

Once you have cloned the github repository or unzip the file provided.
In backend folder open a terminal and use the command:
``` 
composer install
```
Then check if the app is running using this url below, you're supposed to see the symfony home page.  
[Click Here !](http://127.0.0.1:8000/) 

### 2- Install DB and Import datas into it 
Now use these commands to create and install the DB :
First set in backend/.env file your database parameters (name, user, password,port)

```
 php bin/console doctrine:database:create
 php bin/console doctrine:migration:migrate
```  

Now use this command if you have the symfony binary installed on your machine : `symfony console ugo:orders:import`  
Otherwise une these command : `php bin/console ugo:orders:import` 

Now the symfony Api is running and can display datas from DB

### 1- Start frontend server

Open a terminal in frontend folder and run : `npm install`


## Run the application using Docker :

Open a terminal at the root directory.
And use these commands:
``` 
docker compose up -d db
docker compose --profile init run --rm backend-init
docker compose up -d backend frontend

```