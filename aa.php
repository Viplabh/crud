<?php
$insert = false;
$update = false;
$delete = false;
$editData = array();

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $sno = $_POST['delete'];
        $sql = "DELETE FROM `user` WHERE `sno` = $sno";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $delete = true;
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['edit'])) {
        $sno = $_POST['snoEdit'];
        $title = $_POST['titleEdit'];
        $description= $_POST['descriptionEdit'];

        $sql = "UPDATE `user` SET `title` = '$title', `description` = '$description' WHERE `sno` = $sno";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $update = true;
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }
}

$imageUploadPath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete']) && !isset($_POST['edit'])) {
    $imageFileName = $_FILES['image']['name'];
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageUploadPath = 'images/' . $imageFileName;

    if (move_uploaded_file($imageTmpName, $imageUploadPath)) {
        $title = $_POST["titleEdit"];
        $description = $_POST["descriptionEdit"];

        $sql = "INSERT INTO `user` (`title`, `description`, `image`) VALUES ('$title', '$description', '$imageFileName')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $insert = true;
        } else {
            echo "The record was not inserted successfully because of this error ---> " . mysqli_error($conn);
        }
    } else {
        echo "Image upload failed.";
    }
}

if (isset($_POST['edit'])) {
    $sno = $_POST['snoEdit'];
    $sql = "SELECT * FROM `user` WHERE `sno` = $sno";
    $result = mysqli_query($conn, $sql);
    $editData = mysqli_fetch_assoc($result);
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">

    <title>Notes</title>

    <style>
    .uploaded-image-container {
        max-width: 100px;
        max-height: 100px;
        overflow: hidden;
    }

    .uploaded-image-container img {
        width: 100%;
        height: auto;
    }
    </style>
</head>

<body>
    <?php
    if ($delete) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Data deleted successfully!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
              </div>';
    }
    ?>
    <div class="container my-4">
        <h2>Add a Note to Notes</h2>
        <form action="/cogent/crud/project.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="snoEdit" value="<?php echo isset($editData['sno']) ? $editData['sno'] : ''; ?>">
            <div class="form-group">
                <label for="titleEdit">Note Title</label>
                <input type="text" class="form-control" id="titleEdit" name="titleEdit" aria-describedby="emailHelp"
                    value="<?php echo isset($editData['title']) ? $editData['title'] : ''; ?>">
            </div>

            <div class="form-group">
                <label for="descriptionEdit">Note Description</label>
                <textarea class="form-control" id="descriptionEdit" name="descriptionEdit" rows="2"><?php echo isset($editData['description']) ? $editData['description'] : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Upload Image</label>
                <input type="file" class="form-control-file" id="image" name="image" onchange="previewImage(this);">
            </div>

            <div class="form-group">
                <label for="uploadedImage">Uploaded Image</label>
                <div class="uploaded-image-container">
                    <img id="preview" src="<?php echo isset($editData['image']) ? 'images/' . $editData['image'] : ''; ?>" alt="Uploaded Image">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo isset($editData['sno']) ? 'Edit Note' : 'Add Note'; ?></button>
        </form>
    </div>

    <div class="container my-4">
        <table class="table" id="myTable">
            <thead>
                <tr>
                    <th scope="col">S.No</th>
                    <th scope="col">Title</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $sql = "SELECT * FROM `user`";
                $result = mysqli_query($conn, $sql);
                $sno = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $sno = $sno + 1;
                    echo "<tr>
                    <th scope='row'>" . $sno . "</th>
                    <td>" . $row['title'] . "</td>
                    <td>" . $row['description'] . "</td>
                    <td>";

                    if (!empty($row['image'])) {
                        $imagePath = 'images/' . $row['image'];
                        echo "<div class='uploaded-image-container'><img src='" . $imagePath . "' alt='Image'></div>";
                    }

                    echo "</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='snoeDelete' value='" . $row['sno'] . "'>
                            <button type='submit' class='btn btn-danger'>Delete</button>
                        </form>
                        <form method='POST' action=''>
                            <input type='hidden' name='snoEdit' value='" . $row['sno'] . "'>
                            <button type='submit' class='btn btn-primary'>Edit</button>
                        </form>
                    </td>
                </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>

</html>
