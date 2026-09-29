# PHP Mentored Education Program, Module 10: SQL, PDO

## Requirements
The goal of this exercise is to create a very simple inventory for products which should consist of the following features:

- List the inventory items, where a page should contain a maximum of 10 products.
  If the overall number of products exceeds the maximum of 10, then on the bottom of the list there should be links so the users will be able to
  navigate between the pages.
  In the list, for each item you must display all the details for a given product: the name, category, description, the creation and update dates (if it was updated earlier), and a button for the Update Page.
- Users should be able to add new products. Each product should include the product's name, description, category, and the creation date. All of the items must be saved into the database.
- Users should be able to update the products. If a product was updated, the date and time of the modification should be displayed along with other details.
- The form must be validated on the server side. The validation should be the following:
    - Product name and category are mandatory; description is optional.
    - The max length of the description is 500 characters.
    - The max length of the product category and the name is 64 characters. 

Important notes:
For the database communication, use PDO.

## Steps to follow
You can skip the first 4 steps if you use the environment you already created for the exercises.

1. Create a new folder for your project in your WSL2 environment. Navigate to the `~/projects` directory you created earlier and create a new directory for this task:
   ```
   mkdir -p ~/projects/mep_db_task
   cd ~/projects/mep_db_task
   ```

2. Create a Dockerfile in the project directory with the following content:
    ```Dockerfile
    FROM php:8.1-fpm

    # Install necessary extensions
    RUN docker-php-ext-install pdo pdo_mysql

    # Set working directory
    WORKDIR /var/www/html

    # Copy project files
    COPY . .
    ```

3. Create an nginx.conf file in the project directory with the following content:
    ```conf
    server {
        listen 80;
        server_name localhost;

        root /var/www/html;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_pass app:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
    ```

4. Start your Docker containers for PHP, Nginx, and MySQL. Update your `docker-compose.yml` file to include a MySQL service if you haven't done so already:
   ```yaml
   version: '3.8'

   services:
     app:
       build:
         context: .
         dockerfile: Dockerfile
       volumes:
         - .:/var/www/html
       ports:
         - "9000:9000"

     web:
       image: nginx:latest
       volumes:
         - .:/var/www/html
         - ./nginx.conf:/etc/nginx/conf.d/default.conf
       ports:
         - "8080:80"

     db:
       image: mysql:8.0
       environment:
         MYSQL_ROOT_PASSWORD: root
         MYSQL_DATABASE: mep_db_task
       ports:
         - "3306:3306"
   ```

   Start your containers:
   ```
   docker-compose up -d
   ```

   Verify the containers are running:
   ```
   docker ps
   ```

5. Connect to the MySQL database using a MySQL client, like [HeidiSQL](https://www.heidisql.com/).

   Configuration:
   - Hostname/IP: `127.0.0.1`
   - User: `root`
   - Password: `root`
   - Port: `3306`
   - Database: `mep_db_task`

6. Create a new table in the "mep_db_task" database called `products`. You can write either an SQL command or use the user interface. It should contain the following fields:

   ```sql
   CREATE TABLE products (
       id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(64) NOT NULL,
       category VARCHAR(64) NOT NULL,
       description TEXT,
       created_at DATETIME NOT NULL DEFAULT NOW(),
       updated_at DATETIME NULL
   );
   ```

7. Clone this repository into your project directory:
   ```
   git clone <repository-url> .
   ```
   The repository contains an `index.php` file, which is a template for your Inventory app. You will need to update it in a way to satisfy the requirements listed above.

8. Create a `ProductForm` Class:
    - It should have the following properties: `name` (string), `category` (string), `description` (string), `id` (integer, optional).
    - It should have only one method, called `validate`, that will implement the validation rules for the product described above. The method needs to return a boolean value based on the validation results.

9. Create a `Product` Class:
    - A `Product` Class needs to have exactly the same properties as the `ProductForm`. Instantiate this class once the `ProductForm` is validated and everything is OK with the data.

10. Create a new Class called `ProductRepository`:
    - Implement a method called `save` with a parameter called `$product` of type `Product`, which saves the data into the newly created table.
    - Implement a method called `update` that requires a parameter called `$product` of type `Product`. Within the method, implement the logic to update the product that has this specific ID in the table.
    - Implement a method called `get` that requires an integer parameter called `id`. The method should return the product with the specific ID or return `null`.
    - Implement a method called `getAll`. The `getAll` method should have a parameter `page` (integer, with the default value 1). If the value is 1, return the first 10 products; if the page is 2, return the next 10 products (starting from 10 to 20); if the page is 3, return products 20–30, and so on.

------------
# TODO:
11. Update the `index.php` file to satisfy the requirements described above. It must list the products using the `getAll` method.

12. Update the `save-process.php` file to follow this flow:
    - If the `$_POST` superglobal is empty, return to `index.php`.
    - If it has data: Instantiate a `ProductForm` class, set its properties using the data sent by the form (use the `$_POST` superglobal).
    - Validate the form inputs using the `validate` method. If it's valid, create a `Product` object; if invalid, return to `index.php` and display an error message.
    - If the data is valid, proceed with the save.

13. Update the `update-process.php` file to follow this flow:
    - If the `$_POST` superglobal is empty, return to `index.php`.
    - If it has data: Instantiate a `ProductForm` class, set its properties using the data sent by the form (use the `$_POST` superglobal).
    - Validate the form inputs using the `validate` method. If it's valid, create a `Product` object; if invalid, return to `index.php` and display an error message.
    - If the data is valid, proceed with the update.