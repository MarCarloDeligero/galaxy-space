<?php
// admin/logout_and_redirect.php
session_start();
session_unset();
session_destroy();
header('Location: ../index.php');
exit;
