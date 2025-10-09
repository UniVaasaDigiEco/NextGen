<?php
session_start();
date_default_timezone_set('Europe/Helsinki');

require_once('../classes/tools.class.php');

$db = Tools::GetDB();

$email = $_POST['email'];
$password = $_POST['password'];

$location = "Location: ../index.php";

$sql = "SELECT id, hash, last_name, first_name, user_type FROM users WHERE email = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->bind_result($id, $hash, $last_name, $first_name, $user_type);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows == 1)
{
    $stmt->fetch();

    if(password_verify($password, $hash))
    {
        $name = "$first_name $last_name";
        $user = [
            "id" => $id,
            "name" => $name,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "type" => $user_type
        ];

        $_SESSION['user'] = $user;
        $location = "Location: ../pages/main.php";
    }
    else
    {
        $location = "Location: ../index.php";
        $_SESSION['error'] = "Virheellinen käyttäjätunnus ja/tai salasana.";
    }
}
else
{
    $location = "Location: ../index.php";
    $_SESSION['error'] = "Virheellinen käyttäjätunnus ja/tai salasana.";
}

header($location);

