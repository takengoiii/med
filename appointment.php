<?php
session_start();

// Қосылымды тексеру
$conn = new mysqli("localhost", "root", "", "medjuye");

// Қосылым қатесін тексеру
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

// Форма жіберілген кезде
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Авторизацияны тексеру
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $date = $conn->real_escape_string($_POST['date']);
    $department = $conn->real_escape_string($_POST['department']);
    $doctor_id = $conn->real_escape_string($_POST['doctor']);
    $message = $conn->real_escape_string($_POST['message']);

    // Validation
    if (empty($name) || empty($phone) || empty($email) || empty($date) || empty($department) || empty($doctor_id)) {
        $error = 'Барлық міндетті өрістерді толтырыңыз';
    } else {
        // Check if the selected date is not in the past
        $selected_date = new DateTime($date);
        $today = new DateTime();
        if ($selected_date < $today) {
            $error = 'Өткен күнге жазылу мүмкін емес';
        } else {
            // Insert appointment
            $sql = "INSERT INTO appointments (user_id, patient_name, phone, email, appointment_date, department, doctor_id, message, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssss", $user_id, $name, $phone, $email, $date, $department, $doctor_id, $message);

            if ($stmt->execute()) {
                $success = 'Сіздің өтінішіңіз қабылданды. Жақын арада байланысқа шығамыз.';
            } else {
                $error = 'Қате орын алды. Қайталап көріңіз.';
            }
        }
    }
}

// Get doctors list for AJAX response
if (isset($_GET['get_doctors']) && isset($_GET['department'])) {
    $department = $conn->real_escape_string($_GET['department']);
    $sql = "SELECT id, name, specialty FROM doctors WHERE department = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = array();
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($doctors);
    exit();
}

// Redirect back to the form with status
if ($error || $success) {
    $_SESSION['appointment_error'] = $error;
    $_SESSION['appointment_success'] = $success;
    header("Location: appointment.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <title>Жазылу</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Дәрігерге жазылу</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Бөлім</label>
                <select name="department" id="department" class="form-control">
                    <option value="">Бөлімді таңдаңыз</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Дәрігер</label>
                <select name="doctor" id="doctor" class="form-control">
                    <option value="">Алдымен бөлімді таңдаңыз</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Күні</label>
                <input type="date" name="date" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Уақыты</label>
                <input type="time" name="time" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Қосымша ақпарат</label>
                <textarea name="message" class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Жазылу">
                <a href="index.php" class="btn btn-secondary">Бас тарту</a>
            </div>
        </form>
    </div>
    
    <script>
    $(document).ready(function(){
        // Бөлім таңдалған кезде дәрігерлер тізімін жаңарту
        $('#department').change(function(){
            var departmentId = $(this).val();
            if(departmentId){
                $.get('get_doctors.php', {department_id: departmentId}, function(data){
                    $('#doctor').html(data);
                });
            } else {
                $('#doctor').html('<option value="">Алдымен бөлімді таңдаңыз</option>');
            }
        });
    });
    </script>
</body>
</html> 