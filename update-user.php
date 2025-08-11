<?php
include 'server.php';

$user_id      = (int) $_POST['user_id'];
$name         = $_POST['name'];
$username     = $_POST['username'];
$password     = $_POST['password']; // Only update if not empty
$mobile       = $_POST['mobile'];
$role_id      = $_POST['role_id'];
$region_id    = $_POST['region_id'];
$area_id      = $_POST['area_id'];
$supervisor_id= $_POST['supervisor_id'];
$collection   = $_POST['collection'];
$commission   = $_POST['commission'];

$set_password = "";
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $set_password = ", password = '$password'";
}
if (!is_numeric($region_id) && $region_id !== '') {
    die("Invalid region ID.");
}
if (!is_numeric($area_id) && $area_id !== '') {
    die("Invalid area ID.");
}
if (!is_numeric($supervisor_id) && $supervisor_id !== '') {
    die("Invalid supervisor ID.");
}
$sql = "
  UPDATE user_master SET 
    name = '$name',
    username = '$username',
    mobile = '$mobile',
    role_id = " . ($role_id === '' ? 'NULL' : $role_id) . ",
    region_id = " . ($region_id === '' ? 'NULL' : $region_id) . ",
    area_id = $area_id,
    supervisor_id = " . ($supervisor_id === '' ? 'NULL' : $supervisor_id) . ",
    collection = $collection,
    commission = $commission
    $set_password
  WHERE user_id = $user_id
";

if ($conn->query($sql)) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo "Error updating user: " . $conn->error;
}
