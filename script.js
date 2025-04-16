document.addEventListener('DOMContentLoaded', function() {
    // Жазылу формасын өңдеу
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        // Бөлім таңдалған кезде дәрігерлер тізімін жаңарту
        const departmentSelect = document.getElementById('department');
        const doctorSelect = document.getElementById('doctor');

        const doctors = {
            therapy: [
                'Ахметов Асқар Болатұлы',
                'Серікова Әйгерім Маратқызы'
            ],
            cardiology: [
                'Жұмабаев Бақыт Сәкенұлы',
                'Қасымова Динара Ержанқызы'
            ],
            neurology: [
                'Тұрсынов Мақсат Қанатұлы',
                'Әлібекова Жанар Бақытқызы'
            ],
            pediatrics: [
                'Нұрланова Айгүл Маратқызы',
                'Сәрсенов Дәурен Бақытұлы'
            ]
        };

        departmentSelect.addEventListener('change', function() {
            const selectedDepartment = this.value;
            doctorSelect.innerHTML = '<option value="">Дәрігерді таңдаңыз</option>';
            
            if (selectedDepartment && doctors[selectedDepartment]) {
                doctors[selectedDepartment].forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor;
                    option.textContent = doctor;
                    doctorSelect.appendChild(option);
                });
            }
        });

        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Форма мәліметтерін алу
            const formData = {
                fullName: document.getElementById('fullName').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                department: document.getElementById('department').value,
                doctor: document.getElementById('doctor').value,
                date: document.getElementById('date').value,
                time: document.getElementById('time').value,
                comments: document.getElementById('comments').value
            };
            
            // Форманы тексеру
            if (validateAppointmentForm(formData)) {
                // Мәліметтерді консольге шығару (бұл жерде сервермен байланысты іске асыруға болады)
                console.log('Жазылу мәліметтері:', formData);
                alert('Сіздің өтінішіңіз сәтті қабылданды! Біз сізбен байланысамыз.');
                appointmentForm.reset();
            }
        });
    }

    // Байланыс формасын өңдеу
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('contactEmail').value,
                subject: document.getElementById('subject').value,
                message: document.getElementById('message').value
            };
            
            if (validateContactForm(formData)) {
                console.log('Байланыс формасының мәліметтері:', formData);
                alert('Хабарламаңыз сәтті жіберілді! Біз жақын арада жауап береміз.');
                contactForm.reset();
            }
        });
    }
    
    // Жазылу формасын тексеру
    function validateAppointmentForm(data) {
        // Аты-жөні тексеру
        if (data.fullName.length < 2) {
            alert('Аты-жөніңізді толық енгізіңіз');
            return false;
        }
        
        // Телефон нөмірін тексеру
        const phoneRegex = /^\+?[\d\s-]{10,}$/;
        if (!phoneRegex.test(data.phone)) {
            alert('Дұрыс телефон нөмірін енгізіңіз');
            return false;
        }
        
        // Email тексеру
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            alert('Дұрыс email адресін енгізіңіз');
            return false;
        }
        
        // Бөлімді тексеру
        if (!data.department) {
            alert('Бөлімді таңдаңыз');
            return false;
        }

        // Дәрігерді тексеру
        if (!data.doctor) {
            alert('Дәрігерді таңдаңыз');
            return false;
        }
        
        // Күнді тексеру
        const selectedDate = new Date(data.date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert('Күнді дұрыс таңдаңыз');
            return false;
        }
        
        // Уақытты тексеру
        if (!data.time) {
            alert('Уақытты таңдаңыз');
            return false;
        }
        
        return true;
    }

    // Байланыс формасын тексеру
    function validateContactForm(data) {
        if (data.name.length < 2) {
            alert('Аты-жөніңізді толық енгізіңіз');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            alert('Дұрыс email адресін енгізіңіз');
            return false;
        }

        if (data.subject.length < 2) {
            alert('Тақырыпты енгізіңіз');
            return false;
        }

        if (data.message.length < 10) {
            alert('Хабарлама тым қысқа');
            return false;
        }

        return true;
    }
    
    // Плавная прокрутка для навигации
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Мобильді меню
    const menuToggle = document.createElement('div');
    menuToggle.className = 'menu-toggle';
    menuToggle.innerHTML = `
        <svg viewBox="0 0 100 80" width="30" height="30">
            <rect width="100" height="10" rx="5"></rect>
            <rect y="30" width="100" height="10" rx="5"></rect>
            <rect y="60" width="100" height="10" rx="5"></rect>
        </svg>
    `;

    const nav = document.querySelector('.nav-container');
    const menu = document.querySelector('.nav-menu');
    
    nav.insertBefore(menuToggle, menu);

    menuToggle.addEventListener('click', function() {
        menu.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Скролл анимациясы
    function reveal() {
        const reveals = document.querySelectorAll('.service-card, .pricing-card, .feature-card, .contact-item');
        
        reveals.forEach(element => {
            const windowHeight = window.innerHeight;
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < windowHeight - elementVisible) {
                element.classList.add('animate-fade-in');
            }
        });
    }

    window.addEventListener('scroll', reveal);
    reveal();
}); 