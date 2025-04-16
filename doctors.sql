-- Дәрігерлер кестесін жасау
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    experience INT,
    about TEXT,
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Жазылулар кестесін жасау
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    department VARCHAR(50) NOT NULL,
    doctor_id INT NOT NULL,
    message TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Тест дәрігерлерді қосу
INSERT INTO doctors (name, specialty, department, phone, email, experience, about) VALUES
('Ахметов Асқар', 'Жалпы терапевт', 'therapy', '+7 (777) 123-45-67', 'askar@medjuye.kz', 15, 'Жалпы терапия саласында 15 жылдық тәжірибесі бар дәрігер'),
('Серікова Әсел', 'Кардиолог', 'cardiology', '+7 (777) 234-56-78', 'asel@medjuye.kz', 10, 'Жүрек-қан тамыр аурулары бойынша маман'),
('Қалиев Мақсат', 'Невролог', 'neurology', '+7 (777) 345-67-89', 'maksat@medjuye.kz', 12, 'Жүйке жүйесі аурулары бойынша тәжірибелі маман'),
('Жұмабаева Айгүл', 'Педиатр', 'pediatrics', '+7 (777) 456-78-90', 'aigul@medjuye.kz', 8, 'Балалар дәрігері'),
('Нұрланов Бақыт', 'Хирург', 'surgery', '+7 (777) 567-89-01', 'bakhyt@medjuye.kz', 20, 'Жоғары санатты хирург'); 