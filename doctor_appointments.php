<?php
session_start();

// Тек дәрігерлерге рұқсат беру
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "doctor"){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Дәрігердің ID-ін алу
$doctor_id = 0;
$sql = "SELECT id FROM doctors WHERE user_id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result)){
            $doctor_id = $row['id'];
        }
    }
    mysqli_stmt_close($stmt);
}

// Жазылуларды алу
$appointments = [];
$sql = "SELECT a.*, s.name as service_name, s.price, u.full_name as patient_name, u.phone as patient_phone, u.email as patient_email 
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date ASC, a.appointment_time ASC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)){
            $appointments[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Жазылу мәртебесін өзгерту
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])){
    $sql = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "sii", $_POST['status'], $_POST['appointment_id'], $doctor_id);
        
        if(mysqli_stmt_execute($stmt)){
            header("location: doctor_appointments.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);

// Жазылуларды күндер бойынша топтастыру
$grouped_appointments = [];
foreach($appointments as $appointment){
    $date = $appointment['appointment_date'];
    if(!isset($grouped_appointments[$date])){
        $grouped_appointments[$date] = [];
    }
    $grouped_appointments[$date][] = $appointment;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <title>Дәрігер жазылулары</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Менің жазылуларым</h2>
        
        <?php if(empty($appointments)): ?>
            <p>Сізде әлі жазылулар жоқ.</p>
        <?php else: ?>
            <!-- Фильтр -->
            <div class="filter-section">
                <button class="btn btn-filter active" data-status="all">Барлығы</button>
                <button class="btn btn-filter" data-status="pending">Күтілуде</button>
                <button class="btn btn-filter" data-status="confirmed">Расталған</button>
                <button class="btn btn-filter" data-status="completed">Аяқталған</button>
                <button class="btn btn-filter" data-status="cancelled">Бас тартылған</button>
            </div>

            <?php foreach($grouped_appointments as $date => $day_appointments): ?>
                <div class="date-section">
                    <h3><?php echo date('d.m.Y', strtotime($date)); ?></h3>
                    <div class="appointments-list">
                        <?php foreach($day_appointments as $appointment): ?>
                            <div class="appointment-card <?php echo $appointment['status']; ?>" data-status="<?php echo $appointment['status']; ?>">
                                <div class="appointment-header">
                                    <div class="time-slot">
                                        <strong><?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></strong>
                                    </div>
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
                                    <p><strong>Пациент:</strong> <?php echo $appointment['patient_name']; ?></p>
                                    <p><strong>Телефон:</strong> <?php echo $appointment['patient_phone']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $appointment['patient_email']; ?></p>
                                    <p><strong>Қызмет:</strong> <?php echo $appointment['service_name']; ?></p>
                                    <p><strong>Бағасы:</strong> <?php echo $appointment['price']; ?> тг</p>
                                    <?php if(!empty($appointment['notes'])): ?>
                                        <p><strong>Қосымша:</strong> <?php echo $appointment['notes']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if($appointment['status'] != 'cancelled' && $appointment['status'] != 'completed'): ?>
                                    <div class="appointment-actions">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <?php if($appointment['status'] == 'pending'): ?>
                                                <input type="hidden" name="status" value="confirmed">
                                                <input type="submit" name="update_status" class="btn btn-success" value="Растау">
                                            <?php endif; ?>
                                            <?php if($appointment['status'] == 'confirmed'): ?>
                                                <input type="hidden" name="status" value="completed">
                                                <input type="submit" name="update_status" class="btn btn-primary" value="Аяқтау">
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Фильтрлеу функциясы
        const filterButtons = document.querySelectorAll('.btn-filter');
        const appointmentCards = document.querySelectorAll('.appointment-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const status = button.getAttribute('data-status');
                
                // Белсенді кнопканы өзгерту
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Карточкаларды фильтрлеу
                appointmentCards.forEach(card => {
                    if (status === 'all' || card.getAttribute('data-status') === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
</body>
</html> 