<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "User") {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION["user"];
$error = "";
$success = "";

// Handle new patient record submission - removed for User role

// Handle sending chat message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $recipient = $_POST['recipient'];
    $message = $_POST['message'];
    $sender = $username;
    $sent_at = date('Y-m-d H:i:s');

    // Vulnerable SQL injection (no prepared statements)
    $sql = "INSERT INTO messages (sender, recipient, message, sent_at, is_read) VALUES ('$sender', '$recipient', '$message', '$sent_at', 0)";
    if ($conn->query($sql) === TRUE) {
        echo "Message sent";
    } else {
        echo "Error sending message: " . $conn->error;
    }
    exit();
}

// Handle fetching messages for a chat
if (isset($_GET['action']) && $_GET['action'] === 'get_messages' && isset($_GET['recipient'])) {
    $recipient = $_GET['recipient'];
    $sender = $username;

    // Vulnerable SQL injection (no prepared statements)
    $sql = "SELECT sender, recipient, message, sent_at FROM messages WHERE (sender = '$sender' AND recipient = '$recipient') OR (sender = '$recipient' AND recipient = '$sender') ORDER BY sent_at ASC";
    $result = $conn->query($sql);
    $messages = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }

    // Mark messages as read where recipient is current user
    $updateSql = "UPDATE messages SET is_read = 1 WHERE sender = '$recipient' AND recipient = '$sender' AND is_read = 0";
    $conn->query($updateSql);

    header('Content-Type: application/json');
    echo json_encode($messages);
    exit();
}

// Handle fetching recent chats for sidebar
if (isset($_GET['action']) && $_GET['action'] === 'get_recent_chats') {
    $chats = [];
    $sql = "SELECT sender, recipient, message, sent_at, is_read FROM messages WHERE sender = '$username' OR recipient = '$username' ORDER BY sent_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $chatMap = [];
        while ($row = $result->fetch_assoc()) {
            $otherUser = ($row['sender'] === $username) ? $row['recipient'] : $row['sender'];
            if (!isset($chatMap[$otherUser])) {
                $chatMap[$otherUser] = [
                    'name' => $otherUser,
                    'profile' => 'default-profile.png',
                    'lastMessage' => $row['message'],
                    'unread' => 0
                ];
            }
            if ($row['recipient'] === $username && $row['is_read'] == 0) {
                $chatMap[$otherUser]['unread'] += 1;
            }
        }
        $chats = array_values($chatMap);
    }
    header('Content-Type: application/json');
    echo json_encode($chats);
    exit();
}

