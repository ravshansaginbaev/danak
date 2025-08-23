<?php
session_start();
require 'db.php';

$id = intval($_GET['id']);
$conn->query("UPDATE notifications SET seen = 1 WHERE id = $id");
