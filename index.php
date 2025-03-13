<?php
$host = 'localhost';
$dbname = 'mydb';
$user = 'myuser';
$pass = 'mypass';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$searchText = $_GET['text'] ?? '';
$searchTheme = $_GET['theme'] ?? '';
$searchAuthor = $_GET['author'] ?? '';

$query = "SELECT theme, author, text FROM quotes WHERE 1=1";
$params = [];

if (!empty($searchText)) {
    $query .= " AND text ILIKE ?";
    $params[] = "%$searchText%";
}
if (!empty($searchTheme)) {
    $query .= " AND theme ILIKE ?";
    $params[] = "%$searchTheme%";
}
if (!empty($searchAuthor)) {
    $query .= " AND author ILIKE ?";
    $params[] = "%$searchAuthor%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./styles/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyQuotes</title>
</head>
<body>
<header class="header">
    <a href="index.php" class="header__title">MyQuotes</a>
</header>
<main class="main">
    <div class="search">
        <form action="index.php" method="GET" class="search__form form">
            <div class="form__input">
                <label class="form__input-text" for="text">Поиск по тексту</label>
                <input class="form__input-input"  type="text" id="text" name="text" placeholder="Введите текст" value="<?= htmlspecialchars($searchText) ?>">
            </div>
            <div class="form__input">
                <label class="form__input-text" for="theme">Тематика</label>
                <select class="form__input-input" id="theme" name="theme">
                    <option value="">Выберите тему</option>
                    <?php
                    $topics = $pdo->query("SELECT DISTINCT theme FROM quotes")->fetchAll();
                    foreach ($topics as $topic) {
                      echo "<option value='" . htmlspecialchars($topic['theme']) . "' " . ($searchTheme === $topic['theme'] ? 'selected' : '') . ">" . htmlspecialchars($topic['theme']) . "</option>";
                  }
                    ?>
                </select>
            </div>
            <div class="form__input">
                <label class="form__input-text" for="author">Автор</label>
                <select class="form__input-input" id="author" name="author">
                    <option value="" <?= empty($searchAuthor) ? 'selected' : '' ?>>Выберите автора</option>
                    <?php
                    $authors = $pdo->query("SELECT DISTINCT author FROM quotes")->fetchAll();
                    foreach ($authors as $author) {
                        echo "<option value='" . htmlspecialchars($author['author']) . "' " . ($searchAuthor === $author['author'] ? 'selected' : '') . ">" . htmlspecialchars($author['author']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form__button">
                <button class="form__button-reset" type="button" onclick="window.location='index.php'">Сбросить</button>
                <button class="form__button-submit" type="submit">Применить</button>
            </div>
        </form>
    </div>

    <div class="quotes">
        <p class="quotes__title">Цитаты</p>
        <div class="quotes__quotes">
            <?php foreach ($quotes as $quote): ?>
                <div class="quote">
                    <p class="quote__title"><?= htmlspecialchars($quote['theme']) ?></p>
                    <p class="quote__text"><?= htmlspecialchars($quote['text']) ?></p>
                    <p class="quote__author"><?= htmlspecialchars($quote['author']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<footer class="footer">
    <p class="footer__text">©Kirill Balko, PIN-221, OmSTU, 2025</p>
</footer>
</body>
</html>
