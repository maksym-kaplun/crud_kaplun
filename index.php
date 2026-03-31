<?php
session_start();

$filename = 'profile.json';
$json_data = file_get_contents($filename);
$data = json_decode($json_data, true);
$interests = $data['interests'] ?? [];

$message = $_SESSION['msg'] ?? '';
$messageType = $_SESSION['type'] ?? '';
unset($_SESSION['msg'], $_SESSION['type']);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (isset($interests[$id])) {
        array_splice($interests, $id, 1);
        $data['interests'] = $interests;
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['msg'] = "Zájem byl odstraněn.";
        $_SESSION['type'] = "success";
    }
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interest_val'])) {
    $val = trim($_POST['interest_val']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : -1;

    if (empty($val)) {
        $_SESSION['msg'] = "Pole nesmí být prázdné.";
        $_SESSION['type'] = "error";
    } else {
        $exists = false;
        foreach ($interests as $idx => $existing) {
            if (strtolower($existing) === strtolower($val) && $idx !== $edit_id) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $_SESSION['msg'] = "Tento zájem už existuje.";
            $_SESSION['type'] = "error";
        } else {
            if ($edit_id >= 0) {
                $interests[$edit_id] = $val;
                $_SESSION['msg'] = "Zájem byl upraven.";
            } else {
                $interests[] = $val;
                $_SESSION['msg'] = "Zájem byl úspěšně přidán.";
            }
            $data['interests'] = array_values($interests);
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $_SESSION['type'] = "success";
        }
    }
    header("Location: index.php");
    exit;
}

$edit_mode = false;
$edit_val = "";
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if (isset($interests[$id])) {
        $edit_mode = true;
        $edit_val = $interests[$id];
        $current_id = $id;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>IT Profil 5.0</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Moje zájmy</h1>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <ul>
        <?php foreach ($interests as $index => $interest): ?>
            <li>
                <?php echo htmlspecialchars($interest); ?>
                <div>
                    <a href="?edit=<?php echo $index; ?>" class="edit-link">Upravit</a>
                    <a href="?delete=<?php echo $index; ?>" class="delete-link" onclick="return confirm('Opravdu smazat?')">Smazat</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <form method="POST">
        <h3><?php echo $edit_mode ? "Upravit zájem" : "Přidat nový zájem"; ?></h3>
        <input type="text" name="interest_val" value="<?php echo htmlspecialchars($edit_val); ?>" required>
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?php echo $current_id; ?>">
        <?php endif; ?>
        <button type="submit"><?php echo $edit_mode ? "Uložit" : "Přidat"; ?></button>
        <?php if ($edit_mode): ?>
            <br><a href="index.php">Zrušit úpravu</a>
        <?php endif; ?>
    </form>
</body>
</html>