// Handle fetching doctors for new chat
if (isset($_GET['action']) && $_GET['action'] === 'get_doctors') {
    $docs = [];
    $doctor_result = $conn->query("SELECT Name FROM staff WHERE LOWER(TRIM(Role)) = 'doctor'");
    if ($doctor_result && $doctor_result->num_rows > 0) {
        while ($doc = $doctor_result->fetch_assoc()) {
            $docs[] = ['name' => $doc['Name']];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($docs);
    exit();
}

$record_id = isset($_GET['record_id']) ? $_GET['record_id'] : null;

if ($record_id) {
    // IDOR vulnerability: no authorization check on record_id
    $sql = "SELECT * FROM patient_records WHERE id = $record_id";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare("SELECT * FROM patient_records WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard - HiveNova Medical</title>
    <link rel="stylesheet" type="text/css" href="user_dashboard.css" />
    <style>
        /* Styles for search bar and toggle button */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: var(--bg-color);
            border-bottom: 1px solid #ccc;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 400px;
        }

        .search-bar form {
            display: flex;
            gap: 8px;
        }

        .search-bar input[type="text"] {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .search-bar button {
            padding: 8px 16px;
            border: none;
            background-color: #00796b;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #004d40;
        }

        .toggle-switch {
            margin-left: 20px;
            cursor: pointer;
            user-select: none;
        }

        .toggle-switch input {
            display: block
        }

        .slider {
            position: relative;
            width: 50px;
            height: 24px;
            background-color: #ccc;
            border-radius: 24px;
            transition: background-color 0.3s;
        }

        .slider::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            left: 2px;
            top: 2px;
            background-color: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        input:checked+.slider {
            background-color: #00796b;
        }

        input:checked+.slider::before {
            transform: translateX(26px);
        }

        body.light-mode {
            --bg-color: #f5f5f5;
            --text-color: #222;
        }

        body.dark-mode {
            --bg-color: #222;
            --text-color: #eee;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="search-bar">
            <form method="get" action="?">
                <input type="text" name="search" placeholder="Enter patient name or illness" />
                <button type="submit">Search</button>
            </form>
        </div>
        <label class="toggle-switch" for="modeToggle">
            <input type="checkbox" id="modeToggle" />
            <span class="slider"></span>
        </label>
    </div>
    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <h2>Your Patient Records</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Patient Name</th>
                        <th>Age</th>
                        <th>Illness</th>
                        <th>Last Visit</th>
                        <th>Doctor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <?php if ($row['id'] == '1') continue; ?>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Age']); ?></td>
                    <td><?php echo htmlspecialchars($row['Illness']); ?></td>
                    <td><?php echo htmlspecialchars($row['LastVisit']); ?></td>
                    <td><?php echo htmlspecialchars($row['Doctor']); ?></td>
                </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No patient records found.</p>
        <?php endif; ?>



        <a href="logout.php" class="logout-btn">Logout</a>
    </div>


    <!-- Appointment Booking Section -->
    <div class="appointment-booking modern-appointment">
        <h3>Book an Appointment</h3>
        <form method="post" action="?">
            <div class="form-group">
                <label for="appointment_doctor">Select Doctor:</label>
                <select name="appointment_doctor" id="appointment_doctor" required class="doctor-dropdown">
                    <option value="">Select a doctor</option>
                    <?php
                    $doctor_result = $conn->query("SELECT Name, Department FROM staff WHERE LOWER(TRIM(Role)) = 'doctor'");
                    if ($doctor_result && $doctor_result->num_rows > 0) {
                        while ($doc = $doctor_result->fetch_assoc()) {
                            $display = htmlspecialchars($doc['Name']) . " (" . htmlspecialchars($doc['Department']) . ")";
                            $selected = (isset($_POST['appointment_doctor']) && $_POST['appointment_doctor'] === $doc['Name']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($doc['Name']) . '" ' . $selected . '>' . $display . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment_date">Date:</label>
                <input type="date" name="appointment_date" id="appointment_date" required />
            </div>

            <div class="form-group">
                <label for="appointment_time">Time:</label>
                <input type="time" name="appointment_time" id="appointment_time" required />
            </div>

            <button type="submit" name="book_appointment" class="btn-primary">Book Appointment</button>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
            $doctor = $_POST['appointment_doctor'];
            $date = $_POST['appointment_date'];
            $time = $_POST['appointment_time'];
            $user = $username;

            if (empty($doctor) || empty($date) || empty($time)) {
                echo '<p class="message error">Please fill in all appointment fields.</p>';
            } else {
                // Vulnerable SQL injection (no prepared statements)
                $sql = "INSERT INTO appointment_schedules (username, doctor, appointment_date, appointment_time, status) VALUES ('$user', '$doctor', '$date', '$time', 'pending')";
                if ($conn->query($sql) === TRUE) {
                    echo '<p class="message success">Appointment booked successfully.</p>';
                } else {
                    echo '<p class="message error">Error booking appointment: ' . $conn->error . '</p>';
                }
            }
        }
        ?>

        <h2>Your Appointments</h2>
        <?php
        $appt_sql = "SELECT * FROM appointment_schedules WHERE username = ?";
        $appt_stmt = $conn->prepare($appt_sql);
        $appt_stmt->bind_param("s", $username);
        $appt_stmt->execute();
        $appt_result = $appt_stmt->get_result();
        ?>
        <?php if ($appt_result && $appt_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appt = $appt_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appt['id']); ?></td>
                            <td><?php echo htmlspecialchars($appt['doctor']); ?></td>
                            <td><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appt['appointment_time']); ?></td>
                            <td>
                                <?php
                                $status = $appt['status'];
                                if ($status === 'pending') {
                                    echo '<span style="color: orange; font-weight: bold;">Pending</span>';
                                } elseif ($status === 'approved') {
                                    echo '<span style="color: green; font-weight: bold;">Approved</span>';
                                } elseif ($status === 'rejected') {
                                    echo '<span style="color: red; font-weight: bold;">Rejected</span>';
                                } else {
                                    echo htmlspecialchars(ucfirst($status));
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointments found.</p>
        <?php endif; ?>
    </div>

    <!-- Chat Sidebar -->
    <div id="chatSidebar" class="chat-sidebar collapsed" aria-label="Chat Sidebar">
        <div class="chat-header">
            <h3>Chats</h3>
            <button id="toggleSidebar" aria-label="Toggle chat sidebar">&#x25B6;</button>
        </div>
        <ul id="recentChats" class="recent-chats">
            <!-- Recent chats will be dynamically populated here -->
        </ul>
    </div>

    <!-- Expanded Chat Window -->
    <div id="expandedChat" class="expanded-chat" style="display:none;" aria-label="Expanded Chat Window">
        <div class="expanded-header">
            <button id="closeExpandedChat" aria-label="Close chat">&times;</button>
            <div class="chat-profile">
                <img src="default-profile.png" alt="Profile Picture" class="profile-pic" />
                <span id="chatWithName">Doctor Name</span>
            </div>
        </div>
        <div id="expandedChatMessages" class="expanded-chat-messages"></div>
        <form id="expandedChatForm" class="expanded-chat-form">
            <input type="text" id="expandedChatInput" placeholder="Type your message..." autocomplete="off" />
            <button type="submit" aria-label="Send message">Send</button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const chatSidebar = document.getElementById('chatSidebar');
edi        const closeExpandedChatBtn = document.getElementById('closeExpandedChat');

        toggleSidebarBtn.addEventListener('click', () => {
            chatSidebar.classList.toggle('collapsed');
            toggleSidebarBtn.innerHTML = chatSidebar.classList.contains('collapsed') ? '&#x25B6;' : '&#x25C0;';
        });

        // Open expanded chat window
        function openExpandedChat(doctorName) {
            selectedDoctor = doctorName;
            chatWithName.textContent = doctorName;
            expandedChat.style.display = 'flex';
            chatSidebar.classList.add('collapsed');
            toggleSidebarBtn.innerHTML = '&#x25B6;';
            loadExpandedChatHistory(doctorName);
        }

        // Close expanded chat window
        closeExpandedChatBtn.addEventListener('click', () => {
            expandedChat.style.display = 'none';
            selectedDoctor = null;
        });

        // Chat Icon Button and Sidebar Script

        const chatIconBtn = document.getElementById('chatIconBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const recentChatsList = document.getElementById('recentChats');
        const expandedChat = document.getElementById('expandedChat');
        const chatWithName = document.getElementById('chatWithName');
        const expandedChatMessages = document.getElementById('expandedChatMessages');
        const expandedChatForm = document.getElementById('expandedChatForm');
        const expandedChatInput = document.getElementById('expandedChatInput');

        let selectedChatUser = null;


            // Load doctors for chat
            const doctors = <?php
            $docs = [];
            $doctor_result = $conn->query("SELECT Name FROM staff WHERE LOWER(TRIM(Role)) = 'doctor'");
            if ($doctor_result && $doctor_result->num_rows > 0) {
                while ($doc = $doctor_result->fetch_assoc()) {
                    $docs[] = [
                        'name' => $doc['Name'],
                        'profile' => 'default-profile.png',
                        'lastMessage' => '',
                    ];
                }
            }
            echo json_encode($docs);
            ?>;

            let selectedDoctor = null;

            // Populate recent chats list with doctors
            function renderRecentChats() {
                recentChatsList.innerHTML = '';
                doctors.forEach(doc => {
                    const li = document.createElement('li');
                    li.classList.add('recent-chat-item');
                    li.tabIndex = 0;
                    li.innerHTML = `
                        <img src="${doc.profile}" alt="${doc.name} profile" class="chat-profile-pic" />
                        <div class="chat-info">
                            <span class="chat-name">${doc.name}</span>
                            <span class="chat-preview">${doc.lastMessage || 'No messages yet'}</span>
                        </div>
                    `;
                    li.addEventListener('click', () => {
                        openExpandedChat(doc.name);
                    });
                    li.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            openExpandedChat(doc.name);
                        }
                    });
                    recentChatsList.appendChild(li);
                });
            }

            // Fetch and display chat history in expanded chat
            async function loadExpandedChatHistory(doctor) {
                expandedChatMessages.innerHTML = '';
                const messages = await fetchMessages(doctor);
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.classList.add('chat-message');
                    div.classList.add(msg.sender === '<?php echo $username; ?>' ? 'sent' : 'received');
                    div.innerHTML = `<span class="chat-sender">${msg.sender}</span> <span class="chat-timestamp">${msg.sent_at}</span>: <span class="chat-text">${msg.message}</span>`;
                    expandedChatMessages.appendChild(div);
                });
                expandedChatMessages.scrollTop = expandedChatMessages.scrollHeight;
            }

            // Send message to server
            async function sendMessage(doctor, message) {
                const response = await fetch('user_dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=send_message&recipient=${encodeURIComponent(doctor)}&message=${encodeURIComponent(message)}`
                });
                return response.text();
            }

            // Fetch messages from server
            async function fetchMessages(doctor) {
                const response = await fetch(`user_dashboard.php?action=get_messages&recipient=${encodeURIComponent(doctor)}`);
                const data = await response.json();
                return data;
            }

            // Handle message form submit in expanded chat
            expandedChatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!selectedDoctor) {
                    alert('Please select a chat.');
                    return;
                }
                const message = expandedChatInput.value.trim();
                if (message === '') return;

                // Vulnerable: no input sanitization or escaping (XSS)
                await sendMessage(selectedDoctor, message);
                loadExpandedChatHistory(selectedDoctor);
                expandedChatInput.value = '';
            });

            // Initialize recent chats on page load
            renderRecentChats();
    });
    </script>

    <?php
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    ?>
    <script>
        // Toggle light/dark mode
        const toggle = document.getElementById('modeToggle');
        const body = document.body;

        // Load saved mode from localStorage
        if (localStorage.getItem('mode') === 'dark') {
            body.classList.add('dark-mode');
            toggle.checked = true;
        } else {
            body.classList.add('light-mode');
        }

        toggle.addEventListener('change', () => {
            if (toggle.checked) {
                body.classList.replace('light-mode', 'dark-mode');
                localStorage.setItem('mode', 'dark');
            } else {
                body.classList.replace('dark-mode', 'light-mode');
                localStorage.setItem('mode', 'light');
            }
        });
    </script>
</body>

</html>