<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Enviar Estilos para Site WordPress</h1>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">Baixar Plugin</div>
            <div class="card-body">
                <p>Baixe o plugin para instalar no seu site WordPress (versão <?= $plugin_version ?>).</p>
                <a href="<?= base_url('/sync/download-plugin') ?>" class="btn btn-success">
                    <i class="bi bi-download"></i> Baixar Plugin v<?= $plugin_version ?>
                </a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Selecionar Estilos</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('/sync/send') ?>">
                    <div class="mb-3">
                        <label class="form-label">Site de Destino</label>
                        <select name="website_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($websites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= $site['name'] ?> (<?= $site['url'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estilos para Enviar</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($styles)): ?>
                                <p class="text-muted">Nenhum estilo disponível.</p>
                            <?php else: ?>
                                <?php foreach ($styles as $style): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="style_ids[]" value="<?= $style['id'] ?>" id="style<?= $style['id'] ?>">
                                        <label class="form-check-label" for="style<?= $style['id'] ?>">
                                            <?= $style['label'] ?> <span class="badge bg-secondary"><?= $style['tipo'] ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enviar para o Site</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>