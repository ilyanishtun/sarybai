<?php
if (isset($_POST)) {
    print("Имя: " . $_POST['name']);
    print("<br>Email: " . $_POST['email']);
    print("<br>Сообщение: " . $_POST['message']);
}