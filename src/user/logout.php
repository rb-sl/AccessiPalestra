<?php
// Functions to close the session
session_start();
session_unset();
session_destroy();
header('Location: /');
?>