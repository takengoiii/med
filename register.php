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
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $conn->real_escape_string($_POST['phone']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error = 'Барлық өрістерді толтырыңыз';
    } elseif ($password !== $confirm_password) {
        $error = 'Құпия сөздер сәйкес келмейді';
    } elseif (strlen($password) < 6) {
        $error = 'Құпия сөз кем дегенде 6 таңбадан тұруы керек';
    } else {
        // Check if email already exists
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Бұл email тіркелген';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'patient')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);

            if ($stmt->execute()) {
                $success = 'Тіркелу сәтті аяқталды! Енді жүйеге кіре аласыз.';
            } else {
                $error = 'Қате орын алды. Қайталап көріңіз.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тіркелу - МедЖүйе</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .register-form {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--text-light);
            margin-bottom: 10px;
            font-size: 1.1em;
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 10px rgba(0, 150, 136, 0.1);
        }

        .submit-btn {
            background: var(--secondary-color);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .submit-btn:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 150, 136, 0.2);
        }

        .login-link {
            text-align: center;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .error-message {
            background: rgba(255, 87, 87, 0.1);
            color: #ff5757;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 87, 87, 0.2);
        }

        .success-message {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .welcome-section {
            text-align: center;
            padding: 80px 20px;
            background: rgba(0, 0, 0, 0.5);
            margin-bottom: 40px;
        }

        .welcome-section h2 {
            color: var(--text-light);
            font-size: 2.5em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-section p {
            color: var(--text-light);
            font-size: 1.2em;
            max-width: 800px;
            margin: 0 auto;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <h1>МедЖүйе</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html">Басты бет</a></li>
                <li><a href="services.html">Қызметтер</a></li>
                <li><a href="appointment.html">Жазылу</a></li>
                <li><a href="contacts.html">Байланыс</a></li>
                <li><a href="login.php">Кіру</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome-section">
            <div class="container">
                <h2>Тіркелу</h2>
                <p>Жүйеге тіркеліп, қызметтерді пайдаланыңыз</p>
            </div>
        </section>

        <div class="register-container">
            <form class="register-form" method="POST" action="">
                <?php if($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Аты-жөні</label>
                    <input type="text" id="name" name="name" required placeholder="Аты-жөніңізді енгізіңіз">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="email@example.com">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone" required placeholder="+7 (XXX) XXX-XX-XX">
                </div>

                <div class="form-group">
                    <label for="password">Құпия сөз</label>
                    <input type="password" id="password" name="password" required placeholder="Құпия сөзді енгізіңіз">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Құпия сөзді қайталаңыз</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Құпия сөзді қайталап енгізіңіз">
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Тіркелу
                </button>

                <div class="login-link">
                    Аккаунтыңыз бар ма? <a href="login.php">Кіру</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2024 МедЖүйе. Барлық құқықтар қорғалған.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html> 