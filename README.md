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

### 1. Start backend server

Once you have cloned the github repository or unzip the file provided.
In backend folder open a terminal and use the command:
``` 
composer install
```
Start the server : `symfony server:start --no-tls`
Then check if the app is running using this url below, you're supposed to see the symfony home page.  
[Click Here !](http://127.0.0.1:8000/)  (Check if the port 8000 is free)

### 2. Install DB and Import datas into it 
Now use these commands to create and install the DB :
First set in backend/.env file your database parameters (name, user, password,port)

```
 php bin/console doctrine:database:create
 php bin/console doctrine:migration:migrate
```  

Now use this command if you have the symfony binary installed on your machine : `symfony console ugo:orders:import`  
Otherwise une these command : `php bin/console ugo:orders:import` 

Now the symfony Api is running and can display datas from DB

### 3. Start frontend server

Open a terminal in frontend folder and run : `npm install`


## Run the application using Docker :

Open a terminal at the root directory.
And use these commands:
``` 
docker compose up -d db
docker compose --profile init run --rm backend-init
docker compose up -d backend frontend

```

## Testing 

A quick test has been added in both services.
For the backend, use the /backend folder.
`vendor/bin/phpunit tests/Controller/CustomerControllerTest.php --testdox` 

For the frontend, use the /frontend folder.  
`npm run test:run`

## Notes 
### Possible developments

1. It could be interesting to integrate pagination on both the Customers and Orders pages.
In our case, it works well because we don’t have a lot of data stored in our database.
However, if we need to store a large amount of data, this could currently create performance issues because all the data must be loaded before rendering.
With pagination, we can display items 10 at a time, for example.
This would make loading faster and also improve the UI/UX.

2. On the Customers page, implement an order-count preview per customer using a lightweight tooltip/popover triggered on hover over the “Show orders” button.