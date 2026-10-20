<?php
// helpers/db.php

// Параметры подключения к базе данных
$dbHost = 'localhost';
$dbName = 'korochki';
$dbUser = 'root'; // Стандартный пользователь для XAMPP/LAMPP
$dbPass = '';     // Стандартный пароль для XAMPP/LAMPP (пустой)
$dbCharset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset";

// Опции для PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Включаем исключения при ошибках
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Устанавливаем режим выборки по умолчанию
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Отключаем эмуляцию подготовленных запросов
];

try {
    // Создаем объект PDO для подключения
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    // В случае ошибки подключения, выводим сообщение и прекращаем работу
    http_response_code(500);
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}