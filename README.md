# dbpp
dbpp is a library that simplifies database queries by collecting them into one class.

## Installation
dbpp requires composer and php 8.0 or higher.

For installation run this command in composer:
```shell
composer require ppeco/dbpp
```

## Usage
The main class for dbpp is Database.
Create a class extended from Database from dbpp.

```php
class SimpleDatabase extends Database {

}
```

Create a class that will contain all the queries for a specific table.
The class should be extended from the Dao class from dbpp.

```php
class TableDao extends Dao {

}
```

Create functions with query annotations from dbpp.
They can be Query and Insert.
Functions should call a function from parent.

```php
abstract class TableDao extends Dao {
    #[Query("SELECT * FROM `table`")]
    public abstract function getAll(): array|false;
    
    #[Query("SELECT * FROM `table` WHERE `id` = :id")]
    public abstract function getById(int $id): array|false;
    
    #[Insert("INSERT `table`(`id`, `name`) VALUES(NULL, :name)")]
    public abstract function insert(string $name): bool;

    #[Insert("INSERT `table`(`id`, `name`) VALUES(NULL, :name)", ['id'])]
    public abstract function insert2(string $name): int|bool;
}
```

Link your Dao to Database via class properties.
```php
class SimpleDatabase extends Database {
   public TableDao $table;
}
```

Well, the final steps. 
Create PDO object and create Database class
```php
$pdo = new PDO(*data*);
$database = new SimpleDatabase($pdo);
```

Now you can call methods from the Database class
