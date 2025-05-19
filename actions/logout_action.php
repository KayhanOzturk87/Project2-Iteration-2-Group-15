 <?php
    session_start();
    session_unset();
    session_destroy();
    $base_url = '/'; 
    header("Location: " . $base_url . "login.php?message=logged_out");
    exit();
    ?>