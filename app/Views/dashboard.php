<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Dashboard</h1>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Estilos Disponíveis</h5>
                        <p class="card-text display-4"><?= count($styles ?? []) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Sites Cadastrados</h5>
                        <p class="card-text display-4"><?= count($websites ?? []) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Usuários</h5>
                        <p class="card-text display-4"><?= count($users ?? []) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Ações Rápidas</h4>
            <div class="list-group mt-3">
                <a href="<?= base_url('/websites') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-globe"></i> Gerenciar Sites WordPress
                </a>
                <a href="<?= base_url('/styles') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-palette"></i> Ver Estilos Disponíveis
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>