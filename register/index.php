<!DOCTYPE html>
<html>
<head>
    <title>User Registration Form (Secure)</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php
        // IMPORTANT: Use '../' to look one directory up for db_config.php
        require '../db_config.php'; 

        $username = $email = "";
        $status_message = "";

        // --- 1. CHECK IF FORM WAS SUBMITTED ---
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            // --- 2. CAPTURE AND CLEAN THE DATA ---
            $username = htmlspecialchars($_POST["username"]);
            $email = htmlspecialchars($_POST["email"]);
            
            // Basic Validation
            if (empty($username) || empty($email)) {
                $status_message = "<p style='color:red;'>**ERROR:** Both username and email are required!</p>";
            } else {
                // --- SECURE INSERT QUERY using Prepared Statements ---

                // 1. Prepare: Use '?' placeholders instead of direct variables
                $sql_insert = "INSERT INTO users (username, email) VALUES (?, ?)";
                $stmt = $conn->prepare($sql_insert);
                
                // Check if the prepare failed
                if ($stmt === FALSE) {
                    $status_message = "<p style='color:red;'>Prepare failed: " . $conn->error . "</p>";
                } else {
                    // 2. Bind: Tell the database what type the data is ("ss" = two strings)
                    $stmt->bind_param("ss", $username, $email);
                    
                    // 3. Execute: Send the secure data to the database
                    if ($stmt->execute()) {
                        $status_message = "<p style='color:green;'>Data successfully captured and inserted (Securely)! New ID: " . $stmt->insert_id . "</p>";
                        // Clear the form fields after successful submission
                        $username = $email = "";
                    } else {
                        $status_message = "<p style='color:red;'>Database execution error: " . $stmt->error . "</p>";
                    }

                    // 4. Close the statement
                    $stmt->close();
                }
            }
        }
    ?>

    <h1>Register New User</h1>

    <?php echo $status_message; // Display the success/error message ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" value="<?php echo $username; ?>"><br><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $email; ?>"><br><br>
        
        <input type="submit" value="Register User">
    </form>
    
    <?php 
        // --- 5. DISPLAY ALL USERS ---
        echo "<h2>Current Users</h2>";
        // Select and order by ID descending to show the newest users first
        $sql_select = "SELECT id, username, email FROM users ORDER BY id DESC";
        $result = $conn->query($sql_select);
        
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th></tr>";
            // Use while loop to fetch data row by row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["username"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users registered yet.</p>";
        }
        
        // Close the connection
        $conn->close();
    ?>
</body>
</html>