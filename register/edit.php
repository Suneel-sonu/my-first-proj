<?php
// --- 1. SETUP AND CONNECTION ---
require '../db_config.php'; 

$status_message = "";
$user = null;
$user_id = null;

// Check if an ID was passed in the URL (GET request)
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // --- A. READ EXISTING DATA (Secure SELECT) ---
    // Prepare the SELECT statement to fetch the user's current data
    $sql_select = "SELECT id, username, email FROM users WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $user_id); 
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        $status_message = "<p style='color:red;'>User not found!</p>";
        // If user ID is bad, stop and redirect back to the main page
        header("Location: index.php");
        exit(); 
    }
    $stmt_select->close();
} elseif (isset($_POST['update'])) { 
    // --- B. PROCESS UPDATE (POST Request) ---

    $user_id = $_POST['id'];
    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);

    // Basic validation
    if (empty($new_username) || empty($new_email)) {
         $status_message = "<p style='color:red;'>**ERROR:** Both fields are required for update!</p>";
    } else {

        // --- SECURE UPDATE QUERY using Prepared Statements ---
        $sql_update = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);

        // "ssi": two strings (username, email) and one integer (id)
        $stmt_update->bind_param("ssi", $new_username, $new_email, $user_id); 

        if ($stmt_update->execute()) {
            // Success: Redirect the user back to the main page with a success message
            header("Location: index.php?update_success=true&id=" . $user_id);
            exit();
        } else {
            $status_message = "<p style='color:red;'>Update failed: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
    }

} else {
    // If no ID is provided, just go back to the main page
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User ID: <?php echo $user_id; ?></title>
</head>
<body>
    <h1>Edit User: <?php echo $user_id; ?></h1>

    <?php echo $status_message; ?>

    <?php if ($user): // Only display the form if a user record was found ?>
        <form method="POST" action="edit.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">

            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>

            <input type="submit" name="update" value="Save Changes">
            <a href="index.php">Cancel</a>
        </form>
    <?php endif; ?>
</body>
</html>