<!DOCTYPE html>
<html>
<head>
    <title>User Management (Secure)</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        th { background-color: #f2f2f2; }
        .delete-btn {
            background-color: red; 
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <?php
        // IMPORTANT: Use '../' to look one directory up for db_config.php
        require '../db_config.php'; 

        $username = $email = "";
        $status_message = "";

        // --- 1. CHECK FOR SUCCESS MESSAGE FROM EDIT.PHP (NEW) ---
        if (isset($_GET['update_success']) && $_GET['update_success'] == 'true' && isset($_GET['id'])) {
            $status_message = "<p style='color:green;'>User ID " . htmlspecialchars($_GET['id']) . " updated successfully!</p>";
        }

        // --- 2. CHECK IF FORM WAS SUBMITTED (POST Request) ---
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            // --- A. DELETE LOGIC ---
            if (isset($_POST['action']) && $_POST['action'] === 'Delete' && isset($_POST['user_id'])) {
                $user_id_to_delete = $_POST['user_id'];
                
                // SECURE DELETE QUERY using Prepared Statements
                $sql_delete = "DELETE FROM users WHERE id = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                
                if ($stmt_delete === FALSE) {
                    $status_message = "<p style='color:red;'>Delete prepare failed: " . $conn->error . "</p>";
                } else {
                    $stmt_delete->bind_param("i", $user_id_to_delete); // "i" for integer ID
                    
                    if ($stmt_delete->execute()) {
                        $status_message = "<p style='color:green;'>User ID $user_id_to_delete deleted successfully!</p>";
                    } else {
                        $status_message = "<p style='color:red;'>Delete execution error: " . $stmt_delete->error . "</p>";
                    }
                    $stmt_delete->close();
                }
                
            } 
            
            // --- B. REGISTRATION/INSERT LOGIC ---
            elseif (isset($_POST['username']) || isset($_POST['email'])) { // Only proceed if the registration form was submitted

                // CAPTURE AND CLEAN THE DATA 
                $username = htmlspecialchars($_POST["username"]);
                $email = htmlspecialchars($_POST["email"]);
                
                // Basic Validation
                if (empty($username) || empty($email)) {
                    $status_message = "<p style='color:red;'>**ERROR:** Both username and email are required!</p>";
                } else {
                    // SECURE INSERT QUERY using Prepared Statements 
                    $sql_insert = "INSERT INTO users (username, email) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql_insert);
                    
                    if ($stmt === FALSE) {
                        $status_message = "<p style='color:red;'>Prepare failed: " . $conn->error . "</p>";
                    } else {
                        $stmt->bind_param("ss", $username, $email);
                        
                        if ($stmt->execute()) {
                            $status_message = "<p style='color:green;'>Data successfully captured and inserted (Securely)! New ID: " . $stmt->insert_id . "</p>";
                            $username = $email = ""; // Clear fields after success
                        } else {
                            $status_message = "<p style='color:red;'>Database execution error: " . $stmt->error . "</p>";
                        }
                        $stmt->close();
                    }
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
    
    <hr>
    
    <?php 
        // --- 4. DISPLAY ALL USERS (READ Logic) ---
        echo "<h2>Current Users</h2>";
        // Select and order by ID descending to show the newest users first
        $sql_select = "SELECT id, username, email FROM users ORDER BY id DESC";
        $result = $conn->query($sql_select);
        
        if ($result->num_rows > 0) {
            echo "<table>";
            // UPDATED HEADER ROW (ADDED 'Edit' column)
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Edit</th><th>Delete</th></tr>";
            
            // UPDATED WHILE LOOP (with Edit Link and Delete Form)
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["username"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";

                // EDIT LINK
                echo "<td>";
                echo "<a href='edit.php?id=" . $row["id"] . "'>Edit</a>"; 
                echo "</td>";

                // DELETE FORM
                echo "<td>";
                echo "<form method='POST' action=''>";
                echo "<input type='hidden' name='user_id' value='" . $row["id"] . "'>"; // Hidden ID to identify row
                echo "<input type='submit' name='action' value='Delete' class='delete-btn'>";
                echo "</form>";
                echo "</td>";

                echo "</tr>";
            }
            // *** MISSING CLOSING TAGS CORRECTED HERE ***
            echo "</table>"; 
        } else {
            echo "<p>No users registered yet.</p>";
        }
        
        // Close the connection
        $conn->close();
    ?>
</body>
</html>