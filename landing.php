<?php
// Clean hospital landing page with embedded CSS for modern look
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HiveNova Hospital</title>
    <link rel="icon" href="hospital_logo.png" type="image/png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: #eeea10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            background: url('hive picture.png') no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }


        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }



        header {
            background-color: #00796b;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            color: #e0f2f1;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #004d40;
        }

        main {
            flex: 1;
            max-width: 960px;
            margin: 3rem auto;
            padding: 2rem 2.5rem;
            background: rgba(37, 35, 35, 0.25);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #004d40;
            position: relative;
            z-index: 5;
        }

        .hero {
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 0.05em;
            text-shadow: 0 2px 3px rgb(236, 223, 30);
        }

        .hero p {
            font-size: 1.25rem;
            color: #eeea10;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }

        section {
            margin-bottom: 3rem;
        }

        section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #eeea10;
            border-bottom: 3px solid #eeea10;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        section p,
        section ul {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #eeea10;
            text-shadow: 0 1px 2px rgba(158, 37, 37, 1);

        }

        section ul {
            list-style: disc inside;
            padding-left: 1rem;
        }

        footer {
            background-color: #004d40;
            color: #b2dfdb;
            text-align: center;
            padding: 1rem 0;
            font-weight: 500;
            letter-spacing: 0.05em;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 600px) {
            nav ul {
                flex-direction: column;
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            main {
                margin: 2rem 1rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="landing.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
                <!-- <li><a href="user_dashboard.php">User Dashboard</a></li>
                <li><a href="doctor_dashboard.php">Doctor Dashboard</a></li>
                <li><a href="nurse_dashboard.php">Nurse Dashboard</a></li>
                <li><a href="technician_dashboard.php">Lab Technician Dashboard</a></li> -->
                <li><a href="admin/login.php">Admin Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="hero">
            <h1>Welcome to HiveNova Hospital</h1>
            <p>Your trusted healthcare partner providing compassionate and quality medical services.</p>
        </div>

        <section id="about-us">
            <h2>About Us</h2>
            <p>HiveNova Hospital is dedicated to delivering exceptional healthcare with a patient-centered approach,
                advanced technology, and a compassionate team.</p>
        </section>

        <section id="departments">
            <h2>Departments</h2>
            <ul>
                <li>Emergency</li>
                <li>Radiology</li>
                <li>Cardiology</li>
                <li>Pharmacy</li>
                <li>Laboratory</li>
                <li>Outpatient Services</li>
            </ul>
        </section>

        <section id="meet-our-doctors">
            <h2>Meet Our Doctors</h2>
            <ul>
                <li>Dr. Akosua Okoro - Radiology</li>
                <li>Dr. Kwame Mensah - Cardiology</li>
                <li>Dr. Ama Boateng - General Medicine</li>
                <li>Dr. Kojo Asante - Surgery</li>
            </ul>
        </section>

        <section id="emergency-contact">
            <h2>Emergency Contact</h2>
            <p>For emergencies, please call: <strong>+1-800-555-1234</strong></p>
            <p>Or visit our emergency department 24/7 at HiveNova Hospital.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 HiveNova Hospital. All rights reserved.</p>
    </footer>
</body>

</html>