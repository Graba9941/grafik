<?php
session_start();

function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        return array();
    }
    $jsonString = file_get_contents($filePath);
    $data = json_decode($jsonString, true);
        if (!is_array($data)) {
        return array();
    }
    return $data;
}

function writeJsonFile($filePath, $data) {
    $jsonString = json_encode($data);
    return file_put_contents($filePath, $jsonString);
}

function loginUser($username, $password) {
    $users = readJsonFile('config/users.json');
    foreach ($users as $user) {
        if ($user['username'] === $username && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logoutUser() {
    session_destroy();
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function getUserName($userId){
    $users = readJsonFile('config/users.json');
    foreach ($users as $user) {
        if ($user['id'] === $userId) {
            return $user['username'];
        }
    }
    return 'N/A';
}

function getWorkHours(){
    return readJsonFile('config/work_hours.json');
}

function getSchedule(){
    return readJsonFile('config/schedule.json');
}

function addEvent($eventData) {
    $schedule = getSchedule();
    $eventData['id'] = uniqid();
    $schedule[] = $eventData;
    writeJsonFile('config/schedule.json', $schedule);
}

function updateEvent($eventId, $eventData){
    $schedule = getSchedule();
    foreach($schedule as $key => $event){
        if($event['id'] === $eventId){
            $schedule[$key] = $eventData;
            break;
        }
    }
    writeJsonFile('config/schedule.json', $schedule);
}

function deleteEvent($eventId){
    $schedule = getSchedule();
    $newSchedule = array();
        foreach ($schedule as $event) {
        if ($event['id'] !== $eventId) {
          $newSchedule[] = $event;
        }
      }
    writeJsonFile('config/schedule.json', $newSchedule);
}


function getSettings() {
    return readJsonFile('config/settings.json');
}

function addWorkHour($hour){
    $workHours = getWorkHours();
    $workHours[] = $hour;
    writeJsonFile('config/work_hours.json', $workHours);
}

function deleteWorkHour($hour){
    $workHours = getWorkHours();
     $newWorkHours = array();
        foreach ($workHours as $wh) {
        if ($wh !== $hour) {
          $newWorkHours[] = $wh;
        }
      }
    writeJsonFile('config/work_hours.json', $newWorkHours);
}

function getUsers() {
    return readJsonFile('config/users.json');
}

function addUser($userData) {
    $users = getUsers();
    $userData['id'] = uniqid();
    $users[] = $userData;
    writeJsonFile('config/users.json', $users);
}

function updateUser($userId, $userData) {
    $users = getUsers();
    foreach($users as $key => $user) {
        if($user['id'] === $userId) {
            $users[$key] = $userData;
            break;
        }
    }
    writeJsonFile('config/users.json', $users);
}

function deleteUser($userId) {
   $users = getUsers();
      $newUsers = array();
        foreach ($users as $user) {
        if ($user['id'] !== $userId) {
          $newUsers[] = $user;
        }
      }
    writeJsonFile('config/users.json', $newUsers);
}
function validateDateTime($dateTime) {
        return (strtotime($dateTime) !== false);
    }
?>