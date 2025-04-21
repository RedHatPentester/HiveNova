# 🏥 HIVE Nova Web App 🚀

Welcome to **HIVE Nova**, the buzzing heart of hospital management! 🐝 This web app is designed to make healthcare workflows smoother than a fresh jar of honey. Whether you're a doctor, nurse, technician, or admin, HIVE Nova has got your back!

---

## What is HIVE Nova? 🤔

HIVE Nova is a fun and functional hospital management system that helps manage appointments, patient records, staff dashboards, and more. It's like having a super organized hive where every bee knows exactly what to do! This project aims to streamline hospital operations, improve communication, and enhance patient care — all wrapped up in a neat, user-friendly package.

---

## Features 🛠️

- 🩺 **Role-Based Dashboards:** Tailored views for Doctors, Nurses, Technicians, and Admins. Everyone gets their own comfy hive corner!  
- 📅 **Appointment Scheduling:** Book, view, and manage appointments with ease. No more double-booking or buzzing confusion!  
- 🧾 **Patient Records:** Securely store and access patient information and diagnostic reports. Your digital honeycomb of health data.  
- 📨 **Messaging System:** Keep the staff connected with an internal messaging system. Share updates, alerts, and good vibes.  
- 🔐 **Secure Authentication:** Login and registration with role-based access control to keep the hive safe.  
- 📊 **Progress Summaries:** Visualize patient progress and hospital stats to keep track of the buzz.  
- 🏥 **Staff Management:** Manage hospital staff details and roles effortlessly.  

---

## Technology Stack 🧑‍💻

- **Backend:** PHP (because bees love a solid hive foundation)  
- **Frontend:** HTML, CSS, JavaScript (for that sweet user experience)  
- **Database:** MySQL (the nectar storage for all your data)  
- **Scripts:** Shell scripts for setup and running the app smoothly  

---

## How It Works 🐝

1. **Setup:** Run the setup.sh to create necessary tables and users in your database.  
2. **Configuration:** Use the setup shell script to configure environment variables and dependencies.  
3. **Launch:** Fire up the app with the run script and open it in your favorite browser.  
4. **Buzz Around:** Log in, navigate your dashboard, manage appointments, communicate with the doctors, and keep the hospital humming!  

---

## Installation 🐝

Ready to join the hive? Here's how to get started:

1. Clone this repo:  
   ```bash
   git clone https://github.com/RedHatPentester/HiveNova
   cd hivenova_web_app
   ```


2. Run the setup script to configure the environment:  
   ```bash
   ./setup.sh
   ```

3. Start the app by running:  
   ```bash
   ./run.sh
   ```

4. Open your browser and buzz over to `http://127.0.0.1:9000` (or your configured host)!

---

## Usage 🐝

- Log in with your credentials (or register if you're new to the hive).  
- Navigate through your dashboard tailored to your role.  
- Manage appointments, check patient records, send messages, and keep the hospital running like a well-oiled hive!  
- Admins can manage staff and oversee the entire operation.  

---

## Troubleshooting 🐞

- **Can't connect to the database?**  
  Double-check your database credentials and ensure the MySQL server is running.  
- **Setup script fails?**  
  Make sure you have execution permissions:  
  ```bash
  chmod +x setup.sh run.sh
  ```  
- **Pages not loading?**  
  Check your web server configuration and PHP error logs for clues.  

---


## Contributing 🍯

Got ideas to make the hive sweeter? Feel free to fork, branch out, and send a pull request! We love collaboration and fresh nectar of ideas.

---

## License 📜

This project is open-source and free as a bee in a meadow. Use it, share it, and make healthcare better!

---

Thanks for stopping by the hive! Keep buzzing and stay healthy! 🐝💛
