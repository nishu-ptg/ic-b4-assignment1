<?php
  session_start();
  
  require_once 'StudentManager.php';

  $studentManager = new StudentManager();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    $result = $studentManager->delete($id);
    
    $_SESSION['flash_message'] = $result;
    header("Location: index.php");
    exit;    

}