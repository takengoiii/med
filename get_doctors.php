<?php
require_once "config.php";

if(isset($_GET['department_id'])){
    $sql = "SELECT d.id, u.full_name FROM doctors d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.department_id = ?";
            
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_GET['department_id']);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            echo '<option value="">Дәрігерді таңдаңыз</option>';
            while($row = mysqli_fetch_assoc($result)){
                echo '<option value="' . $row['id'] . '">' . $row['full_name'] . '</option>';
            }
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($conn);
?> 