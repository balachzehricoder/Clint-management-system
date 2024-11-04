<?php
// Include your database connection file
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $servises = $_POST['servises'];

    // Allowed file types
    $allowedFileTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

    // Check if a file was uploaded without errors
    if (isset($_FILES['contact_image'])) {
        // Check for file upload errors
        if ($_FILES['contact_image']['error'] === UPLOAD_ERR_OK) {
            // File details
            $fileTmpPath = $_FILES['contact_image']['tmp_name'];
            $fileName = $_FILES['contact_image']['name'];
            $fileSize = $_FILES['contact_image']['size'];
            $fileType = $_FILES['contact_image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Validate file extension
            if (in_array($fileExtension, $allowedFileTypes)) {
                // Directory where the file will be saved
                $uploadFileDir = 'uploads/';
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                // Move the file to the specified directory
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Insert the data into the database
                    $sql = "INSERT INTO customers (first_name, last_name, email, mobile, contact_image, servises) 
                            VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $mobile, $dest_path, $servises);

                    if ($stmt->execute()) {
                        echo "Customer and file successfully saved.";
                        header('Location: create.html');
                        exit(); // It's good practice to call exit after redirecting
                    } else {
                        echo "Database error: " . $conn->error;
                    }
                } else {
                    echo "File upload error. Please check the permissions for the upload directory.";
                }
            } else {
                echo "Unsupported file type. Allowed types: " . implode(", ", $allowedFileTypes);
            }
        } else {
            // Handle file upload errors
            switch ($_FILES['contact_image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "File size is too large.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "File was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "No file was uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "A PHP extension stopped the file upload.";
                    break;
                default:
                    echo "Unknown error during file upload.";
                    break;
            }
        }
    } else {
        echo "No file uploaded.";
    }
}
?>
