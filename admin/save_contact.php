<?php
require_once '../database/starroofing_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $email     = trim($_POST['email']);
    $message   = trim($_POST['message']);

    // Validation: empty fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($message)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'All fields are required.'
            });
        </script>";
        exit();
    }

    // Validation: email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.'
            });
        </script>";
        exit();
    }

    // Escape values
    $firstname = $conn->real_escape_string($firstname);
    $lastname  = $conn->real_escape_string($lastname);
    $email     = $conn->real_escape_string($email);
    $message   = $conn->real_escape_string($message);

    // Insert into DB
    $sql = "INSERT INTO contact_messages (firstname, lastname, email, message) 
            VALUES ('$firstname', '$lastname', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Message Sent',
                text: 'Thank you! Your message has been successfully sent.'
            }).then(() => {
                document.getElementById('contactForm').reset();
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error',
                text: 'There was a problem saving your message. Please try again.'
            });
        </script>";
    }

    $conn->close();
}
?>
