<?php
  require 'StudentManager.php';

  $studentManager = new StudentManager();

  $id = $_POST['id'] ?? null;

  if (!$id) {
    die("Missing student ID!");
  }

  $result = $studentManager->delete($id);
  print_r($result);

?>  