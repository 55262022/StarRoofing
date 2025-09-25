<?php
include '../database/starroofing_db.php';

if (!empty($_POST['selected'])) {
    $ids = implode(",", array_map('intval', $_POST['selected']));
    $sql = "UPDATE contact_messages SET is_archived = 1 WHERE id IN ($ids)";
    if ($conn->query($sql)) {
        header("Location: messages.php?archived=success");
        exit();
    } else {
        die("Error: " . $conn->error);
    }
}
header("Location: messages.php?archived=none");
exit();
?>