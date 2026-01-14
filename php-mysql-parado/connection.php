<?php 

function Connect(): PDO 

{ 

    // Palitan mo ito ng DB credentials mo 

    $host = "localhost"; 

    $db   = "parado"; 

    $user = "root"; 

    $pass = ""; 

    $charset = "utf8mb4"; 

 

 

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset"; 

 

    $options = [ 

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // para makita errors 

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,         // fetch as object 

        PDO::ATTR_EMULATE_PREPARES   => false, 

    ]; 

 

try { 

        return new PDO($dsn, $user, $pass, $options); 

    } catch (PDOException $e) { 

         

        die("Database connection failed. Please contact the administrator."); 

         

        // for debugging (optional) 

        echo $e->getMessage(); 

    } 

} 