<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$page_title = $page_title ?? 'Sites Para Empresas';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Sites Para Empresas</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>/index.php">
                        <i class="fas fa-globe"></i> Sites Para Empresas
                    </a>
                </div>
                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Início</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/planos.php">Planos</a></li>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>/dashboard/index.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/api/logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/cadastro.php" class="btn-primary">Cadastre-se</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>