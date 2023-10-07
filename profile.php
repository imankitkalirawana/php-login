<?php
require_once "config.php";
include_once "common.php";

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


$param_id = $_SESSION["id"];

if ($param_id) {

    $stmp = $link->prepare("SELECT * FROM users WHERE id=?");
    $stmp->bind_param("i", $param_id);
    $stmp->execute();
    $result = $stmp->get_result();
    $row = $result->fetch_assoc();
    // Script to update user details
    if (isset($_POST['update'])) {
        if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK) {
            // A file has been uploaded
            $target_dir = "assets/";
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Allow certain file formats
            if (
                $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" && $imageFileType != "pdf"
            ) {
                // echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
                $uploadOk = 1;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    header("Location: index.php");

                    // Insert filename into database
                    $filename = basename($_FILES["fileToUpload"]["name"]);
                    $username = $_POST['username'];
                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $id = $_POST['id'];
                    $stmt = $link->prepare("UPDATE users SET name=?, email=?, image=? WHERE id=?");
                    $stmt->bind_param("ssss", $name, $email, $filename, $id);
                    $stmt->execute();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        } else {
            // No file has been uploaded
            // Insert other form data into database
            $username = $_POST['username'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $id = $_POST['id'];
            $stmt = $link->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("sss", $name, $email, $id);
            $stmt->execute();
            header("Location: index.php");
        }
    }


    if (isset($_POST["delete_user"])) {
        if ($param_user_type === "admin") {
            echo '<div class="alert alert-danger">Cannot Delete Admin</div>';
        } else {
            echo "Deleted";

            $id = $_POST['id'];
            $stmt = $link->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            header("Location: logout.php");
        }
    }
?>

<?php
} else {
    echo "failed";
    echo $param_id;
}

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/css/common.css">
    <title>Profile</title>
</head>





<body>

    <div class="container">
        <div class="wrapper">

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <div class="mb-3">
                    <input class="form-control" type="text" name="username" placeholder="username" value="<?php echo $row['username']; ?>" disabled>
                </div>
                <div class="mb-3">
                    <input class="form-control" type="text" name="name" placeholder="Name" value="<?php echo $row['name']; ?>">
                </div>
                <div class="mb-3">
                    <input class="form-control" type="email" name="email" placeholder="Email" value="<?php echo $row['email']; ?>">
                </div>


                <div class="mb-3">
                    <input class="form-control" type="file" name="fileToUpload" value="<?php echo $row['image']; ?>">
                </div>
                <div class="card-body align-items-center">
                    <button type="button" class="btn btn-primary" name="update">Update</button>
                    <a href="/reset-password.php" class="btn btn-warning">Change Password</a>
                    <button type="button" class="btn btn-outline-danger" name="delete_user">Delete Profile</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </form>

        </div>
    </div>
</body>