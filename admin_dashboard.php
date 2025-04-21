<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Honey Pot</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');
        body {
            font-family: 'Share Tech Mono', monospace;
            background-color: #000;
            color: #00ff00;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            text-shadow: 0 0 5px #00ff00;
        }
        h1 {
            color: #00ff00;
            margin-bottom: 10px;
            text-shadow: 0 0 10px #00ff00;
        }
        .info-box {
            background-color: #001100;
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px #00ff00;
        }
        .info-box h2 {
            margin-top: 0;
            color: #00ff00;
            text-shadow: 0 0 5px #00ff00;
        }
        .info-item {
            margin: 10px 0;
            font-size: 1.1em;
        }
        .comment {
            font-style: italic;
            color: #0f0;
            margin-top: 30px;
            text-align: center;
            text-shadow: 0 0 5px #00ff00;
        }
        .hacker-symbols {
            font-size: 2em;
            color: #0f0;
            text-align: center;
            margin-bottom: 20px;
            animation: flicker 1.5s infinite alternate;
        }
        @keyframes flicker {
            0% { opacity: 1; }
            100% { opacity: 0.5; }
        }
    </style>
    <script>
        function getScreenSize() {
            return window.innerWidth + " x " + window.innerHeight;
        }
        function getUserAgent() {
            return navigator.userAgent;
        }
        function getLanguage() {
            return navigator.language || navigator.userLanguage;
        }
        function getPlatform() {
            return navigator.platform;
        }
        function getCookiesEnabled() {
            return navigator.cookieEnabled ? "Yes" : "No";
        }
        function getOnlineStatus() {
            return navigator.onLine ? "Online" : "Offline";
        }
        function displayDeviceInfo() {
            document.getElementById("screenSize").textContent = getScreenSize();
            document.getElementById("userAgent").textContent = getUserAgent();
            document.getElementById("language").textContent = getLanguage();
            document.getElementById("platform").textContent = getPlatform();
            document.getElementById("cookiesEnabled").textContent = getCookiesEnabled();
            document.getElementById("onlineStatus").textContent = getOnlineStatus();
        }
        window.onload = displayDeviceInfo;
    </script>
</head>
<body>
    <div class="hacker-symbols">
        >_ &nbsp; 01010100 01110010 01100001 01100011 01101011 01101001 01101110 01100111 00100000 01001001 01101110 01110100 01101111 00100000 01010011 01111001 01110011 01110100 01100101 01101101
    </div>
    <h1>Admin Dashboard (FAKE)</h1>
    <div class="info-box">
        <h2>Your Device Information</h2>
        <div class="info-item"><strong>IP Address:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></div>
        <div class="info-item"><strong>Screen Size:</strong> <span id="screenSize">Loading...</span></div>
        <div class="info-item"><strong>User Agent:</strong> <span id="userAgent">Loading...</span></div>
        <div class="info-item"><strong>Language:</strong> <span id="language">Loading...</span></div>
        <div class="info-item"><strong>Platform:</strong> <span id="platform">Loading...</span></div>
        <div class="info-item"><strong>Cookies Enabled:</strong> <span id="cookiesEnabled">Loading...</span></div>
        <div class="info-item"><strong>Online Status:</strong> <span id="onlineStatus">Loading...</span></div>
    </div>

    <style>
        /* Warning message styles */
        .warning-message {
            background-color: #ff0000;
            color: #fff;
            padding: 15px;
            margin: 10px auto;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 0 10px #ff0000;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 10px #ff0000; }
            50% { box-shadow: 0 0 20px #ff4444; }
            100% { box-shadow: 0 0 10px #ff0000; }
        }
        /* Fake system logs */
        .fake-logs {
            background-color: #111;
            color: #0f0;
            font-family: monospace;
            padding: 15px;
            margin: 10px auto;
            width: 90%;
            max-width: 600px;
            height: 150px;
            overflow-y: scroll;
            border: 1px solid #0f0;
            border-radius: 5px;
            box-shadow: 0 0 10px #0f0;
        }
        /* Misleading statistics */
        .stats {
            background-color: #001100;
            color: #0f0;
            padding: 15px;
            margin: 10px auto;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 0 10px #0f0;
            font-family: monospace;
        }
        /* Countdown timer */
        .countdown {
            font-size: 1.5em;
            color: #ff0000;
            text-align: center;
            margin: 20px auto;
            font-weight: bold;
            text-shadow: 0 0 10px #ff0000;
        }
        /* Fake law enforcement notice */
        .law-enforcement {
            background-color: #220000;
            color: #ff4444;
            padding: 15px;
            margin: 10px auto;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 0 15px #ff4444;
            font-weight: bold;
            text-align: center;
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
    <p class="warning-message">WARNING: Unauthorized access detected! Security team has been notified.</p>
    <div class="fake-logs" id="fakeLogs">
        [2025-04-20 03:14:07] ALERT: Multiple failed login attempts detected.<br/>
        [2025-04-20 03:14:10] WARNING: Suspicious activity from IP <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?>.<br/>
        [2025-04-20 03:14:15] INFO: System scan initiated.<br/>
        [2025-04-20 03:14:20] ERROR: Unauthorized file access attempt blocked.<br/>
        [2025-04-20 03:14:25] ALERT: Potential malware detected.<br/>
        [2025-04-20 03:14:30] INFO: Security protocols updated.<br/>
        [2025-04-20 03:14:35] WARNING: Network anomaly detected.<br/>
        [2025-04-20 03:14:40] ALERT: Hacker activity logged.<br/>
    </div>
    <div class="stats">
        <p>Intrusion Attempts: 27</p>
        <p>Active Monitoring: ENABLED</p>
        <p>Data Capture: ACTIVE</p>
        <p>System Integrity: COMPROMISED</p>
        <p>Last Breach: 2025-04-20 03:14:07</p>
    </div>
    <div class="law-enforcement">
        NOTICE: This system is monitored by the Cyber Crime Unit. Unauthorized access is a criminal offense and will be prosecuted to the fullest extent of the law.
    </div>
    <div class="countdown" id="countdownTimer">Authorities arriving in: 00:10:00</div>
    <script>
        // Countdown timer script
        let countdownSeconds = 600; // 5 minutes
        const countdownElement = document.getElementById('countdownTimer');
        function updateCountdown() {
            let minutes = Math.floor(countdownSeconds / 60);
            let seconds = countdownSeconds % 60;
            countdownElement.textContent = `Authorities arriving in: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            if (countdownSeconds > 0) {
                countdownSeconds--;
                setTimeout(updateCountdown, 1000);
            } else {
                countdownElement.textContent = "Authorities have arrived.";
            }
        }
        updateCountdown();
    </script>
    <p class="comment"> Ctrl + U </p>
    <!-- <p class="comment">Note: The real admin login page is <code>normal_login.php</code></p> -->
</body>
</html>
