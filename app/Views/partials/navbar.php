<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/dashboard') ?>">
            <img src="<?= base_url('/Images/logo.png') ?>" alt="Divi Patropi">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (session()->get('user_id')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/styles') ?>">Estilos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/sync') ?>">Sync</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/websites') ?>">Sites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/users') ?>">Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/auth/logout') ?>">
                            <i class="bi bi-box-arrow-right"></i> Sair (<?= session()->get('user_name') ?>)
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>