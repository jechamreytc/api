<?php
include "headers1.php";
class Users
{
    function login($json)
    {
        include "connection1.php";
        $data = json_decode($json, true);
        $sql = "SELECT * FROM tblusers WHERE user_username = :user_username AND BINARY user_password = :user_password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_username", $data["user_username"]);
        $stmt->bindParam(":user_password", $data["user_password"]);
        $stmt->execute();
        $returnValue = 0;
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $returnValue = json_encode($result);
        }

        return $returnValue;
    }

    function registerUser($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "INSERT INTO tblusers(user_fullname, user_username, user_password) VALUES(:user_fullname, :user_username, :user_password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_fullname", $json["user_fullname"]);
        $stmt->bindParam(":user_username", $json["user_username"]);
        $stmt->bindParam(":user_password", $json["user_password"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }
    function createPost($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $date = getCurrentDate();

        $returnValueImage = uploadImage();
        switch ($returnValueImage) {
            case 2:
                // You cannot Upload files of this type!
                return 2;
            case 3:
                // There was an error uploading your file!
                return 3;
            case 4:
                // Your file is too big (25mb maximum)
                return 4;
            default:
                break;
        }
        $sql = "INSERT INTO tblpost(post_user_id, post_description, post_date, post_image) 
    VALUES(:post_user_id, :post_description, :post_date, :post_image)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":post_user_id", $json["post_user_id"]);
        $stmt->bindParam(":post_description", $json["post_description"]);
        $stmt->bindParam(":post_date", $date);
        $stmt->bindParam(":post_image", $returnValueImage);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    // function getAllPost(){
    //     include 'connection1.php';
    //     $sql="SELECT * FROM `tblpost` ORDER BY `post_date` DESC";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute();
    //     $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     return json_encode($result);
    // }
    function getUserProfile($json)
    {
        include 'connection1.php';
        $json = json_decode($json, true);
        $sql = "SELECT user_profile_picture FROM tblusers WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_id", $json["user_id"]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    function getAllUserProfiles()
    {
        include "connection1.php";
        $sql = "SELECT a.user_fullname, a.user_profile_picture, b.* 
        FROM tblusers AS a 
        INNER JOIN tblpost AS b ON a.user_id = b.post_user_id 
        WHERE b.post_user_id = :user_id 
        ORDER BY post_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_id", $json["user_id"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
    }

    function getAllUserPost($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "SELECT a.user_fullname, a.user_profile_picture, b.post_description, b.post_date, b.post_image 
        FROM tblusers AS a 
        INNER JOIN tblpost AS b ON a.user_id = b.post_user_id 
        WHERE b.post_user_id = :user_id 
        ORDER BY post_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_id", $json["user_id"]);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $stmt->rowCount() > 0 ? json_encode($result) : 0;
    }

    function getUserDetails($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "SELECT * FROM tblusers WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_id", $json["user_id"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
    }
    function getAllPost()
    {
        include "connection1.php";
        $sql = "SELECT a.user_id, a.user_fullname, a.user_profile_picture, b.*, COUNT(c.userlikes_id) AS likes 
        FROM tblusers AS a 
        INNER JOIN tblpost AS b ON a.user_id = b.post_user_id 
        LEFT JOIN tbluserlikes as c ON c.userlikes_post_id = b.post_id 
        GROUP BY b.post_id  
        ORDER BY post_date DESC;";
        $stmt = $conn->prepare($sql); // Prepare the SQL statement
        $stmt->execute(); // Execute the prepared statement
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $stmt->rowCount() > 0 ? json_encode($result) : 0;
    }


    function comments($json)
    {

        $date = getCurrentDate();
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "INSERT INTO tblusercomments(usercoms_user_id, usercoms_post_id, usercoms_comments, usercoms_date) VALUES(:usercoms_user_id, :usercoms_post_id, :usercoms_comments, :usercoms_date)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":usercoms_user_id", $json["usercoms_user_id"]);
        $stmt->bindParam(":usercoms_post_id", $json["usercoms_post_id"]);
        $stmt->bindParam(":usercoms_date", $date);
        $stmt->bindParam(":usercoms_comments", $json["usercoms_comments"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function getComments($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "SELECT a.user_id, a.user_fullname, a.user_profile_picture, c.usercoms_comments 
        FROM tblusers as a 
        INNER JOIN tblusercomments as c ON c.usercoms_user_id = a.user_id 
        WHERE c.usercoms_post_id = :post_id 
        ORDER BY c.usercoms_date";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":post_id", $json["post_id"]);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            return json_encode($results);
        } else {
            return 0;
        }
    }


    function heartPost($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);

        $sql_check = "SELECT * FROM tbluserlikes WHERE userlikes_post_id = :userlikes_post_id AND userlikes_user_id = :userlikes_user_id";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(":userlikes_post_id", $json["userlikes_post_id"]);
        $stmt_check->bindParam(":userlikes_user_id", $json["userlikes_user_id"]);
        $stmt_check->execute();

        if ($stmt_check->fetch()) {
            // return "User already liked the post";
            $sql1 = "DELETE FROM tbluserlikes WHERE userlikes_post_id = :userlikes_post_id AND userlikes_user_id = :userlikes_user_id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(":userlikes_post_id", $json["userlikes_post_id"]);
            $stmt1->bindParam(":userlikes_user_id", $json["userlikes_user_id"]);
            $stmt1->execute();
            return 5;
        }

        $sql_insert = "INSERT INTO tbluserlikes(userlikes_post_id, userlikes_user_id) VALUES (:userlikes_post_id, :userlikes_user_id)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bindParam(":userlikes_post_id", $json["userlikes_post_id"]);
        $stmt_insert->bindParam(":userlikes_user_id", $json["userlikes_user_id"]);
        $stmt_insert->execute();

        return $stmt_insert->rowCount() > 0 ? 1 : 0;
    }
    function isUserLiked($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);
        $sql = "SELECT * FROM tbluserlikes WHERE userlikes_post_id = :userlikes_post_id AND userlikes_user_id = :userlikes_user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userlikes_post_id", $json["userlikes_post_id"]);
        $stmt->bindParam(":userlikes_user_id", $json["userlikes_user_id"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function updateProfile($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);

        $returnValueImage = uploadImage();
        switch ($returnValueImage) {
            case 2:
                // You cannot Upload files of this type!
                return 2;
            case 3:
                // There was an error uploading your file!
                return 3;
            case 4:
                // Your file is too big (25mb maximum)
                return 4;
            default:
                break;
        }
        $sql = "UPDATE tblusers 
        SET user_profile_picture = :user_profile_picture, 
            user_fullname = :user_fullname, 
            user_username = :user_username, 
            user_password = :user_password 
        WHERE user_id = :user_id;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":user_profile_picture", $returnValueImage);
        $stmt->bindParam(":user_fullname", $json["user_fullname"]);
        $stmt->bindParam(":user_username", $json["user_username"]);
        $stmt->bindParam(":user_password", $json["user_password"]);
        $stmt->bindParam(":user_id", $json["user_id"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function deletePost($json)
    {
        include "connection1.php";
        $json = json_decode($json, true);

        try {
            $sql = "DELETE FROM tblpost WHERE post_id = :post_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":post_id", $json["post_id"]);
            $stmt->execute();

            // Check if any rows were affected by the delete operation
            return $stmt->rowCount() > 0 ? 1 : 0;
        } catch (PDOException $e) {
            // Handle database errors
            return 0; // Return 0 to indicate failure
        }
    }
}

function uploadImage()
{
    if (isset($_FILES["file"])) {
        $file = $_FILES['file'];
        // print_r($file);
        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileError = $_FILES['file']['error'];
        // $fileType = $_FILES['file']['type'];

        $fileExt = explode(".", $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowed = ["jpg", "jpeg", "png", "gif"];

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 25000000) {
                    $fileNameNew = uniqid("", true) . "." . $fileActualExt;
                    $fileDestination = 'images/' . $fileNameNew;
                    move_uploaded_file($fileTmpName, $fileDestination);
                    return $fileNameNew;
                } else {
                    return 4;
                }
            } else {
                return 3;
            }
        } else {
            return 2;
        }
    } else {
        return "";
    }

    // $returnValueImage = uploadImage();

    // switch ($returnValueImage) {
    //     case 2:
    //         // You cannot Upload files of this type!
    //         return 2;
    //     case 3:
    //         // There was an error uploading your file!
    //         return 3;
    //     case 4:
    //         // Your file is too big (25mb maximum)
    //         return 4;
    //     default:
    //         break;
    // }
}
function getCurrentDate()
{
    $today = new DateTime("now", new DateTimeZone('Asia/Manila'));
    return $today->format('Y-m-d H:i:s');
}

$json = isset($_POST["json"]) ? $_POST["json"] : "0";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";
$users = new Users();

switch ($operation) {
    case "login":
        echo $users->login($json);
        break;
    case "registerUser":
        echo $users->registerUser($json);
        break;
    case "createPost":
        echo $users->createPost($json);
        break;
    case "getAllPost":
        echo $users->getAllPost();
        break;
    case "getUserProfile":
        echo $users->getUserProfile($json);
        break;
    case "comments":
        echo $users->comments($json);
        break;
    case "getComments":
        echo $users->getComments($json);
        break;
    case "getAllUserPost":
        echo $users->getAllUserPost($json);
        break;
    case "getAllUserProfile":
        echo $users->getAllUserPost($json);
        break;
    case "getUserDetails":
        echo $users->getUserDetails($json);
        break;
    case "heartPost":
        echo $users->heartPost($json);
        break;
    case "isUserLiked":
        echo $users->isUserLiked($json);
        break;
    case "updateProfile":
        echo $users->updateProfile($json);
        break;
    case "deletePost":
        echo $users->deletePost($json);
        break;
    default:
        break;
}
