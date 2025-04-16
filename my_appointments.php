<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Жазылуларды алу
$appointments = [];
$sql = "SELECT a.*, d.specialization, s.name as service_name, s.price, u.full_name as doctor_name, dp.name as department_name 
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN services s ON a.service_id = s.id
        JOIN users u ON d.user_id = u.id
        JOIN departments dp ON d.department_id = dp.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)){
            $appointments[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Жазылудан бас тарту
if(isset($_POST['cancel_appointment']) && !empty($_POST['appointment_id'])){
    $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status = 'pending'";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $_POST['appointment_id'], $_SESSION["id"]);
        
        if(mysqli_stmt_execute($stmt)){
            header("location: my_appointments.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <title>Менің жазылуларым</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Менің жазылуларым</h2>
        
        <?php if(empty($appointments)): ?>
            <p>Сізде әлі жазылулар жоқ.</p>
            <a href="appointment.php" class="btn btn-primary">Жазылуға өту</a>
        <?php else: ?>
            <div class="appointments-list">
                <?php foreach($appointments as $appointment): ?>
                    <div class="appointment-card <?php echo $appointment['status']; ?>">
                        <div class="appointment-header">
                            <h3><?php echo $appointment['department_name']; ?></h3>
                            <span class="status <?php echo $appointment['status']; ?>">
                                <?php
                                switch($appointment['status']){
                                    case 'pending':
                                        echo 'Күтілуде';
                                        break;
                                    case 'confirmed':
                                        echo 'Расталған';
                                        break;
                                    case 'completed':
                                        echo 'Аяқталған';
                                        break;
                                    case 'cancelled':
                                        echo 'Бас тартылған';
                                        break;
                                }
                                ?>
                            </span>
                        </div>
                        <div class="appointment-details">
                            <p><strong>Дәрігер:</strong> <?php echo $appointment['doctor_name']; ?> (<?php echo $appointment['specialization']; ?>)</p>
                            <p><strong>Қызмет:</strong> <?php echo $appointment['service_name']; ?></p>
                            <p><strong>Бағасы:</strong> <?php echo $appointment['price']; ?> тг</p>
                            <p><strong>Күні мен уақыты:</strong> <?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?> <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></p>
                            <?php if(!empty($appointment['notes'])): ?>
                                <p><strong>Қосымша:</strong> <?php echo $appointment['notes']; ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if($appointment['status'] == 'pending'): ?>
                            <div class="appointment-actions">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <input type="submit" name="cancel_appointment" class="btn btn-danger" value="Бас тарту" onclick="return confirm('Жазылудан бас тартқыңыз келе ме?');">
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="actions">
                <a href="appointment.php" class="btn btn-primary">Жаңа жазылу</a>
                <a href="index.php" class="btn btn-secondary">Басты бетке оралу</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 