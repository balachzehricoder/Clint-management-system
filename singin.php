<?php
// Include your database connection file
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password']; // Keep it as plain text

    // Debugging: output the username and password
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password: " . htmlspecialchars($password) . "<br>";

    // Prepare a select statement
    $sql = "SELECT * FROM admin WHERE name = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password); // Verify with plain text password
    $stmt->execute();
    $result = $stmt->get_result();

    // Debugging: Show the number of rows returned
    echo "Number of rows returned: " . $result->num_rows . "<br>";

    // Check if user exists
    if ($result->num_rows === 1) {
        echo "Login successful!";
        // Start a session and redirect
        session_start();
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php'); // Redirect to a dashboard or home page
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>
