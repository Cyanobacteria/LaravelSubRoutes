# LaravelSubRoute

 1. a simple route spliter by folders  
 2. set perfix default by fileName
 3. can set middlewares or not (need to create a file.php under config folder)
 4. only for laravel 
 5. need atleast laravel 5.5 or up
 6. project create on laravel 5.7
 
## Getting Started

 1. download and require yourself (git clone or just download)
 ````
 git clone https://github.com/Cyanobacteria/laravel-sub-route.git
 ````
 2. use composer
````
 composer require cyanobacteria/laravel-sub-routes
 ````
### Prerequisites

 laravel and atleast version 5.5


### Installing

 1. do Getting Started and success
 2. use on laravelProjectRoot/App/ServiceProviders/RouteServiceProvider.php
```
//in RouteServiceProvider.php

 public function map()
    {
        $this->mapApiRoutes(); //<--laravel originSet

        $this->mapWebRoutes(); //<--laravel originSet


        // we will add 
        $subRoute=new LaravelSubRoutes(['configPath'=>'yourConfigFileName']);
        $subRoute->mapSubRoutes(['subRouteFolderName'=>'yourSubRouteFolderName']);
        
        /*
        projectRootPath/config/yourConfigFileName.php
        projectRootPath/routes/yourSubRouteFolderName/
        */
      
        // we will add
    }

```
 3. php artisan route:clear

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details


