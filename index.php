<!DOCTYPE html>
<html>
<head>
    <title>PHP Display Data</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php
        // Load the database configuration
        require 'db_config.php';

        // Build the SQL SELECT statement
        $sql = "SELECT id, username, email, created_at FROM users";

        // Execute the query, the result is a result object
        $result = $conn->query($sql);
    ?>

    <h1>User Records</h1>

    <?php
    // Check if any rows were returned
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Signed Up</th></tr>";

        // --- Loop through the results (Fetch data row by row) ---
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["username"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["created_at"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }

    // Close the connection
    $conn->close();
    ?>
</body>
</html>