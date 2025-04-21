<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION["role"] !== "Doctor") {
    header("Location: index.php");
    exit();
}


$conn = new mysqli("localhost", "vulnuser", "vulnpassword", "hivenova");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION["user"] ?? '';

$name_sql = "SELECT Name FROM staff WHERE username = ?";
$stmt = $conn->prepare($name_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$name_result = $stmt->get_result();

$doctor_name = '';
if ($name_result && $name_result->num_rows > 0) {
    $row = $name_result->fetch_assoc();
    $doctor_name = $row['Name'];
}

// Handle adding a new patient record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $illness = $_POST['illness'] ?? '';
    $last_visit = $_POST['last_visit'] ?? '';
    $doctor = $doctor_name;
    $username = $_SESSION["user"] ?? '';

    if ($name && $age && $illness && $last_visit) {
        $insert_stmt = $conn->prepare("INSERT INTO patient_records (Name, Age, Illness, LastVisit, Doctor, username) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sissss", $name, $age, $illness, $last_visit, $doctor, $username);
        $insert_stmt->execute();
        $insert_stmt->close();
        header("Location: doctor_dashboard.php");
        exit();
    }
}

// Handle appointment approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_action']) && isset($_POST['appointment_id'])) {
    $action = $_POST['appointment_action'];
    $appointment_id = intval($_POST['appointment_id']);

    if ($action === 'approve') {
        $update_stmt = $conn->prepare("UPDATE appointment_schedules SET status = 'approved' WHERE id = ?");
        $update_stmt->bind_param("i", $appointment_id);
        $update_stmt->execute();
        $update_stmt->close();
    } elseif ($action === 'reject') {
        $update_stmt = $conn->prepare("UPDATE appointment_schedules SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $appointment_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: doctor_dashboard.php");
    exit();
}

$appointment_sql = "SELECT * FROM appointment_schedules WHERE doctor = ? AND status IN ('pending', 'approved')";
$appointment_stmt = $conn->prepare($appointment_sql);
$appointment_stmt->bind_param("s", $doctor_name);
$appointment_stmt->execute();
$appointment_result = $appointment_stmt->get_result();

// Handle deleting a patient record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient_id'])) {
    $delete_id = intval($_POST['delete_patient_id']);
    $delete_stmt = $conn->prepare("DELETE FROM patient_records WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: doctor_dashboard.php");
    exit();
}

$username = $_SESSION["user"] ?? '';

$name_sql = "SELECT Name FROM staff WHERE username = ?";
$stmt = $conn->prepare($name_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$name_result = $stmt->get_result();

$doctor_name = '';
if ($name_result && $name_result->num_rows > 0) {
    $row = $name_result->fetch_assoc();
    $doctor_name = $row['Name'];
}

$patient_sql = "SELECT * FROM patient_records WHERE Doctor = ?";
$patient_stmt = $conn->prepare($patient_sql);
$patient_stmt->bind_param("s", $doctor_name);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();

// Handle sending chat message (fixed SQL injection)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $recipient = $_POST['recipient'];
    $message = $_POST['message'];
    $sender = $doctor_name;
    $sent_at = date('Y-m-d H:i:s');

    // Input validation and sanitization
    $recipient = trim($recipient);
    $message = trim($message);
    if (empty($recipient) || empty($message)) {
        echo "Recipient and message cannot be empty.";
        exit();
    }

    $insert_sql = "INSERT INTO messages (sender, recipient, message, sent_at, is_read) VALUES (?, ?, ?, ?, 0)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $sender, $recipient, $message, $sent_at);
    if ($insert_stmt->execute()) {
        echo "Message sent";
    } else {
        echo "Error sending message: " . $conn->error;
    }
    exit();
}

// Handle fetching messages for a chat (fixed SQL injection)
if (isset($_GET['action']) && $_GET['action'] === 'get_messages' && isset($_GET['recipient'])) {
    $recipient = $_GET['recipient'];
    $sender = $doctor_name;

    $select_sql = "SELECT sender, recipient, message, sent_at FROM messages WHERE (sender = ? AND recipient = ?) OR (sender = ? AND recipient = ?) ORDER BY sent_at ASC";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("ssss", $sender, $recipient, $recipient, $sender);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    $messages = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }

    // Mark messages as read where recipient is current user
    $update_sql = "UPDATE messages SET is_read = 1 WHERE sender = ? AND recipient = ? AND is_read = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $recipient, $sender);
    $update_stmt->execute();

    header('Content-Type: application/json');
    echo json_encode($messages);
    exit();
}

