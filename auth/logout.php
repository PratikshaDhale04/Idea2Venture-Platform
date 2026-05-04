<?php
require_once '../config/database.php';

session_destroy();
session_unset();

redirect('../index.php');
?>