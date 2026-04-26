<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Resultado do Envio</h1>
        
        <div class="card">
            <div class="card-header">Site: <?= $website['name'] ?></div>
            <div class="card-body">
                <?php foreach ($results as $result): ?>
                    <div class="alert alert-<?= $result['success'] ? 'success' : 'danger' ?>">
                        <strong><?= $result['style']['label'] ?></strong> - <?= $result['message'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <a href="<?= base_url('/sync') ?>" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>