// Handle fetching recent chats for sidebar (fixed SQL injection)
if (isset($_GET['action']) && $_GET['action'] === 'get_recent_chats') {
    $chats = [];
    $sql = "SELECT sender, recipient, message, sent_at, is_read FROM messages WHERE sender = ? OR recipient = ? ORDER BY sent_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $doctor_name, $doctor_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $chatMap = [];
        while ($row = $result->fetch_assoc()) {
            $otherUser = ($row['sender'] === $doctor_name) ? $row['recipient'] : $row['sender'];
            if (!isset($chatMap[$otherUser])) {
                $chatMap[$otherUser] = [
                    'name' => $otherUser,
                    'profile' => 'default-profile.png',
                    'lastMessage' => $row['message'],
                    'unread' => 0
                ];
            }
            if ($row['recipient'] === $doctor_name && $row['is_read'] == 0) {
                $chatMap[$otherUser]['unread'] += 1;
            }
        }
        $chats = array_values($chatMap);
    }
    header('Content-Type: application/json');
    echo json_encode($chats);
    exit();
}

// Handle fetching staff list for new chat excluding receptionists and users (fixed SQL injection)
if (isset($_GET['action']) && $_GET['action'] === 'get_staff') {
    $docs = [];
    $staff_sql = "SELECT Name, Role FROM staff WHERE LOWER(TRIM(Role)) NOT IN ('receptionist', 'user')";
    $staff_stmt = $conn->prepare($staff_sql);
    $staff_stmt->execute();
    $staff_result = $staff_stmt->get_result();

    if ($staff_result && $staff_result->num_rows > 0) {
        while ($staff = $staff_result->fetch_assoc()) {
            $docs[] = ['name' => $staff['Name']];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($docs);
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Doctor Dashboard - HiveNova Medical</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px 40px;
            background: #ffffffcc;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        h1 {
            color: #007c91;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2.4rem;
            letter-spacing: 0.05em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1rem;
        }

        th,
        td {
            padding: 14px 18px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #00bfa5;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background-color: #f0f9f8;
            transition: background-color 0.3s ease;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 28px;
            background-color: #00bfa5;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 6px 15px rgba(0, 191, 165, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #008c7e;
            box-shadow: 0 8px 20px rgba(0, 140, 126, 0.6);
        }

        /* Chat Messages */
        .chat-messages {
            padding: 10px;
            background-color: #ffffff;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .chat-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.sent {
            background-color: #d1f7c4;
            align-self: flex-end;
            text-align: right;
        }

        .chat-message.received {
            background-color: #f1f0f0;
            align-self: flex-start;
            text-align: left;
        }

        .chat-sender {
            font-weight: bold;
            display: block;
        }

        .chat-timestamp {
            font-size: 0.8rem;
            color: #888;
            display: block;
        }

        .chat-text {
            margin-top: 5px;
        }

        .recent-chat-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }

        .recent-chat-item:hover {
            background-color: #f0f9f8;
        }

        .chat-profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .chat-info {
            display: flex;
            flex-direction: column;
        }

        .chat-name {
            font-weight: bold;
        }

        .chat-preview {
            color: #888;
            font-size: 0.9rem;
        }

        /* Chat Box Styles */
        .expanded-chat {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: #ffffffcc;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            z-index: 1200;
        }

        .chat-header {
            background-color: #00bfa5;
            color: white;
            padding: 10px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 15px 15px 0 0;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }

        .chat-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.sent {
            background-color: #d1f7c4;
            align-self: flex-end;
            text-align: right;
        }

        .chat-message.received {
            background-color: #f1f0f0;
            align-self: flex-start;
            text-align: left;
        }

        .chat-sender {
            font-weight: bold;
            display: block;
        }

        .chat-timestamp {
            font-size: 0.8rem;
            color: #888;
            display: block;
        }

        .chat-text {
            margin-top: 5px;
        }

        .expanded-chat-form {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
        }

        .expanded-chat-form input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        .expanded-chat-form button {
            background-color: #00bfa5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .expanded-chat-form button:hover {
            background-color: #008c7e;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <h1>Welcome Doctor <?php echo htmlspecialchars($_SESSION["user"]); ?></h1>
        <h2>Your Patient Records</h2>
        <form method="POST" style="margin-bottom: 20px;">
            <input type="hidden" name="add_patient" value="1" />
            <input type="text" name="name" placeholder="Patient Name" required />
            <input type="number" name="age" placeholder="Age" required min="0" />
            <input type="text" name="illness" placeholder="Illness" required />
            <input type="date" name="last_visit" placeholder="Last Visit Date" required />
            <button type="submit" style="background-color: #00bfa5; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Add Patient Record</button>
        </form>
        <?php if ($patient_result && $patient_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Patient Name</th>
                        <th>Age</th>
                        <th>Illness</th>
                        <th>Last Visit</th>
                        <th>Doctor</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $patient_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Age']); ?></td>
                            <td><?php echo htmlspecialchars($row['Illness']); ?></td>
                            <td><?php echo htmlspecialchars($row['LastVisit']); ?></td>
                            <td><?php echo htmlspecialchars($row['Doctor']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this patient record?');">
                                    <input type="hidden" name="delete_patient_id" value="<?php echo htmlspecialchars($row['id']); ?>" />
                                    <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No patient records found.</p>
        <?php endif; ?>

        <h2>Pending Appointments</h2>
        <?php if ($appointment_result && $appointment_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient Username</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = $appointment_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['username']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>" />
                                    <button type="submit" name="appointment_action" value="approve" style="background-color: #00bfa5; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Approve</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>" />
                                    <button type="submit" name="appointment_action" value="reject" style="background-color: #ff4d4d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending appointments.</p>
        <?php endif; ?>

        <h2>Uploaded Lab Results</h2>
        <?php
        $upload_dir = __DIR__ . '/uploads/';
        $files = [];
        if (is_dir($upload_dir)) {
            $files = array_diff(scandir($upload_dir), ['.', '..']);
        }
        ?>
        <?php if (!empty($files)): ?>
            <ul>
                <?php foreach ($files as $file): ?>
                    <li><a href="doctor_dashboard.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No lab results uploaded yet.</p>
        <?php endif; ?>

        <!-- Chat Icon Button -->
        <button id="chatIconBtn" class="chat-icon-btn" aria-label="Open chat" type="button"
            style="position: fixed; bottom: 20px; right: 20px; z-index: 1100; background: #00bfa5; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer;">
            <svg width="24" height="24" fill="#fff" viewBox="0 0 24 24" aria-hidden="true" style="margin: 13px;">
                <path d="M2 3h20v14H5l-3 3V3z" />
            </svg>
        </button>

        <!-- Chat Sidebar -->
        <div id="chatSidebar" class="chat-sidebar"
            style="display:none; position: fixed; bottom: 80px; right: 20px; width: 320px; height: 400px; background: #ffffffcc; border-radius: 15px; box-shadow: 0 12px 30px rgba(0,0,0,0.12); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; z-index: 1000; flex-direction: column; overflow: hidden;">
            <div class="chat-sidebar-header"
                style="background-color: #00bfa5; color: white; padding: 10px; font-weight: 700; display: flex; justify-content: space-between; align-items: center;">
                <span>Chats</span>
                <button id="closeSidebarBtn" aria-label="Close chat sidebar"
                    style="background: transparent; border: none; color: white; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <ul id="recentChats" class="recent-chats"
                style="list-style: none; padding: 10px; margin: 0; overflow-y: auto; flex-grow: 1;">
                <!-- Recent chats will be dynamically populated here -->
            </ul>
        </div>

        <!-- Expanded Chat Window -->
        <div id="expandedChat" class="expanded-chat" style="display:none;" aria-label="Expanded Chat Window">
            <div class="chat-header">
                <span id="chatWithName"></span>
                <button id="closeExpandedChat" aria-label="Close chat">&times;</button>
            </div>
            <div id="expandedChatMessages" class="chat-messages" style="overflow-y: auto; max-height: 300px;">
                <!-- Messages will be dynamically appended here -->
            </div>
            <form id="expandedChatForm" class="expanded-chat-form">
                <input type="text" id="expandedChatInput" placeholder="Type your message..." autocomplete="off" />
                <button type="submit" aria-label="Send message">Send</button>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chatIconBtn = document.getElementById('chatIconBtn');
                const chatSidebar = document.getElementById('chatSidebar');
                const closeSidebarBtn = document.getElementById('closeSidebarBtn');
                const recentChatsList = document.getElementById('recentChats');
                const expandedChat = document.getElementById('expandedChat');
                const closeExpandedChatBtn = document.getElementById('closeExpandedChat');
                const chatWithName = document.getElementById('chatWithName');
                const expandedChatMessages = document.getElementById('expandedChatMessages');
                const expandedChatForm = document.getElementById('expandedChatForm');
                const expandedChatInput = document.getElementById('expandedChatInput');

                let selectedChatUser = null;

                chatIconBtn.addEventListener('click', () => {
                    if (chatSidebar.style.display === 'none' || chatSidebar.style.display === '') {
                        chatSidebar.style.display = 'flex';
                        loadRecentChats();
                    } else {
                        chatSidebar.style.display = 'none';
                    }
                });

                closeSidebarBtn.addEventListener('click', () => {
                    chatSidebar.style.display = 'none';
                });

                closeExpandedChatBtn.addEventListener('click', () => {
                    expandedChat.style.display = 'none';
                    selectedChatUser = null;
                });

                async function loadRecentChats() {
                    try {
                        const response = await fetch('doctor_dashboard.php?action=get_recent_chats');
                        const chats = await response.json();
                        recentChatsList.innerHTML = '';
                        chats.forEach(chat => {
                            const li = document.createElement('li');
                            li.classList.add('recent-chat-item');
                            li.tabIndex = 0;
                            li.textContent = chat.name + ': ' + chat.lastMessage;
                            if (chat.unread > 0) {
                                const badge = document.createElement('span');
                                badge.textContent = chat.unread;
                                badge.style.backgroundColor = 'red';
                                badge.style.color = 'white';
                                badge.style.borderRadius = '50%';
                                badge.style.padding = '2px 6px';
                                badge.style.marginLeft = '10px';
                                li.appendChild(badge);
                            }
                            li.addEventListener('click', () => {
                                openExpandedChat(chat.name);
                            });
                            li.addEventListener('keypress', (e) => {
                                if (e.key === 'Enter') {
                                    openExpandedChat(chat.name);
                                }
                            });
                            recentChatsList.appendChild(li);
                        });
                    } catch (error) {
                        console.error('Error loading recent chats:', error);
                    }
                }

                function openExpandedChat(user) {
                    selectedChatUser = user;
                    chatWithName.textContent = user;
                    expandedChat.style.display = 'flex';
                    chatSidebar.style.display = 'none';
                    loadChatMessages(user);
                }

                async function loadChatMessages(user) {
                    expandedChatMessages.innerHTML = '';
                    try {
                        const response = await fetch(`doctor_dashboard.php?action=get_messages&recipient=${encodeURIComponent(user)}`);
                        const messages = await response.json();
                        messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.classList.add('chat-message');
                            div.classList.add(msg.sender === '<?php echo $doctor_name; ?>' ? 'sent' : 'received');
                            div.innerHTML = `<span class="chat-sender">${msg.sender}</span> <span class="chat-timestamp">${msg.sent_at}</span>: <span class="chat-text">${msg.message}</span>`;
                            expandedChatMessages.appendChild(div);
                        });
                        expandedChatMessages.scrollTop = expandedChatMessages.scrollHeight;
                    } catch (error) {
                        console.error('Error loading messages:', error);
                    }
                }

                expandedChatForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    if (!selectedChatUser) {
                        alert('Please select a chat.');
                        return;
                    }
                    const message = expandedChatInput.value.trim();
                    if (message === '') return;

                    try {
                        const response = await fetch('doctor_dashboard.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=send_message&recipient=${encodeURIComponent(selectedChatUser)}&message=${encodeURIComponent(message)}`
                        });
                        const text = await response.text();
                        if (text === 'Message sent') {
                            expandedChatInput.value = '';
                            loadChatMessages(selectedChatUser);
                        } else {
                            alert('Error sending message: ' + text);
                        }
                    } catch (error) {
                        alert('Failed to send message.');
                    }
                });
            });
        </script>
</body>

</html>
<?php
$conn->close();
?>