<?php
// pages/index.php

// 1. Задаем заголовок вкладки браузера
$page_title = 'Главная — Портал «Стипендия»';

// 2. Начинаем "запоминать" (буферизировать) весь HTML-код ниже
ob_start(); 
?>

<!-- Слайдер -->
<section class="slider-section">
    <div class="slider-container">
        <div class="slider-wrapper">
            <div class="slide active">
                <img src="/media/diploma.webp" alt="Слайд 1">
            </div>
            <div class="slide">
                <img src="/media/1643087798_5-bigfoto-name-p-id.jpg" alt="Слайд 2">
            </div>
            <div class="slide">
                <img src="/media/7621e0c3c1df2e3322f6222ff94e67f3.jpg" alt="Слайд 3">
            </div>
            <div class="slide">
                <img src="/media/864c66af3dc2cc0eb8166ba359500f25.jpg" alt="Слайд 4">
            </div>
        </div>

        <!-- Индикаторы (4 точки) -->
        <div class="slider-dots">
            <span class="dot active" data-index="0"></span>
            <span class="dot" data-index="1"></span>
            <span class="dot" data-index="2"></span>
            <span class="dot" data-index="3"></span>
        </div>

        <!-- Кнопки навигации -->
        <button class="slider-btn slider-prev">&#10094;</button>
        <button class="slider-btn slider-next">&#10095;</button>
    </div>
</section>

<!-- Описание портала -->
<section class="description-section">
    <div class="description-content">
        <p>
            Портал «Стипендия» представляет собой информационную систему для подачи
            заявок на стипендиальные программы. Здесь вы можете ознакомиться с доступными
            типами стипендий, подать заявку и отслеживать её статус.
        </p>
    </div>
</section>

<?php 
// 3. Сохраняем весь "запомненный" HTML в переменную $page_content
$page_content = ob_get_clean(); 

// 4. Подключаем единый layout, который выведет шапку, наш контент и подвал
require_once __DIR__ . '/layout.php'; 
?>
