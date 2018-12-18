# Phanda Framework

To learn more about how to use Phanda, please go to the [Documentation](https://phandaframework.com/docs)

## Installation
Phanda uses [Composer](https://getcomposer.org/) to manage all of it's dependencies.
It also provides an easy way for you to manage your projects being built with Phanda. Make sure that you have composer installed before continuing.

**Creating a project using composer**  
To get started with Phanda, you can create a new project using the composer `create-project` command.  
```bash
composer create-project --prefer-dist phanda-framework/phanda project-name-here
```   

**Initializing Environment**   
Once you have setup a new project, you should copy the file `/environment/app.example.env` to `/environment/app.env` and configure the values to
be in align with your project. The same goes for any other `.env` files that you may be needing in your application.

**Running a local development server**  
If you have installed PHP locally, and would like to run a development server to see changes you make before you
put them live, you can run the following `kungfu` command. A development server will start at
`http://127.0.0.1:8000/` and present you with the default welcome page.
```bash
php kungfu serve
```