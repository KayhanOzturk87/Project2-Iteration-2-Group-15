    <?php
    $db_host = 'localhost';
    $db_name = 'learning_assistant_db'; 
    $db_user = 'root'; 
    $db_pass = '';    

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {

        die("Could not connect to the database $db_name: " . $e->getMessage());
        
    }
    ?>