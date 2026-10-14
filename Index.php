<?php
$errors = [];
$successMessage = '';

$name = '';
$wateringIntervalDays = '';
$lastWatered = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $wateringIntervalDays = $_POST['wateringIntervalDays'] ?? '';
    $lastWatered = $_POST['lastWatered'] ?? '';

    
    if ($name === '') {
        $errors['name'] = 'Назва рослини обов\'язкова.';
    }

    if (!filter_var($wateringIntervalDays, FILTER_VALIDATE_INT) || (int)$wateringIntervalDays <= 0) {
        $errors['wateringIntervalDays'] = 'Інтервал поливу має бути цілим числом більше 0.';
    }

    if ($lastWatered === '') {
        $errors['lastWatered'] = 'Дата останнього поливу обов\'язкова.';
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $lastWatered);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $lastWatered) {
            $errors['lastWatered'] = 'Некоректний формат дати.';
        } else {
            $today = new DateTime('today'); // Сьогоднішня дата (без часу)
            $inputDate = new DateTime($lastWatered);
            $inputDate->setTime(0, 0, 0); // Обнуляємо час для коректного порівняння
            
            if ($inputDate > $today) {
                $errors['lastWatered'] = 'Дата не може бути пізнішою за сьогодні.';
            }
        }
    }

   
    if (empty($errors)) {
        $successMessage = "Рослину <strong>" . htmlspecialchars($name) . "</strong> успішно додано!";
        $name = '';
        $wateringIntervalDays = '';
        $lastWatered = '';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Додавання рослини</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="date"], select { width: 100%; padding: 8px; box-sizing: border-box; }
        .error-message { color: red; font-size: 0.9em; margin-top: 5px; }
        .success { color: green; background-color: #e6ffe6; padding: 15px; border: 1px solid green; margin-bottom: 20px; }
        .controls-section { background: #f9f9f9; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <h1>Додавання нової рослини</h1>

    <div class="controls-section">
        <label for="sortSelect">Сортування списку рослин (зберігається в localStorage):</label>
        <select id="sortSelect">
            <option value="name">За назвою</option>
            <option value="date">За датою поливу</option>
        </select>
    </div>

    <?php if ($successMessage): ?>
        <div class="success"><?= $successMessage ?></div>
    <?php endif; ?>

    <form id="plantForm" method="post" action="form.php">
        
        <div class="form-group">
            <label for="name">Назва рослини:</label>
            <input type="text" id="name" name="name" 
                   value="<?= htmlspecialchars($name) ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="error-message"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="wateringIntervalDays">Інтервал поливу (днів):</label>
            <input type="number" id="wateringIntervalDays" name="wateringIntervalDays" 
                   value="<?= htmlspecialchars($wateringIntervalDays) ?>" min="1" required>
            <?php if (isset($errors['wateringIntervalDays'])): ?>
                <div class="error-message"><?= $errors['wateringIntervalDays'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="lastWatered">Дата останнього поливу:</label>
            <input type="date" id="lastWatered" name="lastWatered" 
                   value="<?= htmlspecialchars($lastWatered) ?>" 
                   max="<?= date('Y-m-d') ?>" required>
            <?php if (isset($errors['lastWatered'])): ?>
                <div class="error-message"><?= $errors['lastWatered'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Додати рослину</button>
    </form>

    <script>
        const form = document.getElementById('plantForm');
        form.addEventListener('submit', function(event) {
            const nameInput = document.getElementById('name').value.trim();
            
            if (nameInput.length < 2) {
                alert('Назва рослини повинна містити хоча б 2 символи.');
                event.preventDefault(); 
            }
        });


        const sortSelect = document.getElementById('sortSelect');
        const STORAGE_KEY = 'plantListSortPreference';

        const savedSort = localStorage.getItem(STORAGE_KEY);
        if (savedSort) {
            sortSelect.value = savedSort;
        }

        sortSelect.addEventListener('change', function() {
            localStorage.setItem(STORAGE_KEY, this.value);
            console.log('Налаштування сортування збережено: ' + this.value);
        });
    </script>
</body>
</html>